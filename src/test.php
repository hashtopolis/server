<?php
ini_set("display_errors", "1");

/*
 * This file just consists of some examples to test the templating engine
 */

require_once(dirname(__FILE__) . "/inc/templating/Statement.class.php");
require_once(dirname(__FILE__) . "/inc/templating/Template.class.php");
require_once(dirname(__FILE__) . "/inc/UI.class.php");

class Test {
  public function getString(){
    return "This is test!";
  }
  
  public static function getTest(){
    return "This is static!";
  }
}

$text1 = "hello [[substr([[user]], 0, 2)]], how are you [[do[1] ]]? [[test.getString()]] [[Test::getTest()]]{{IF [[iftest]] < 1}}{{IF false}}blub{{ENDIF}}{{ENDIF}}";
$text2 = "{{IF [[iftest]] < 1}}{{IF false}}blub{{ENDIF}}{{ENDIF}}{{FOREACH x;[[values]]}}current: [[x]]{{ENDFOREACH}}";
$text3 = "hello {{IF [[test]] > 1}}ich {{IF [[test]] > 2}}ich{{ENDIF}}{{ELSE}}outerelse{{ENDIF}} bin!";
$text4 = "hello {{IF [[test]]}}1{{ELSE}}2{{ENDIF}} du {{IF [[test]]}}1{{ELSE}}2{{ENDIF}}!";
$text5 = "hello {{FOREACH var;[[var]];cnt}}[[var]]{{ENDFOREACH}} blabla";
$text6 = "hello {{FOREACH var;[[var]];cnt}}[[var]]{{FOREACH var2;[[var2]]}}[[var1]][[var2]]{{ENDFOREACH}}{{ENDFOREACH}} blabla";
$text7 = "hello {{FOR var;[[var1]];[[var2]]}}[[var]]{{ENDFOR}} blabla";
$text8 = "hello {{FOR var;[[var1]];[[var2]]}}[[var]]{{FOR varr;[[varr1]];[[varr2]]}}[[var]][[varr]]{{ENDFOR}}{{ENDFOR}} blabla";
$objects = array('user' => "s3in!c", "do" => array("blug", "doing"), 'test' => new Test(), "iftest" => 4, "values" => array("val1", "val2", "val3"));

$text10 = "hello [[user]], how are you? {{IF [[fine]] == 1}}I'm fine!{{ELSE}}I'm not fine :({{ENDIF}}";
$text11 = "hello {{FOR x;[[start]];[[end]]}}user[[x]]\n{{ENDFOR}}";
$text12 = "hello {{IF [[var]] == 1}} user1 {{IF [[varr]] == 2}} varr is equal 2{{ENDIF}}{{ENDIF}}";
$objects10 = array('user' => "s3in!c", "fine" => 1, "start" => 5, "end" => 10, "var" => 0, "varr" => 2);


$TEMPLATE = new Template($text1, true);
echo $TEMPLATE->render($objects);