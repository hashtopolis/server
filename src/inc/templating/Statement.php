<?php

namespace Hashtopolis\inc\templating;

use Hashtopolis\inc\UI;

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
  
  /**
   * current hack and easy way to make sure we have everything we need, we just include all. As it's legacy code it will
   * be removed on refactoring to get rid of any eval code.
   */
  private static array $useStatements = [
    'Hashtopolis\\inc\\utils\\AccessControl',
    'Hashtopolis\\inc\\utils\\AccessControlUtils',
    'Hashtopolis\\inc\\utils\\AccessGroupUtils',
    'Hashtopolis\\inc\\utils\\AccessUtils',
    'Hashtopolis\\inc\\utils\\AccountUtils',
    'Hashtopolis\\inc\\utils\\AgentBinaryUtils',
    'Hashtopolis\\inc\\utils\\AgentUtils',
    'Hashtopolis\\inc\\utils\\ApiUtils',
    'Hashtopolis\\inc\\utils\\AssignmentUtils',
    'Hashtopolis\\inc\\utils\\ChunkUtils',
    'Hashtopolis\\inc\\utils\\ConfigUtils',
    'Hashtopolis\\inc\\utils\\CrackerBinaryUtils',
    'Hashtopolis\\inc\\utils\\CrackerUtils',
    'Hashtopolis\\inc\\utils\\FileDownloadUtils',
    'Hashtopolis\\inc\\utils\\FileUtils',
    'Hashtopolis\\inc\\utils\\HashlistUtils',
    'Hashtopolis\\inc\\utils\\HashtypeUtils',
    'Hashtopolis\\inc\\utils\\HealthUtils',
    'Hashtopolis\\inc\\utils\\Lock',
    'Hashtopolis\\inc\\utils\\LockUtils',
    'Hashtopolis\\inc\\utils\\NotificationUtils',
    'Hashtopolis\\inc\\utils\\PreprocessorUtils',
    'Hashtopolis\\inc\\utils\\PretaskUtils',
    'Hashtopolis\\inc\\utils\\RunnerUtils',
    'Hashtopolis\\inc\\utils\\SupertaskUtils',
    'Hashtopolis\\inc\\utils\\TaskUtils',
    'Hashtopolis\\inc\\utils\\TaskWrapperUtils',
    'Hashtopolis\\inc\\utils\\UserUtils',
    'Hashtopolis\\inc\\defines\\DAccessControl',
    'Hashtopolis\\inc\\defines\\DAccessControlAction',
    'Hashtopolis\\inc\\defines\\DAccessGroupAction',
    'Hashtopolis\\inc\\defines\\DAccessLevel',
    'Hashtopolis\\inc\\defines\\DAccountAction',
    'Hashtopolis\\inc\\defines\\DAgentAction',
    'Hashtopolis\\inc\\defines\\DAgentBinaryAction',
    'Hashtopolis\\inc\\defines\\DAgentIgnoreErrors',
    'Hashtopolis\\inc\\defines\\DAgentStatsType',
    'Hashtopolis\\inc\\defines\\DApiAction',
    'Hashtopolis\\inc\\defines\\DCleaning',
    'Hashtopolis\\inc\\defines\\DConfig',
    'Hashtopolis\\inc\\defines\\DConfigAction',
    'Hashtopolis\\inc\\defines\\DConfigType',
    'Hashtopolis\\inc\\defines\\DCrackerBinaryAction',
    'Hashtopolis\\inc\\defines\\DDeviceCompress',
    'Hashtopolis\\inc\\defines\\DDirectories',
    'Hashtopolis\\inc\\defines\\DFileAction',
    'Hashtopolis\\inc\\defines\\DFileDownloadStatus',
    'Hashtopolis\\inc\\defines\\DFileType',
    'Hashtopolis\\inc\\defines\\DForgotAction',
    'Hashtopolis\\inc\\defines\\DHashcatStatus',
    'Hashtopolis\\inc\\defines\\DHashlistAction',
    'Hashtopolis\\inc\\defines\\DHashlistFormat',
    'Hashtopolis\\inc\\defines\\DHashtypeAction',
    'Hashtopolis\\inc\\defines\\DHealthCheck',
    'Hashtopolis\\inc\\defines\\DHealthCheckAction',
    'Hashtopolis\\inc\\defines\\DHealthCheckAgentStatus',
    'Hashtopolis\\inc\\defines\\DHealthCheckMode',
    'Hashtopolis\\inc\\defines\\DHealthCheckStatus',
    'Hashtopolis\\inc\\defines\\DHealthCheckType',
    'Hashtopolis\\inc\\defines\\DLimits',
    'Hashtopolis\\inc\\defines\\DLogEntry',
    'Hashtopolis\\inc\\defines\\DLogEntryIssuer',
    'Hashtopolis\\inc\\defines\\DNotificationAction',
    'Hashtopolis\\inc\\defines\\DNotificationObjectType',
    'Hashtopolis\\inc\\defines\\DNotificationType',
    'Hashtopolis\\inc\\defines\\DOperatingSystem',
    'Hashtopolis\\inc\\defines\\DPayloadKeys',
    'Hashtopolis\\inc\\defines\\DPlatforms',
    'Hashtopolis\\inc\\defines\\DPreprocessorAction',
    'Hashtopolis\\inc\\defines\\DPretaskAction',
    'Hashtopolis\\inc\\defines\\DPrince',
    'Hashtopolis\\inc\\defines\\DProxyTypes',
    'Hashtopolis\\inc\\defines\\DSearchAction',
    'Hashtopolis\\inc\\defines\\DServerLog',
    'Hashtopolis\\inc\\defines\\DStats',
    'Hashtopolis\\inc\\defines\\DSupertaskAction',
    'Hashtopolis\\inc\\defines\\DTaskAction',
    'Hashtopolis\\inc\\defines\\DTaskStaticChunking',
    'Hashtopolis\\inc\\defines\\DTaskTypes',
    'Hashtopolis\\inc\\defines\\DUserAction',
    'Hashtopolis\\inc\\defines\\DViewControl',
    'Hashtopolis\\inc\\defines\\UApi',
    'Hashtopolis\\inc\\defines\\UQuery',
    'Hashtopolis\\inc\\defines\\UQueryAccess',
    'Hashtopolis\\inc\\defines\\UQueryAccount',
    'Hashtopolis\\inc\\defines\\UQueryAgent',
    'Hashtopolis\\inc\\defines\\UQueryConfig',
    'Hashtopolis\\inc\\defines\\UQueryCracker',
    'Hashtopolis\\inc\\defines\\UQueryFile',
    'Hashtopolis\\inc\\defines\\UQueryGroup',
    'Hashtopolis\\inc\\defines\\UQueryHashlist',
    'Hashtopolis\\inc\\defines\\UQuerySuperhashlist',
    'Hashtopolis\\inc\\defines\\UQueryTask',
    'Hashtopolis\\inc\\defines\\UQueryUser',
    'Hashtopolis\\inc\\defines\\UResponse',
    'Hashtopolis\\inc\\defines\\UResponseAccess',
    'Hashtopolis\\inc\\defines\\UResponseAccount',
    'Hashtopolis\\inc\\defines\\UResponseAgent',
    'Hashtopolis\\inc\\defines\\UResponseConfig',
    'Hashtopolis\\inc\\defines\\UResponseCracker',
    'Hashtopolis\\inc\\defines\\UResponseErrorMessage',
    'Hashtopolis\\inc\\defines\\UResponseFile',
    'Hashtopolis\\inc\\defines\\UResponseGroup',
    'Hashtopolis\\inc\\defines\\UResponseHashlist',
    'Hashtopolis\\inc\\defines\\UResponseSuperhashlist',
    'Hashtopolis\\inc\\defines\\UResponseTask',
    'Hashtopolis\\inc\\defines\\UResponseUser',
    'Hashtopolis\\inc\\defines\\USection',
    'Hashtopolis\\inc\\defines\\USectionAccess',
    'Hashtopolis\\inc\\defines\\USectionAccount',
    'Hashtopolis\\inc\\defines\\USectionAgent',
    'Hashtopolis\\inc\\defines\\USectionConfig',
    'Hashtopolis\\inc\\defines\\USectionCracker',
    'Hashtopolis\\inc\\defines\\USectionFile',
    'Hashtopolis\\inc\\defines\\USectionGroup',
    'Hashtopolis\\inc\\defines\\USectionHashlist',
    'Hashtopolis\\inc\\defines\\USectionPretask',
    'Hashtopolis\\inc\\defines\\USectionSuperhashlist',
    'Hashtopolis\\inc\\defines\\USectionSupertask',
    'Hashtopolis\\inc\\defines\\USectionTask',
    'Hashtopolis\\inc\\defines\\USectionTest',
    'Hashtopolis\\inc\\defines\\USectionUser',
    'Hashtopolis\\inc\\defines\\UValues',
    'Hashtopolis\\inc\\CSRF',
    'Hashtopolis\\inc\\DataSet',
    'Hashtopolis\\inc\\Encryption',
    'Hashtopolis\\inc\\Lang',
    'Hashtopolis\\inc\\Login',
    'Hashtopolis\\inc\\Menu',
    'Hashtopolis\\inc\\SConfig',
    'Hashtopolis\\inc\\StartupConfig',
    'Hashtopolis\\inc\\UI',
    'Hashtopolis\\inc\\Util',
  ];
  
  private static array $namespaces = [
    'Hashtopolis\\inc',
    'Hashtopolis\\inc\\defines',
    'Hashtopolis\\inc\\utils',
  ];
  
  public function render($objects) {
    global $LANG;
    
    $output = "";
    switch ($this->statementType) {
      case 'IF': //setting -> array(condition, else position)
        $condition = $this->renderContent($this->setting[0], $objects, true);
        if (eval(Statement::getPrefix() . "return $condition;")) {
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
  
  // The functions getPrefix() and isCallable() are needed as a workaround for the templating to work with the current
  // way of namespaces
  private static function getPrefix(): string {
    $statements = "";
    foreach (self::$useStatements as $s) {
      $statements .= "use " . $s . ";";
    }
    return $statements;
  }
  
  private static function isCallable(string $methodString) {
    // split into class and method parts
    if (!strpos($methodString, '::')) {
      return is_callable($methodString); // global methods
    }
    [$classPart, $method] = explode('::', $methodString, 2);
    
    $makeFQ = fn($cls) => '\\' . trim($cls, '\\');
    
    $fqClass = $makeFQ($classPart);
    $candidate = $fqClass . '::' . $method;
    if (is_callable($candidate)) {
      return true;
    }
    
    foreach (Statement::$namespaces as $ns) {
      $fqClass = $makeFQ($ns . '\\' . $classPart);
      $candidate = $fqClass . '::' . $method;
      if (is_callable($candidate)) {
        return true;
      }
    }
    
    // nothing matched
    return false;
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
        return eval(Statement::getPrefix() . "return \$objects['$varname']$calls;");
      }
    }
    else if (isset($objects[preg_replace('/\[.*\] /', "", $value)])) {
      //is a array (this case is not very good to use, it cannot be used with inner variables)
      $varname = substr($varname, 0, strpos($varname, "["));
      if ($inner) {
        return "\$objects['$varname']" . str_replace($varname . "[", "", str_replace("] ", "", $value));
      }
      else {
        return eval(Statement::getPrefix() . "return \$objects['$varname'][" . str_replace($varname . "[", "", str_replace("] ", "", $value)) . "];");
      }
    }
    else if (Statement::isCallable(preg_replace('/\(.*\)/', "", $value))) {
      //is a static function call
      if ($inner) {
        return "$value";
      }
      else {
        return eval(Statement::getPrefix() . "return $value;");
      }
    }
    else if (strpos($value, '$') === 0) {
      // is a constant
      if ($inner) {
        return substr($value, 1);
      }
      else {
        return eval(Statement::getPrefix() . "return " . substr($value, 1) . ";");
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