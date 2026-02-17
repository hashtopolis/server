<?php

namespace Hashtopolis\inc\templating;

use Hashtopolis\inc\UI;

/**
 * current hack and easy way to make sure we have everything we need, we just include all. As it's legacy code it will
 * be removed on refactoring to get rid of any eval code.
 */
define("EVAL_PREFIX", "use Hashtopolis\inc\{
  CSRF,
  DataSet,
  Encryption,
  Lang,
  Login,
  Menu,
  SConfig,
  StartupConfig,
  UI,
  Util,
};
use Hashtopolis\inc\defines\{
  DAccessControlAction,
  DAccessControl,
  DAccessGroupAction,
  DAccessLevel,
  DAccountAction,
  DAgentAction,
  DAgentBinaryAction,
  DAgentIgnoreErrors,
  DAgentStatsType,
  DApiAction,
  DCleaning,
  DConfigAction,
  DConfig,
  DConfigType,
  DCrackerBinaryAction,
  DDeviceCompress,
  DDirectories,
  DFileAction,
  DFileDownloadStatus,
  DFileType,
  DForgotAction,
  DHashcatStatus,
  DHashlistAction,
  DHashlistFormat,
  DHashtypeAction,
  DHealthCheckAction,
  DHealthCheckAgentStatus,
  DHealthCheckMode,
  DHealthCheck,
  DHealthCheckStatus,
  DHealthCheckType,
  DLimits,
  DLogEntryIssuer,
  DLogEntry,
  DNotificationAction,
  DNotificationObjectType,
  DNotificationType,
  DOperatingSystem,
  DPayloadKeys,
  DPlatforms,
  DPreprocessorAction,
  DPretaskAction,
  DPrince,
  DProxyTypes,
  DSearchAction,
  DServerLog,
  DStats,
  DSupertaskAction,
  DTaskAction,
  DTaskStaticChunking,
  DTaskTypes,
  DUserAction,
  DViewControl,
  UApi,
  UQueryAccess,
  UQueryAccount,
  UQueryAgent,
  UQueryConfig,
  UQueryCracker,
  UQueryFile,
  UQueryGroup,
  UQueryHashlist,
  UQuery,
  UQuerySuperhashlist,
  UQueryTask,
  UQueryUser,
  UResponseAccess,
  UResponseAccount,
  UResponseAgent,
  UResponseConfig,
  UResponseCracker,
  UResponseErrorMessage,
  UResponseFile,
  UResponseGroup,
  UResponseHashlist,
  UResponse,
  UResponseSuperhashlist,
  UResponseTask,
  UResponseUser,
  USectionAccess,
  USectionAccount,
  USectionAgent,
  USectionConfig,
  USectionCracker,
  USectionFile,
  USectionGroup,
  USectionHashlist,
  USection,
  USectionPretask,
  USectionSuperhashlist,
  USectionSupertask,
  USectionTask,
  USectionTest,
  USectionUser,
  UValues,
};
use Hashtopolis\inc\utils\{
  AccessControl,
  AccessControlUtils,
  AccessGroupUtils,
  AccessUtils,
  AccountUtils,
  AgentBinaryUtils,
  AgentUtils,
  ApiUtils,
  AssignmentUtils,
  ChunkUtils,
  ConfigUtils,
  CrackerBinaryUtils,
  CrackerUtils,
  FileDownloadUtils,
  FileUtils,
  HashlistUtils,
  HashtypeUtils,
  HealthUtils,
  Lock,
  LockUtils,
  NotificationUtils,
  PreprocessorUtils,
  PretaskUtils,
  RunnerUtils,
  SupertaskUtils,
  TaskUtils,
  TaskWrapperUtils,
  UserUtils,
};");

class Statement {
  private $statementType; //if, for, foreach, content
  /**
   * @var Statement[]
   */
  private $content; //array of statements or string
  private $setting; //settings for statement
  
  public function __construct($type, $content, $setting) {
    $this->content = $content;
    $this->setting = $setting;
    $this->statementType = $type;
  }
  
  public function render($objects) {
    global $LANG;
    
    $output = "";
    switch ($this->statementType) {
      case 'IF': //setting -> array(condition, else position)
        $condition = $this->renderContent($this->setting[0], $objects, true);
        if (eval(EVAL_PREFIX . "return $condition;")) {
          //if statement is true
          for ($x = 0; $x < sizeof($this->content); $x++) {
            if ($x == $this->setting[1]) {
              break; //we reached the position of the else statement, we don't execute this
            }
            $output .= $this->content[$x]->render($objects);
          }
        }
        else {
          //if statement is false
          if ($this->setting[1] != -1) {
            for ($x = $this->setting[1]; $x < sizeof($this->content); $x++) {
              $output .= $this->content[$x]->render($objects);
            }
          }
        }
        break;
      case 'FOR': //setting -> array(varname, start, end)
        $start = $this->renderContent($this->setting[1], $objects);
        $end = $this->renderContent($this->setting[2], $objects);
        for ($x = $start; $x < $end; $x++) {
          $objects[$this->setting[0]] = $x;
          foreach ($this->content as $stat) {
            $output .= $stat->render($objects);
          }
        }
        unset($objects[$this->setting[0]]);
        break;
      case 'FOREACH': //setting -> array(varname, arr [, counter])
        $arr = $this->renderContent($this->setting[1], $objects);
        $counter = 0;
        foreach ($arr as $entry) {
          $objects[$this->setting[0]] = $entry;
          if (isset($this->setting[2])) {
            $objects[$this->setting[2]] = $counter;
          }
          foreach ($this->content as $stat) {
            $output .= $stat->render($objects);
          }
          $counter++;
        }
        if (isset($this->setting[2])) {
          unset($objects[$this->setting[2]]);
        }
        break;
      case 'CONTENT': //setting -> nothing
        $output .= $LANG->render($this->renderContent($this->content, $objects));
        break;
      default:
        UI::printFatalError("Unknown Statement '" . $this->statementType . "'!");
        break;
    }
    return $output;
  }
  
  private function renderContent($content, $objects, $inner = false) {
    $pos = 0;
    $output = "";
    while ($pos < strlen($content)) {
      $varPos = strpos($content, "[[", $pos);
      if ($varPos === false) {
        if ($pos == 0) {
          return $content;
        }
        $output .= substr($content, $pos);
        return $output;
      }
      $result = $this->renderVariable(substr($content, $varPos), $objects, $inner);
      if ($result === false) {
        UI::printFatalError("Variable starting at $varPos not closed!");
      }
      $output .= substr($content, $pos, $varPos - $pos);
      if (strlen($output) == 0) {
        $output = $result[0]; //required to handle passed arrays
      }
      else {
        $output .= $result[0];
      }
      $pos = $varPos + $result[1];
    }
    return $output;
  }
  
  private function renderVariable($content, $objects, $inner = false) {
    $opencount = 1;
    $pos = 2;
    while ($opencount > 0) {
      if ($pos > strlen($content)) {
        UI::printFatalError("Syntax error when parsing variable $content, not closed!");
      }
      $nextOpen = strpos($content, "[[", $pos);
      $nextClose = strpos($content, "]]", $pos);
      if ($nextOpen === false && $nextClose === false) {
        UI::printFatalError("Syntax error when parsing variable $content!");
      }
      else if ($nextOpen === false) {
        $opencount--;
        $pos = $nextClose + 2;
      }
      else if ($nextClose === false) {
        $opencount++;
        $pos = $nextOpen + 2;
      }
      else if ($nextClose < $nextOpen) {
        $opencount--;
        $pos = $nextClose + 2;
      }
      else {
        $opencount++;
        $pos = $nextOpen + 2;
      }
    }
    $varcontent = substr($content, 2, $pos - 4);
    if (strpos($varcontent, "[[") === false) {
      $output = $this->evalResult($varcontent, $objects, $inner);
    }
    else {
      $output = $this->renderContent($varcontent, $objects, true);
      $output = $this->evalResult($output, $objects, $inner);
    }
    return array($output, $pos);
  }
  
  private function evalResult($value, $objects, $inner) {
    $vals = explode(".", $value);
    $varname = $vals[0];
    unset($vals[0]);
    $calls = implode("->", $vals);
    if (strlen($calls) > 0) {
      $calls = "->$calls";
    }
    if (isset($objects[$varname])) {
      //is a variable/object provided in objects
      if ($inner) {
        return "\$objects['$varname']$calls";
      }
      else {
        return eval(EVAL_PREFIX . "return \$objects['$varname']$calls;");
      }
    }
    else if (isset($objects[preg_replace('/\[.*\] /', "", $value)])) {
      //is a array (this case is not very good to use, it cannot be used with inner variables)
      $varname = substr($varname, 0, strpos($varname, "["));
      if ($inner) {
        return "\$objects['$varname']" . str_replace($varname . "[", "", str_replace("] ", "", $value));
      }
      else {
        return eval(EVAL_PREFIX . "return \$objects['$varname'][" . str_replace($varname . "[", "", str_replace("] ", "", $value)) . "];");
      }
    }
    else if (is_callable(preg_replace('/\(.*\)/', "", $value))) {
      //is a static function call
      if ($inner) {
        return "$value";
      }
      else {
        return eval(EVAL_PREFIX . "return $value;");
      }
    }
    else if (strpos($value, '$') === 0) {
      // is a constant
      if ($inner) {
        return substr($value, 1);
      }
      else {
        return eval(EVAL_PREFIX . "return " . substr($value, 1) . ";");
      }
    }
    else {
      if (ini_get("display_errors") == '1') {
        echo "WARN: failed to parse: $value<br>\n";
      }
      return "false";
    }
  }
}