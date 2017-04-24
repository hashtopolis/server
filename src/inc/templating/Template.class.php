<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 13.11.16
 * Time: 21:56
 */
class Template {
  private $content;
  private $statements;
  
  public function __construct($template, $direct = false) {
    if ($direct) {
      $this->content = $template;
    }
    else {
      $path = dirname(__FILE__) . "/../../templates/" . $template . ".template.html";
      if (!file_exists($path)) {
        $path = dirname(__FILE__) . "/../../templates/" . $template;
        if (!file_exists($path)) {
          if (ini_get("display_errors") == 1) {
            echo "ERROR: Template $template not found!\n";
          }
          return false;
        }
      }
      $this->content = file_get_contents($path);
    }
    
    $this->statements = array();
    $this->resolveDependencies();
    $parsed = $this->parse($this->content);
    $this->statements = $parsed[0];
    return true;
  }
  
  public function getContent() {
    return $this->content;
  }
  
  public function render($objects) {
    $output = "";
    foreach ($this->statements as $statement) {
      /** @var Statement $statement */
      $output .= $statement->render($objects);
    }
    return $output;
  }
  
  public function getStatements() {
    return $this->statements;
  }
  
  private function parse($content) {
    $pos = 0;
    $statements = array();
    while ($pos < strlen($content)) {
      $loopPos = strpos($content, "{{", $pos);
      if ($loopPos !== false) {
        //check if we detected a finish of a parent loop
        if ($loopPos === strpos($content, "{{END", $pos) || $loopPos === strpos($content, "{{ELSE}}", $pos)) {
          $subContent = substr($content, $pos, $loopPos - $pos);
          if (strlen($subContent) > 0) {
            $contentStatement = new Statement("CONTENT", $subContent, array());
            $statements[] = $contentStatement;
          }
          return array($statements, $loopPos);
        }
        
        //create statement from the content before the loop starts
        $subContent = substr($content, $pos, $loopPos - $pos);
        if (strlen($subContent) > 0) {
          $contentStatement = new Statement("CONTENT", $subContent, array());
          $statements[] = $contentStatement;
        }
        
        $loopType = substr($content, $loopPos + 2, strpos($content, " ", $loopPos + 2) - $loopPos - 2);
        switch ($loopType) {
          case 'IF':
            $nextPos = strpos($content, "{{", $loopPos + 2);
            $closePos = strpos($content, "{{ENDIF}}", $loopPos + 2);
            $elsePos = strpos($content, "{{ELSE}}", $loopPos + 2);
            if ($nextPos === false || $closePos === false) {
              UI::printFatalError("Syntax error: IF statement at $loopPos not closed!");
            }
            //get the condition for this if
            $startCondition = $loopPos + 5;
            $endCondition = strpos($content, "}}", $startCondition);
            $setting = array(substr($content, $startCondition, $endCondition - $startCondition), -1);
            if ($nextPos == $closePos) {
              //we have a single if statement
              //create statement of the content inside the if
              $startContent = $endCondition + 2;
              $endContent = $closePos;
              $subContent = substr($content, $startContent, $endContent - $startContent);
              $contentStatement = new Statement("CONTENT", $subContent, array());
              
              //create if statement
              $ifStatement = new Statement("IF", array($contentStatement), $setting);
              $statements[] = $ifStatement;
              $pos = $closePos + 9;
            }
            else {
              //there is some inner statement inside the if
              if ($elsePos !== false/* && $elsePos < $closePos*/) {
                $ifContent = array();
                //check if the else is the next position
                if ($elsePos == $nextPos) {
                  //until the else statement we have a clean if
                  $startContent = $endCondition + 2;
                  $endContent = $elsePos;
                  $subContent = substr($content, $startContent, $endContent - $startContent);
                  $contentStatement = new Statement("CONTENT", $subContent, array());
                  $ifContent[] = $contentStatement;
                  $elsePosition = sizeof($ifContent);
                  
                  //check after the else
                  $nextPos = strpos($content, "{{", $elsePos + 2);
                  if ($nextPos == $closePos) {
                    //there is no other statement between else and endif
                    $startContent = $elsePos + 8;
                    $endContent = $closePos;
                    $subContent = substr($content, $startContent, $endContent - $startContent);
                    $contentStatement = new Statement("CONTENT", $subContent, array());
                    $ifContent[] = $contentStatement;
                    $pos = $closePos + 9;
                  }
                  else {
                    //there is some other statement between the else and the endif
                    $innerContent = substr($content, $elsePos + 8);
                    $result = $this->parse($innerContent);
                    $endPos = $result[1] + $elsePos + 8;
                    if ($endPos != strpos($content, "{{ENDIF}}", $endPos)) {
                      UI::printFatalError("IF statement not closed correctly at $endPos!");
                    }
                    foreach ($result[0] as $stat) {
                      $ifContent[] = $stat;
                    }
                    $pos = $endPos + 9;
                  }
                }
                else {
                  //there is some inner statement until the else statement
                  $startContent = $endCondition + 2;
                  $innerContent = substr($content, $startContent);
                  $result = $this->parse($innerContent);
                  $elsePos = $result[1] + $startContent;
                  if ($elsePos != strpos($content, "{{ELSE}}", $elsePos)) {
                    if ($elsePos != strpos($content, "{{ENDIF}}", $elsePos)) {
                      UI::printFatalError("IF statement, else not correctly at $elsePos!");
                    }
                    foreach ($result[0] as $stat) {
                      $ifContent[] = $stat;
                    }
                    $closePos = $elsePos;
                    /*$startContent = $elsePos + 8;
                    $endContent = $closePos;
                    $subContent = substr($content, $startContent, $endContent - $startContent);
                    $contentStatement = new Statement("CONTENT", $subContent, array());
                    $ifContent[] = $contentStatement;*/
                    $pos = $closePos + 9;
                    $elsePosition = -1;
                  }
                  else {
                    foreach ($result[0] as $stat) {
                      $ifContent[] = $stat;
                    }
                    $elsePosition = sizeof($ifContent);
                    
                    $nextPos = strpos($content, "{{", $elsePos + 2);
                    if ($nextPos == $closePos) {
                      //there is no other statement between else and endif
                      $startContent = $elsePos + 8;
                      $endContent = $closePos;
                      $subContent = substr($content, $startContent, $endContent - $startContent);
                      $contentStatement = new Statement("CONTENT", $subContent, array());
                      $ifContent[] = $contentStatement;
                      $pos = $closePos + 9;
                    }
                    else {
                      //there is some other statement between the else and the endif
                      $innerContent = substr($content, $elsePos + 8);
                      $result = $this->parse($innerContent);
                      $endPos = $result[1] + $elsePos + 8;
                      if ($endPos != strpos($content, "{{ENDIF}}", $endPos)) {
                        UI::printFatalError("IF statement not closed correctly at $endPos!");
                      }
                      foreach ($result[0] as $stat) {
                        $ifContent[] = $stat;
                      }
                      $pos = $endPos + 9;
                    }
                  }
                }
                $setting[1] = $elsePosition;
                $ifStatement = new Statement("IF", $ifContent, $setting);
                $statements[] = $ifStatement;
              }
              else {
                //we have a simple if with some inner statements
                $innerContent = substr($content, $endCondition + 2);
                $result = $this->parse($innerContent);
                $endPos = $result[1] + $endCondition + 2;
                if ($endPos != strpos($content, "{{ENDIF}}", $endPos - 4)) {
                  UI::printFatalError("IF statement not closed correctly at $endPos!");
                }
                $ifStatement = new Statement("IF", $result[0], $setting);
                $statements[] = $ifStatement;
                $pos = $endPos + 9;
              }
            }
            break;
          case 'FOR':
            $nextPos = strpos($content, "{{", $loopPos + 2);
            $closePos = strpos($content, "{{ENDFOR}}", $loopPos + 2);
            if ($closePos === false) {
              UI::printFatalError("Syntax error: FOR statement at $loopPos not closed!");
            }
            $startCondition = $loopPos + 6;
            $endCondition = strpos($content, "}}", $startCondition);
            $setting = explode(";", substr($content, $startCondition, $endCondition - $startCondition));
            if (sizeof($setting) != 3) {
              UI::printFatalError("Invalid condition size on FOR on $loopPos");
            }
            if ($nextPos == $closePos) {
              //we have a simple for statement
              $startContent = $endCondition + 2;
              $endContent = $closePos;
              $subContent = substr($content, $startContent, $endContent - $startContent);
              $contentStatement = new Statement("CONTENT", $subContent, array());
              
              //create for statement
              $forStatement = new Statement("FOR", array($contentStatement), $setting);
              $statements[] = $forStatement;
              $pos = $closePos + 10;
            }
            else {
              //the for statement has some inner statements
              $innerContent = substr($content, $endCondition + 2);
              $result = $this->parse($innerContent);
              $endPos = $result[1] + $endCondition + 2;
              if ($endPos != strpos($content, "{{ENDFOR}}", $endPos)) {
                UI::printFatalError("FOR statement not closed correctly at $endPos!");
              }
              $forStatement = new Statement("FOR", $result[0], $setting);
              $statements[] = $forStatement;
              $pos = $endPos + 10;
            }
            break;
          case 'FOREACH':
            $nextPos = strpos($content, "{{", $loopPos + 2);
            $closePos = strpos($content, "{{ENDFOREACH}}", $loopPos + 2);
            if ($closePos === false) {
              UI::printFatalError("Syntax error: FOREACH statement at $loopPos not closed!");
            }
            $startCondition = $loopPos + 10;
            $endCondition = strpos($content, "}}", $startCondition);
            $setting = explode(";", substr($content, $startCondition, $endCondition - $startCondition));
            if (sizeof($setting) != 3 && sizeof($setting) != 2) {
              UI::printFatalError("Invalid condition size on FOREACH on $loopPos");
            }
            if ($nextPos == $closePos) {
              //we have a simple foreach statement
              $startContent = $endCondition + 2;
              $endContent = $closePos;
              $subContent = substr($content, $startContent, $endContent - $startContent);
              $contentStatement = new Statement("CONTENT", $subContent, array());
              
              //create foreach statement
              $foreachStatement = new Statement("FOREACH", array($contentStatement), $setting);
              $statements[] = $foreachStatement;
              $pos = $closePos + 14;
            }
            else {
              //the foreach statement has some inner statements
              $innerContent = substr($content, $endCondition + 2);
              $result = $this->parse($innerContent);
              $endPos = $result[1] + $endCondition + 2;
              if ($endPos != strpos($content, "{{ENDFOREACH}}", $endPos)) {
                UI::printFatalError("FOREACH statement not closed correctly at $endPos!");
              }
              $foreachStatement = new Statement("FOREACH", $result[0], $setting);
              $statements[] = $foreachStatement;
              $pos = $endPos + 14;
            }
            break;
          default:
            UI::printFatalError("Unknown loop type: $loopType");
            break;
        }
      }
      else {
        $subContent = substr($content, $pos);
        $contentStatement = new Statement("CONTENT", $subContent, array());
        $statements[] = $contentStatement;
        $pos += strlen($subContent);
      }
    }
    return array($statements, strlen($content));
  }
  
  private function resolveDependencies() {
    //include all templates
    preg_match_all('/\{\%(.*?)\%\}/mis', $this->content, $matches, PREG_PATTERN_ORDER);
    
    for ($x = 0; $x < sizeof($matches[0]); $x++) {
      $command = explode("->", $matches[1][$x]); //just the command
      $replace = $matches[0][$x]; //whole part which will be replaced
      
      if (sizeof($command) != 2) {
        return false;
      }
      switch ($command[0]) {
        case "TEMPLATE":
          $tmp = new Template($command[1]);
          if ($tmp === false) {
            return false;
          }
          $tmp->resolveDependencies();
          $render = $tmp->getContent();
          if ($render === false) {
            return false;
          }
          $this->content = str_replace($replace, $render, $this->content);
          break;
        default:
          return false;
          break;
      }
    }
    return true;
  }
}