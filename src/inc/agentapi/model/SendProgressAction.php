<?php

declare(strict_types=1);

namespace Hashtopolis\inc\agentapi\model;

use Exception;
use Hashtopolis\dba\ComparisonFilter;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\LikeFilterInsensitive;
use Hashtopolis\dba\MassUpdateSet;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\QueryFilterWithNull;
use Hashtopolis\dba\UpdateSet;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\AgentStat;
use Hashtopolis\dba\models\AgentZap;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\Hash;
use Hashtopolis\dba\models\HashBinary;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\Speed;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskDebugOutput;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\Zap;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQuery;
use Hashtopolis\inc\agent\PQuerySendProgress;
use Hashtopolis\inc\agent\PResponseSendProgress;
use Hashtopolis\inc\agentapi\common\AgentAction;
use Hashtopolis\inc\agentapi\common\AgentResponseTrait;
use Hashtopolis\inc\DataSet;
use Hashtopolis\inc\defines\DAgentStatsType;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DHashcatStatus;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\defines\DLogEntry;
use Hashtopolis\inc\defines\DLogEntryIssuer;
use Hashtopolis\inc\defines\DNotificationType;
use Hashtopolis\inc\defines\DPayloadKeys;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\defines\DTaskTypes;
use Hashtopolis\inc\handlers\NotificationHandler;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\utils\TaskUtils;
use Hashtopolis\inc\Util;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

/**
 * PSR-7 controller for the ``sendProgress`` action.
 *
 * The most complex agent API action.  Receives chunk progress, cracked hashes,
 * GPU/CPU stats, and hashcat state from the agent.  Processes cracks (PLAIN,
 * WPA, BINARY formats), creates zaps for other agents, fires notifications,
 * handles abort/exhausted/cracked state transitions, and returns cracked
 * count, skipped count, and zap list.
 */
final class SendProgressAction implements AgentAction {
    use AgentResponseTrait;

    public function __invoke(Request $request, Response $response): ResponseInterface {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        if (!isset($body[PQuery::TOKEN]) || !isset($body[PQuerySendProgress::CHUNK_ID]) || !isset($body[PQuerySendProgress::KEYSPACE_PROGRESS]) || !isset($body[PQuerySendProgress::RELATIVE_PROGRESS]) || !isset($body[PQuerySendProgress::SPEED]) || !isset($body[PQuerySendProgress::HASHCAT_STATE]) || !isset($body[PQuerySendProgress::CRACKED_HASHES])) {
            return $this->error($response, PActions::SEND_PROGRESS, 'Invalid progress query!');
        }

        /** @var Agent $agent */
        $agent = $request->getAttribute(AgentAction::AGENT_ATTRIBUTE);
        $agent = $this->updateAgent($agent, PActions::SEND_PROGRESS);

        $keyspaceProgress = $body[PQuerySendProgress::KEYSPACE_PROGRESS];
        $relativeProgress = intval($body[PQuerySendProgress::RELATIVE_PROGRESS]);
        $speed = intval($body[PQuerySendProgress::SPEED]);
        $state = intval($body[PQuerySendProgress::HASHCAT_STATE]);

        DServerLog::log(DServerLog::TRACE, 'Agent sending progress', [$agent]);

        $chunk = Factory::getChunkFactory()->get(intval($body[PQuerySendProgress::CHUNK_ID]));
        if ($chunk === null) {
            return $this->error($response, PActions::SEND_PROGRESS, 'Invalid chunk id ' . intval($body[PQuerySendProgress::CHUNK_ID]));
        }
        if ($chunk->getAgentId() != $agent->getId()) {
            return $this->error($response, PActions::SEND_PROGRESS, 'You are not assigned to this chunk');
        }
        if ($agent->getIsActive() == 0) {
            Factory::getChunkFactory()->set($chunk, Chunk::SPEED, 0);
            return $this->error($response, PActions::SEND_PROGRESS, 'Agent is marked inactive!');
        }

        DServerLog::log(DServerLog::TRACE, 'Agent is assigned to this chunk and active', [$agent, $chunk]);

        $task = Factory::getTaskFactory()->get($chunk->getTaskId());
        if ($task === null) {
            DServerLog::log(DServerLog::ERROR, 'Inconsistency between chunk and task!', [$agent, $chunk]);
            return $this->error($response, PActions::SEND_PROGRESS, 'No task exists for the given chunk');
        }
        if ($task->getIsArchived() == 1) {
            return $this->error($response, PActions::SEND_PROGRESS, 'Task is archived, no work to do');
        }
        $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
        if ($taskWrapper === null) {
            DServerLog::log(DServerLog::ERROR, 'Inconsistency between task and taskWrapper!', [$agent, $task]);
            return $this->error($response, PActions::SEND_PROGRESS, 'Inconsistency error on taskWrapper');
        }

        DServerLog::log(DServerLog::TRACE, 'Agent working on valid task', [$agent, $task]);

        $hashlist = Factory::getHashlistFactory()->get($taskWrapper->getHashlistId());
        if ($hashlist === null) {
            DServerLog::log(DServerLog::ERROR, 'Task is not having a valid hashlist!', [$agent, $task]);
            return $this->error($response, PActions::SEND_PROGRESS, 'The given task does not have a corresponding hashlist!');
        }
        $totalHashlist = $hashlist;
        $isSuperhashlist = false;
        if ($hashlist->getFormat() == DHashlistFormat::SUPERHASHLIST) {
            $isSuperhashlist = true;
        }
        $hashlists = Util::checkSuperHashlist($hashlist);
        foreach ($hashlists as $hl) {
            if ($hl->getIsSecret() > $agent->getIsTrusted()) {
                DServerLog::log(DServerLog::TRACE, 'For some reason agent was working on a hashlist he is not allowed to (probabily permission change)', [$agent, $task, $hl]);
                return $this->error($response, PActions::SEND_PROGRESS, 'Unknown Error. The API does not trust you with more information');
            }
        }

        DServerLog::log(DServerLog::TRACE, 'Agent working on correct hashlist(s)', [$agent, $totalHashlist]);

        $dataTime = time();
        $this->saveAgentStats($body, $agent, $dataTime);

        $skip = $chunk->getSkip();
        $length = $chunk->getLength();
        $taskID = $task->getId();

        if ($relativeProgress > 10000) {
            $relativeProgress = 10000;
        }
        if ($keyspaceProgress > $length + $skip) {
            $keyspaceProgress = $length + $skip;
        }

        $this->saveDebugOutput($body, $chunk);

        $aborting = false;
        if ($chunk->getState() == DHashcatStatus::ABORTED) {
            DServerLog::log(DServerLog::TRACE, 'Chunk was aborted, we need to stop afterwards', [$agent]);
            $aborting = true;
        }
        $chunk = Factory::getChunkFactory()->mset($chunk, [
            Chunk::PROGRESS    => $relativeProgress,
            Chunk::CHECKPOINT  => $keyspaceProgress,
            Chunk::SOLVE_TIME  => time(),
            Chunk::STATE       => $state,
        ]);
        DServerLog::log(DServerLog::TRACE, 'Progress updated chunk', [$agent, $chunk]);

        $format = $hashlists[0]->getFormat();

        $skipped = 0;
        $cracked = [];
        foreach ($hashlists as $hl) {
            $cracked[$hl->getId()] = 0;
        }

        $crackedHashes = $body[PQuerySendProgress::CRACKED_HASHES];
        Factory::getAgentFactory()->getDB()->beginTransaction();

        $plainUpdates = [];
        $crackPosUpdates = [];
        $crackHashes = [];
        $timeUpdates = [];
        $zaps = [];

        $isNewWPA = false;
        foreach ($hashlists as $hl) {
            if ($hl->getHashTypeId() == 22000) {
                $isNewWPA = true;
                break;
            }
        }

        for ($i = 0; $i < sizeof($crackedHashes); $i++) {
            $crackedHash = $crackedHashes[$i];
            if (!is_array($crackedHash) && $crackedHash == '') {
                continue;
            }
            else if (!is_array($crackedHash)) {
                $splitLine = explode(SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR), $crackedHash);
                $splitLine[] = '';
                $splitLine[] = -1;
            }
            else {
                $splitLine = $crackedHash;
            }

            switch ($format) {
                case DHashlistFormat::PLAIN:
                    $result = $this->processPlainCrack($splitLine, $hashlists, $chunk, $agent, $totalHashlist, $isNewWPA, $cracked);
                    if ($result === 'skip') {
                        $skipped++;
                    }
                    else {
                        $plainUpdates = array_merge($plainUpdates, $result['plainUpdates']);
                        $crackPosUpdates = array_merge($crackPosUpdates, $result['crackPosUpdates']);
                        $timeUpdates = array_merge($timeUpdates, $result['timeUpdates']);
                        $crackHashes = array_merge($crackHashes, $result['crackHashes']);
                        $zaps = array_merge($zaps, $result['zaps']);
                    }
                    if (sizeof($plainUpdates) >= 1000) {
                        $this->flushPlainBatch($plainUpdates, $crackPosUpdates, $timeUpdates, $crackHashes, $zaps, $chunk);
                        $zaps = [];
                        $plainUpdates = [];
                        $crackHashes = [];
                    }
                    break;

                case DHashlistFormat::WPA:
                    $skipped += $this->processWPACrack($splitLine, $chunk, $agent, $totalHashlist, $cracked);
                    break;

                case DHashlistFormat::BINARY:
                    $skipped += $this->processBinaryCrack($splitLine, $chunk, $agent, $totalHashlist, $cracked);
                    break;
            }
        }

        if ($format == DHashlistFormat::PLAIN && sizeof($plainUpdates) > 0) {
            $this->flushPlainBatch($plainUpdates, $crackPosUpdates, $timeUpdates, $crackHashes, $zaps, $chunk);
        }

        Factory::getAgentFactory()->getDB()->commit();

        Factory::getAgentFactory()->getDB()->beginTransaction();
        $sumCracked = 0;
        foreach ($cracked as $listId => $cracks) {
            $list = Factory::getHashlistFactory()->get($listId);
            if ($cracks > 0) {
                Factory::getHashlistFactory()->inc($list, Hashlist::CRACKED, $cracks);
            }
            if (!$isSuperhashlist) {
                $superHashlists = Util::getParentSuperHashlists($list);
                foreach ($superHashlists as $superHashlist) {
                    if ($cracks > 0) {
                        Factory::getHashlistFactory()->inc($superHashlist, Hashlist::CRACKED, $cracks);
                    }
                }
            }
            $sumCracked += $cracks;
        }
        if ($sumCracked > 0) {
            Factory::getChunkFactory()->inc($chunk, Chunk::CRACKED, $sumCracked);
        }
        if ($isSuperhashlist && $sumCracked > 0) {
            $hashlist = Factory::getHashlistFactory()->get($taskWrapper->getHashlistId());
            Factory::getHashlistFactory()->inc($hashlist, Hashlist::CRACKED, $sumCracked);
        }
        Factory::getAgentFactory()->getDB()->commit();

        DServerLog::log(DServerLog::TRACE, 'Updated with received cracks', [$agent, $chunk]);

        if ($chunk->getState() == DHashcatStatus::STATUS_ABORTED_RUNTIME) {
            $chunk = Factory::getChunkFactory()->set($chunk, Chunk::STATE, DHashcatStatus::ABORTED);
            DServerLog::log(DServerLog::TRACE, 'Chunk was manually interrupted', [$agent]);
            return $this->error($response, PActions::SEND_PROGRESS, 'Chunk was manually interrupted.');
        }

        $taskdone = false;
        if ($relativeProgress == 10000 && $task->getKeyspaceProgress() == $task->getKeyspace()) {
            $incompleteFilter = new QueryFilter(Chunk::PROGRESS, 10000, '<');
            $taskFilter = new QueryFilter(Chunk::TASK_ID, $taskID, '=');
            $count = Factory::getChunkFactory()->countFilter([Factory::FILTER => [$incompleteFilter, $taskFilter]]);
            $incompleteFilter = new QueryFilter(Chunk::PROGRESS, null, '=');
            $count += Factory::getChunkFactory()->countFilter([Factory::FILTER => [$incompleteFilter, $taskFilter]]);
            if ($count == 0) {
                $taskdone = true;
                DServerLog::log(DServerLog::INFO, 'Chunk is the last one and is completed and keyspace is reached', [$agent, $task, $chunk]);
            }
        }

        if ($taskdone) {
            $task = Factory::getTaskFactory()->set($task, Task::PRIORITY, 0);
            if ($taskWrapper->getTaskType() == DTaskTypes::SUPERTASK) {
                if (Util::checkTaskWrapperCompleted($taskWrapper)) {
                    $taskWrapper = Factory::getTaskWrapperFactory()->set($taskWrapper, TaskWrapper::PRIORITY, 0);
                }
            }
            else {
                $taskWrapper = Factory::getTaskWrapperFactory()->set($taskWrapper, TaskWrapper::PRIORITY, 0);
            }
            DServerLog::log(DServerLog::TRACE, 'As task is done, finished it and updated taskWrapper', [$agent, $task, $taskWrapper]);
            $payload = new DataSet([DPayloadKeys::TASK => $task]);
            NotificationHandler::checkNotifications(DNotificationType::TASK_COMPLETE, $payload);
        }

        $toZap = [];

        if ($sumCracked > 0) {
            $payload = new DataSet([DPayloadKeys::NUM_CRACKED => $sumCracked, DPayloadKeys::AGENT => $agent, DPayloadKeys::TASK => $task, DPayloadKeys::HASHLIST => $totalHashlist]);
            NotificationHandler::checkNotifications(DNotificationType::HASHLIST_CRACKED_HASH, $payload);
            Factory::getTaskWrapperFactory()->inc($taskWrapper, TaskWrapper::CRACKED, $sumCracked);
        }

        if ($aborting) {
            $chunk = Factory::getChunkFactory()->mset($chunk, [Chunk::SPEED => 0, Chunk::STATE => DHashcatStatus::ABORTED]);
            DServerLog::log(DServerLog::TRACE, 'From earlier setting, chunk needed to be aborted.', [$agent, $chunk]);
            return $this->error($response, PActions::SEND_PROGRESS, 'Chunk was aborted!');
        }

        switch ($state) {
            case DHashcatStatus::EXHAUSTED:
                $chunk = Factory::getChunkFactory()->mset($chunk, [Chunk::SPEED => 0, Chunk::PROGRESS => 10000, Chunk::CHECKPOINT => $chunk->getSkip() + $chunk->getLength()]);
                DServerLog::log(DServerLog::TRACE, 'Chunk is exhausted (cracker status)', [$agent, $chunk]);
                break;

            case DHashcatStatus::CRACKED:
                $chunk = Factory::getChunkFactory()->mset($chunk, [Chunk::CHECKPOINT => $chunk->getSkip() + $chunk->getLength(), Chunk::PROGRESS => 10000, Chunk::SPEED => 0]);
                DServerLog::log(DServerLog::TRACE, 'Last hash was cracked (cracker status)', [$agent, $chunk]);
                TaskUtils::depriorizeAllTasks($hashlists);
                TaskUtils::unassignAllAgents($hashlists);
                DServerLog::log(DServerLog::TRACE, 'Depriorized all tasks of the hashlist and unassigned all agents', [$agent, $totalHashlist]);
                $payload = new DataSet([DPayloadKeys::HASHLIST => $totalHashlist]);
                NotificationHandler::checkNotifications(DNotificationType::HASHLIST_ALL_CRACKED, $payload);
                break;

            case DHashcatStatus::ABORTED:
            case DHashcatStatus::QUIT:
                $chunk = Factory::getChunkFactory()->set($chunk, Chunk::SPEED, 0);
                return $this->error($response, PActions::SEND_PROGRESS, 'Chunk was aborted!');

            case DHashcatStatus::RUNNING:
            default:
                $qF1 = new ComparisonFilter(Hashlist::CRACKED, Hashlist::HASH_COUNT, '<');
                $qF2 = new ContainFilter(Hashlist::HASHLIST_ID, Util::arrayOfIds($hashlists));
                $count = Factory::getHashlistFactory()->countFilter([Factory::FILTER => [$qF1, $qF2]]);
                if ($count == 0) {
                    $payload = new DataSet([DPayloadKeys::HASHLIST => $totalHashlist]);
                    NotificationHandler::checkNotifications(DNotificationType::HASHLIST_ALL_CRACKED, $payload);
                    DServerLog::log(DServerLog::TRACE, 'Agent still is running, but all hashes got cracked (all agents together), stop it', [$agent]);
                    $chunk = Factory::getChunkFactory()->mset($chunk, [Chunk::CHECKPOINT => $chunk->getSkip() + $chunk->getLength(), Chunk::PROGRESS => 10000, Chunk::SPEED => 0]);
                    TaskUtils::depriorizeAllTasks($hashlists);
                    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), '=');
                    Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
                    $task = Factory::getTaskFactory()->set($task, Task::PRIORITY, 0);
                    DServerLog::log(DServerLog::TRACE, 'Depriorized all tasks and updated', [$agent, $task, $chunk, $totalHashlist]);
                    return $this->success($response, PActions::SEND_PROGRESS, [
                        PResponseSendProgress::NUM_CRACKED   => $sumCracked,
                        PResponseSendProgress::NUM_SKIPPED   => $skipped,
                        PResponseSendProgress::AGENT_COMMAND => 'stop',
                    ]);
                }
                $chunk = Factory::getChunkFactory()->set($chunk, Chunk::SPEED, $speed);
                if ($speed > 0) {
                    $s = new Speed(null, $agent->getId(), $task->getId(), $speed, time());
                    Factory::getSpeedFactory()->save($s);
                }
                $qF = new QueryFilter(AgentZap::AGENT_ID, $agent->getId(), '=');
                $agentZap = Factory::getAgentZapFactory()->filter([Factory::FILTER => $qF], true);
                if ($agentZap === null) {
                    $agentZap = new AgentZap(null, $agent->getId(), null);
                    Factory::getAgentZapFactory()->save($agentZap);
                }
                $qF1 = new ContainFilter(Zap::HASHLIST_ID, Util::arrayOfIds($hashlists));
                $qF2 = new QueryFilter(Zap::ZAP_ID, ($agentZap->getLastZapId() === null) ? 0 : $agentZap->getLastZapId(), '>');
                $qF3 = new QueryFilterWithNull(Zap::AGENT_ID, $agent->getId(), '<>', true);
                $zaps = Factory::getZapFactory()->filter([Factory::FILTER => [$qF1, $qF2, $qF3]]);
                foreach ($zaps as $zap) {
                    if ($zap->getId() > $agentZap->getId()) {
                        $agentZap->setLastZapId($zap->getId());
                    }
                    $toZap[] = $zap->getHash();
                }
                Factory::getAgentFactory()->set($agent, Agent::LAST_TIME, time());
                if ($agentZap->getLastZapId() > 0) {
                    Factory::getAgentZapFactory()->update($agentZap);
                }
                DServerLog::log(DServerLog::TRACE, 'Checked zaps and sending new ones to agent', [$agent, $zaps]);
                break;
        }

        Util::cleaning();
        return $this->success($response, PActions::SEND_PROGRESS, [
            PResponseSendProgress::NUM_CRACKED => $sumCracked,
            PResponseSendProgress::NUM_SKIPPED => $skipped,
            PResponseSendProgress::HASH_ZAPS  => $toZap,
        ]);
    }

    private function saveAgentStats(array $body, Agent $agent, int $dataTime): void {
        $stats = [
            [PQuerySendProgress::GPU_TEMP, DAgentStatsType::GPU_TEMP, '<='],
            [PQuerySendProgress::GPU_UTIL, DAgentStatsType::GPU_UTIL, '<'],
            [PQuerySendProgress::CPU_UTIL, DAgentStatsType::CPU_UTIL, '<'],
        ];
        foreach ($stats as [$field, $type, $op]) {
            if (!isset($body[$field])) {
                continue;
            }
            $values = $body[$field];
            for ($i = 0; $i < sizeof($values); $i++) {
                if (!is_numeric($values[$i]) || ($op == '<=' && $values[$i] <= 0) || ($op == '<' && $values[$i] < 0)) {
                    unset($values[$i]);
                }
            }
            if (sizeof($values) > 0) {
                $data = implode(',', $values);
                $agentStat = new AgentStat(null, $agent->getId(), $type, $dataTime, $data);
                Factory::getAgentStatFactory()->save($agentStat);
            }
        }
    }

    private function saveDebugOutput(array $body, Chunk $chunk): void {
        if (!isset($body[PQuerySendProgress::DEBUG_OUTPUT])) {
            return;
        }
        $lines = $body[PQuerySendProgress::DEBUG_OUTPUT];
        $taskDebugOutputs = [];
        foreach ($lines as $line) {
            $taskDebugOutputs[] = new TaskDebugOutput(null, $chunk->getTaskId(), $line);
        }
        if (sizeof($taskDebugOutputs) > 0) {
            Factory::getTaskDebugOutputFactory()->massSave($taskDebugOutputs);
        }
    }
  
  /**
   * @return array<string, mixed>|string  Returns 'skip' if the hash was skipped,
   *         or an array with plainUpdates, crackPosUpdates, timeUpdates, crackHashes, zaps.
   * @throws Exception
   */
    private function processPlainCrack(array $splitLine, array $hashlists, Chunk $chunk, Agent $agent, Hashlist $totalHashlist, bool $isNewWPA, array &$cracked): array|string {
        if ($isNewWPA) {
            $split = explode(':', $splitLine[0]);
            if (sizeof($split) == 4) {
                $split[3] = Util::strToHex($split[3]);
                $identifier = 'WPA*%*' . implode('*', $split) . '%';
            }
            else {
                return 'skip';
            }
            $qF1 = new LikeFilterInsensitive(Hash::HASH, $identifier);
        }
        else {
            $qF1 = new QueryFilter(Hash::HASH, $splitLine[0], '=');
        }

        $qF2 = new ContainFilter(Hash::HASHLIST_ID, Util::arrayOfIds($hashlists));
        $qF3 = new QueryFilter(Hash::IS_CRACKED, 0, '=');
        $hashes = Factory::getHashFactory()->filter([Factory::FILTER => [$qF1, $qF2, $qF3]]);
        if (sizeof($hashes) == 0) {
            $check = Factory::getHashFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
            if (sizeof($check) == 0) {
                $logMessage = 'Hash has been cracked but skipped! This happened while cracking hashlist with ID: '
                    . $totalHashlist->getId() . ' during chunk with ID: ' . $chunk->getId() . ' This happens when the agent returns
                   a cracked hash that does not exist in the database. This can happen when hashcat malforms the hash.';
                Util::createLogEntry(DLogEntryIssuer::API, $agent->getToken(), DLogEntry::FATAL, $logMessage);
                DServerLog::log(DServerLog::FATAL, $logMessage);
            }
            return 'skip';
        }

        if (sizeof($splitLine) == 5) {
            $plain = $splitLine[2];
            $crackPos = intval($splitLine[4]);
        }
        else {
            $plain = $splitLine[1];
            $crackPos = intval($splitLine[3]);
        }

        $plainUpdates = [];
        $crackPosUpdates = [];
        $timeUpdates = [];
        $crackHashes = [];
        $zaps = [];
        foreach ($hashes as $hash) {
            $cracked[$hash->getHashlistId()]++;
            $plainUpdates[] = new MassUpdateSet($hash->getId(), $plain);
            $crackPosUpdates[] = new MassUpdateSet($hash->getId(), $crackPos);
            $timeUpdates[] = new MassUpdateSet($hash->getId(), time());
            $crackHashes[] = $hash->getId();
            $zaps[] = new Zap(null, $hash->getHash(), time(), $agent->getId(), $totalHashlist->getId());
        }

        return [
            'plainUpdates' => $plainUpdates,
            'crackPosUpdates' => $crackPosUpdates,
            'timeUpdates' => $timeUpdates,
            'crackHashes' => $crackHashes,
            'zaps' => $zaps,
        ];
    }

    private function flushPlainBatch(array $plainUpdates, array $crackPosUpdates, array $timeUpdates, array $crackHashes, array $zaps, Chunk $chunk): void {
        $uS1 = new UpdateSet(Hash::CHUNK_ID, $chunk->getId());
        $uS2 = new UpdateSet(Hash::IS_CRACKED, 1);
        $qF = new ContainFilter(Hash::HASH_ID, $crackHashes);
        Factory::getHashFactory()->massSingleUpdate(Hash::HASH_ID, Hash::PLAINTEXT, $plainUpdates);
        Factory::getHashFactory()->massSingleUpdate(Hash::HASH_ID, Hash::CRACK_POS, $crackPosUpdates);
        Factory::getHashFactory()->massSingleUpdate(Hash::HASH_ID, Hash::TIME_CRACKED, $timeUpdates);
        Factory::getHashFactory()->massUpdate([Factory::UPDATE => $uS1, Factory::FILTER => $qF]);
        Factory::getHashFactory()->massUpdate([Factory::UPDATE => $uS2, Factory::FILTER => $qF]);
        Factory::getZapFactory()->massSave($zaps);
        Factory::getAgentFactory()->getDB()->commit();
        Factory::getAgentFactory()->getDB()->beginTransaction();
    }

    private function processWPACrack(array $splitLine, Chunk $chunk, Agent $agent, Hashlist $totalHashlist, array &$cracked): int {
        $split = explode(':', $splitLine[0]);
        if (sizeof($split) == 4) {
            $mac_ap = $split[1];
            $mac_cli = $split[2];
            $essid = $split[3];
        }
        else if (sizeof($split) == 3) {
            $mac_ap = $split[0];
            $mac_cli = $split[1];
            $essid = $split[2];
        }
        else {
            $mac_ap = $split[0];
            $mac_cli = $split[1];
            $essid = '';
        }
        if (Util::startsWith($essid, '$HEX[') && Util::endsWith($essid, ']') && strlen($essid) % 2 == 0) {
            $essid = substr($essid, 5, strlen($essid) - 6);
        }
        else if (sizeof($split) < 4) {
            $essid = Util::strToHex($essid);
        }
        $identification = $mac_ap . SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR) . $mac_cli;
        if (sizeof($split) > 2) {
            $identification .= SConfig::getInstance()->getVal(DConfig::FIELD_SEPARATOR) . $essid;
        }
        $plain = $splitLine[1];
        $crackPos = intval($splitLine[3]);
        $qF1 = new QueryFilter(HashBinary::ESSID, $identification, '=');
        $qF2 = new QueryFilter(HashBinary::IS_CRACKED, 0, '=');
        $hashes = Factory::getHashBinaryFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
        $skipped = 0;
        if (sizeof($hashes) == 0) {
            $skipped = 1;
        }
        foreach ($hashes as $hash) {
            $cracked[$hash->getHashlistId()]++;
            $hash->setIsCracked(1);
            $hash->setChunkId($chunk->getId());
            $hash->setPlaintext($plain);
            $hash->setCrackPos($crackPos);
            $hash->setTimeCracked(time());
            Factory::getHashBinaryFactory()->update($hash);
        }
        return $skipped;
    }

    private function processBinaryCrack(array $splitLine, Chunk $chunk, Agent $agent, Hashlist $totalHashlist, array &$cracked): int {
        $plain = $splitLine[1];
        $crackPos = intval($splitLine[3]);
        $qF1 = new QueryFilter(HashBinary::HASHLIST_ID, $totalHashlist->getId(), '=');
        $qF2 = new QueryFilter(HashBinary::IS_CRACKED, 0, '=');
        $hashes = Factory::getHashBinaryFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
        $skipped = 0;
        if (sizeof($hashes) == 0) {
            $skipped = 1;
        }
        foreach ($hashes as $hash) {
            $cracked[$hash->getHashlistId()]++;
            $hash->setIsCracked(1);
            $hash->setChunkId($chunk->getId());
            $hash->setPlaintext($plain);
            $hash->setCrackPos($crackPos);
            $hash->setTimeCracked(time());
            Factory::getHashBinaryFactory()->update($hash);
        }
        return $skipped;
    }
}
