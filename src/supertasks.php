<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 5) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("supertasks/index");
$MENU->setActive("tasks_super");

//catch actions here...
if (isset($_POST['action'])) {
  switch ($_POST['action']) {
    case 'taskdelete':
      $supertask = intval($_POST['supertask']);
      $res = $DB->query("SELECT * FROM Supertask WHERE supertaskId=$supertask");
      $supertask = $res->fetch();
      if (!$supertask) {
        $message = "<div class='alert alert-danger'>Invalid Supertask!</div>";
        break;
      }
      $DB->query("START TRANSACTION");
      $DB->query("DELETE FROM SupertaskTask WHERE supertaskId=" . $supertask['supertaskId']);
      $DB->query("DELETE FROM Supertask WHERE supertaskId=" . $supertask['supertaskId']);
      $DB->query("COMMIT");
      header("Location: supertasks.php");
      die();
      break;
    case 'newsupertask':
      $orig = intval($_POST['supertask']);
      $hashlist = intval($_POST['hashlist']);
      $res = $DB->query("SELECT * FROM Supertask WHERE supertaskId=" . $DB->quote($orig));
      $supertask = $res->fetch();
      if (!$supertask) {
        $message = "<div class='alert alert-danger'>Invalid Supertask!</div>";
        break;
      }
      $res = $DB->query("SELECT * FROM hashlists WHERE id=" . $DB->quote($hashlist));
      $hashlist = $res->fetch();
      if (!$hashlist) {
        $message = "<div class='alert alert-danger'>Invalid Hashlist!</div>";
        break;
      }
    
      $res = $DB->query("SELECT tasks.* FROM SupertaskTask INNER JOIN tasks ON tasks.id=SupertaskTask.taskId WHERE supertaskId=" . $supertask['supertaskId']);
      $res = $res->fetchAll();
      foreach ($res as $task) {
        $DB = $FACTORIES::getagentsFactory()->getDB();
        $name = $DB->quote(htmlentities($task['name'], false, "UTF-8"));
        $cmdline = $DB->quote($task["attackcmd"]);
        $autoadj = intval($task["autoadjust"]);
        $chunk = intval($task["chunktime"]);
        $status = intval($task["statustimer"]);
        $priority = intval($task['priority']);
        $color = $task["color"];
        $message = "<div class='alert alert-neutral'>";
        $forward = "";
        if (preg_match("/[0-9A-Za-z]{6}/", $color) == 1) {
          $color = "'$color'";
        }
        else {
          $color = "NULL";
        }
        if (strpos($cmdline, $CONFIG->getVal('hashlistAlias')) === false) {
          $message .= "Command line must contain hashlist (" . $CONFIG->getVal('hashlistAlias') . ").";
        }
        else {
          $thashlist = intval($_POST["hashlist"]);
          if ($thashlist > 0) {
            $hashlist = $thashlist;
          }
          if ($name == "''") {
            $name = "HL" . $hashlist . "_" . date("Ymd_Hi");
          }
          if ($hashlist != "") {
            if ($status > 0 && $chunk > 0 && $chunk > $status) {
              if ($hashlist != "NULL") {
                $res = $DB->query("SELECT * FROM hashlists WHERE id=" . $hashlist);
                $hl = $res->fetch();
                if ($hl['hexsalt'] == 1 && strpos($cmdline, "--hex-salt") === false) {
                  $cmdline = "'--hex-salt " . substr($cmdline, 1, -1) . "'";
                }
              }
              $DB->exec("SET autocommit = 0");
              $DB->exec("START TRANSACTION");
              $message .= "Creating task in the DB...";
              $res = $DB->exec("INSERT INTO tasks (name, attackcmd, hashlist, chunktime, statustimer, autoadjust, color, priority) VALUES ($name, $cmdline, $hashlist, $chunk, $status, $autoadj, $color, $priority)");
              if ($res) {
                // insert succeeded
                $id = $DB->lastInsertId();
                $message .= "OK (id: $id)<br>";
                // attach files
                $attachok = true;
                $ans = $DB->query("SELECT * FROM taskfiles WHERE task=" . $task['id']);
                $ans = $ans->fetchAll();
              
                if (sizeof($ans) > 0) {
                  foreach ($ans as $fid) {
                    if ($fid['file'] > 0) {
                      $message .= "Attaching file {$fid['file']}...";
                      if ($DB->exec("INSERT INTO taskfiles (task,file) VALUES ($id, {$fid['file']})")) {
                        $message .= "OK";
                      }
                      else {
                        $message .= "ERROR!";
                        $attachok = false;
                      }
                      $message .= "<br>";
                    }
                  }
                }
                if ($attachok == true) {
                  $DB->exec("COMMIT");
                  $message .= "Task created successfuly!";
                }
                else {
                  $DB->exec("ROLLBACK");
                }
              }
              else {
                $message .= "ERROR: " . $DB->errorInfo()[2];
              }
            }
            else {
              $message .= "Chunk time must be higher than status timer.";
            }
          }
          else {
            $message .= "Every task requires a hashlist, even if it should contain only one hash.";
          }
        }
        $message .= "</div>";
      }
      header("Location: tasks.php");
      die();
      break;
    case 'createsupertask':
      $name = htmlentities($_POST['name'], false, "UTF-8");
      $tasks = $_POST['task'];
      $DB->query("START TRANSACTION");
      $DB->query("INSERT INTO Supertask (name) VALUES (" . $DB->quote($name) . ")");
      $sid = $DB->lastInsertId();
      foreach ($tasks as $task) {
        $res = $DB->query("SELECT * FROM tasks WHERE id=" . intval($task));
        $task = $res->fetch();
        if ($task) {
          $DB->query("INSERT INTO SupertaskTask (supertaskId, taskId) VALUES ('$sid', '{$task['id']}')");
        }
      }
      $DB->query("COMMIT");
      header("Location: supertasks.php");
      die();
      break;
  }
}

if(isset($_GET['create'])){
  $MENU->setActive("tasks_supernew");
  $TEMPLATE = new Template("supertasks/create");
  $qF = new QueryFilter("hashlistId", null, "=");
  $OBJECTS['preTasks'] = $FACTORIES::getTaskFactory()->filter(array('filter' => $qF));
}
else if (isset($_GET['id']) && isset($_GET['new'])) {
  $TEMPLATE = new Template("supertasks/new");
  $supertask = $FACTORIES::getSupertaskFactory()->get($_GET['id']);
  $OBJECTS['orig'] = $supertask->getId();
  $OBJECTS['lists'] = $FACTORIES::getHashlistFactory()->filter(array());
}
else if (isset($_GET['id'])){
  $TEMPLATE = new Template("supertasks/detail");
  $supertask = $FACTORIES::getSupertaskFactory()->get($_GET['id']);
  if($supertask == null){
    UI::printError("ERROR", "Invalid supertask ID!");
  }
  $qF = new QueryFilter("supertaskId", $supertask->getId(), "=", $FACTORIES::getSupertaskTaskFactory());
  $jF = new JoinFilter($FACTORIES::getSupertaskTaskFactory(), "taskId", "taskId");
  $tasks = $FACTORIES::getTaskFactory()->filter(array('filter' => $qF, 'join' => $jF));
  $OBJECTS['tasks'] = $tasks['Task'];
  $OBJECTS['supertask'] = $supertask;
}
else {
  $supertasks = $FACTORIES::getSupertaskFactory()->filter(array());
  $supertaskTasks = new DataSet();
  foreach($supertasks as $supertask){
    $qF = new QueryFilter("supertaskId", $supertask->getId(), "=", $FACTORIES::getSupertaskTaskFactory());
    $jF = new JoinFilter($FACTORIES::getSupertaskTaskFactory(), "taskId", "taskId");
    $joinedTasks = $FACTORIES::getTaskFactory()->filter(array('filter' => $qF, 'join' => $jF));
    $tasks = $joinedTasks['Task'];
    $supertaskTasks->addValue($supertask->getId(), $tasks);
  }
  $OBJECTS['tasks'] = $supertaskTasks;
  $OBJECTS['supertasks'] = $supertasks;
}

echo $TEMPLATE->render($OBJECTS);




