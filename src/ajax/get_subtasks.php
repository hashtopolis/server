<?php

use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\Factory;

require_once(dirname(__FILE__) . "/../inc/load.php");
set_time_limit(0);
header("Content-Type: application/json");

$QUERY = file_get_contents('php://input');
parse_str($QUERY, $output);

$start = intval($output['start']);
$length = intval($output['length']);

$oF = new OrderFilter(Task::PRIORITY, "DESC LIMIT $start,$length");
$qF = new QueryFilter(Task::TASK_WRAPPER_ID, $output["taskWrapperId"], "=");
$total_subtasks_count = Factory::getTaskFactory()->countFilter([Factory::FILTER => $qF]);

$subtasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);

$accessGroups = AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser());

$subtasks_json = array(
  "draw"            => intval( $output['draw'] ),
  "recordsTotal"    => intval( $total_subtasks_count ),
  "recordsFiltered" => intval( $total_subtasks_count ),
  "data"            => array()
);
if ($subtasks != null) {
  for ($i = 0; $i < count($subtasks); $i++) {
    $taskInfo = Util::getTaskInfo($subtasks[$i]);
    $chunkInfo = Util::getChunkInfo($subtasks[$i]);
    $fileInfo = Util::getFileInfo($subtasks[$i], $accessGroups);
    //$subtasks_json["data"][$i] = $subtasks[$i]->getKeyValueDict();
    $subtasks_json["data"][$i] = array(
        $subtasks[$i]->getId(),
        $subtasks[$i]->getTaskName(),
        Util::showperc($subtasks[$i]->getKeyspaceProgress(), $subtasks[$i]->getKeyspace()) . "% / " . Util::showperc($taskInfo[0], $subtasks[$i]->getKeyspace()). "%",
        $taskInfo[1],
        $chunkInfo[2],
        $fileInfo[0],
        $subtasks[$i]->getPriority(),
        "TODO"
    );
  }
}

echo json_encode($subtasks_json);
die();
