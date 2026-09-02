<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQuerySendBenchmark;
use Hashtopolis\inc\agent\PResponseSendBenchmark;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\agent\PValuesBenchmarkType;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\SConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``sendBenchmark`` action.
 *
 * The agent reports its benchmark result for a task.  Two types are supported:
 * - ``speed`` (``int:float`` format, e.g. ``2345:323.000``)
 * - ``run`` (runtime in seconds, normalized to 100 seconds)
 *
 * Invalid results or types deactivate the agent and return an error.
 */
final class SendBenchmarkAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQuerySendBenchmark::TASK_ID]) || !isset($body[PQuerySendBenchmark::TYPE]) || !isset($body[PQuerySendBenchmark::RESULT])) {
            return $this->error($response, PActions::SEND_BENCHMARK, 'Invalid benchmark query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);
        $agent = $this->updateAgent($agent, PActions::SEND_BENCHMARK);

        $task = Factory::getTaskFactory()->get($body[PQuerySendBenchmark::TASK_ID]);
        if ($task === null) {
            return $this->error($response, PActions::SEND_BENCHMARK, 'Invalid task ID!');
        }

        $qF1 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), '=');
        $qF2 = new QueryFilter(Assignment::TASK_ID, $task->getId(), '=');
        $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
        if ($assignment === null) {
            return $this->error($response, PActions::SEND_BENCHMARK, 'You are not assigned to this task!');
        }

        $type = $body[PQuerySendBenchmark::TYPE];
        $benchmark = $body[PQuerySendBenchmark::RESULT];

        DServerLog::log(DServerLog::TRACE, 'Agent sending benchmark', [$agent, $task, $type, $benchmark]);

        switch ($type) {
            case PValuesBenchmarkType::SPEED_TEST:
                $split = explode(':', (string)$benchmark);
                if (sizeof($split) != 2 || !is_numeric($split[0]) || !is_numeric($split[1]) || $split[0] <= 0 || $split[1] <= 0) {
                    Factory::getAgentFactory()->set($agent, Agent::IS_ACTIVE, 0);
                    DServerLog::log(DServerLog::ERROR, 'Invalid speed test benchmark result!', [$agent, $benchmark]);
                    return $this->error($response, PActions::SEND_BENCHMARK, 'Invalid benchmark result!');
                }
                break;
            case PValuesBenchmarkType::RUN_TIME:
                if (!is_numeric($benchmark) || $benchmark <= 0) {
                    Factory::getAgentFactory()->set($agent, Agent::IS_ACTIVE, 0);
                    DServerLog::log(DServerLog::ERROR, 'Invalid benchmark results for runtime benchmark', [$agent, $task, $benchmark]);
                    return $this->error($response, PActions::SEND_BENCHMARK, 'Invalid benchmark result!');
                }
                $benchmark = $benchmark / SConfig::getInstance()->getVal(DConfig::BENCHMARK_TIME) * 100;
                DServerLog::log(DServerLog::TRACE, 'Saving normalized runtime benchmark', [$agent, $task, $benchmark]);
                break;
            default:
                Factory::getAgentFactory()->set($agent, Agent::IS_ACTIVE, 0);
                return $this->error($response, PActions::SEND_BENCHMARK, 'Invalid benchmark type!');
        }

        $assignment->setBenchmark((string)$benchmark);
        Factory::getAssignmentFactory()->update($assignment);
        DServerLog::log(DServerLog::DEBUG, 'Saved agent benchmark', [$agent, $task, $assignment]);

        return $this->success($response, PActions::SEND_BENCHMARK, [
            PResponseSendBenchmark::BENCHMARK => PValues::OK,
        ]);
    }
}
