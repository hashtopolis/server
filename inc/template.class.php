<?php
/*
* This file is part of Bricky (https://github.com/s3inlc/bricky)
* Copyright 2016 by Sein Coray
*/

namespace Bricky;

/**
 * This class does some magic voodoo shit to generate the real page out of the template :)
 * This file is exported out of a project and some functions are not neccessary for Bricky 
 * and/or are not working outside of this specific project.
 *
 * @author Sein
 */
class Template {
	private $template;

	/**
	 * Load template with given name from the template directory
	 * @param string $name name without ending .template.html
	 */
	public function __construct($name, $directInput = false){
		if($directInput){
			$this->template = $name;
			return;
		}
		$path = dirname(__FILE__)."/../templates/".$name.".template.html";
		if(!file_exists($path)){
			$path = dirname(__FILE__)."/../templates/".$name;
			if(!file_exists($path)){
				if(ini_get("display_errors") == 1){
					echo "ERROR: Template $name not found!\n";
				}
				return false;
			}
		}
		$this->template = file_get_contents($path);
	}

	/**
	 * Just loads the code which is received when all templates are put together
	 * without any replacement of objects or language strings.
	 * 
	 * @return mixed false on failure, otherwise the code will be returned as string
	 */
	public function getCode(){
		//include all templates
		preg_match_all("/\{\%(.*?)\%\}/mis", $this->template, $matches, PREG_PATTERN_ORDER);

		for($x=0;$x<sizeof($matches[0]);$x++){
			$command = explode("->", $matches[1][$x]); //just the command
			$replace = $matches[0][$x]; //whole part which will be replaced

			if(sizeof($command) != 2){
				return false;
			}
			switch($command[0]){
				case "TEMPLATE":
					$tmp = new Template($command[1]);
					if($tmp === false){
						return false;
					}
					$render = $tmp->getCode();
					if($render === false){
						return false;
					}
					$this->template = str_replace($replace, $render, $this->template);
					break;
				default:
					return false;
					break;
			}
		}
		return $this->template;
	}

	/**
	 * This function is used to parse javascript templates to replace any language strings
	 * present in javascript code to also add multilingual support to the interactive scripts.
	 * 
	 * @param array $objects objects required to render the template
	 * @return string rendered javascript code
	 */
	public function renderJS($objects){
		$render = $this->render($objects);

		//delete one-line comments
		preg_match_all("/\/\/(.*?)\\n/mis", $this->template, $matches, PREG_PATTERN_ORDER);
		for($x=0;$x<sizeof($matches[0]);$x++){
			$command = explode("->", $matches[1][$x]); //just the command
			$replace = $matches[0][$x]; //whole part which will be replaced
			$render = str_replace($replace, "", $render);
		}

		//delete multiline comments
		preg_match_all("/\/\*(.*?)\*\//mis", $this->template, $matches, PREG_PATTERN_ORDER);
		for($x=0;$x<sizeof($matches[0]);$x++){
			$command = explode("->", $matches[1][$x]); //just the command
			$replace = $matches[0][$x]; //whole part which will be replaced
			$render = str_replace($replace, "", $render);
		}

		//minify
		$render = str_replace("\r\n", "", $render);
		$render = str_replace("  ", "", $render);
		$render = str_replace("\n", "", $render);
		$render = str_replace("\t", "", $render);

		return $render;
	}

	/**
	 * Render the current template with all objects and language parts.
	 * 
	 * @param array $objects objects and variables which are needed to generate all templates and sub-templates
	 * @return string rendered html code
	 */
	public function render($objects){
		$render = $this->renderExecute($this->template, $objects);

		//remove all HTML comments
		$render = preg_replace("/\<\!\-\- (.*?) \-\-\>\\n/", "", $render);

		//search for to be special treated variables
		preg_match_all("/^(?:(?!\{\{).)*?(\?\?\?(.*?)\?\?\?)/is", $render, $matches, PREG_PATTERN_ORDER);
		while(sizeof($matches[0]) > 0){
			$matches[2][0] = "!!".$matches[2][0]."!!";
			$val = $this->getCondition($matches[2][0], $objects);
			$render = $this->str_replace_first($matches[1][0], str_replace("\n", "---NEWLINE---", eval("return ".$val.";")), $render);
			preg_match_all("/^(?:(?!\{\{).)*?(\?\?\?(.*?)\?\?\?)/is", $render, $matches, PREG_PATTERN_ORDER);
		}
		
		//go through loops recursively and at the end replace all language strings
		return str_replace("---NEWLINE---", "\n", $this->renderLanguage($render));
	}
	
	/**
	 * Similar to str_replace, except that it only replaces the first occurence.
	 *
	 * @param string $search part to replace
	 * @param string $replace part which is used to replace
	 * @param string $subject where replacement should be done
	 * @return string with the replaced string
	 */
	private function str_replace_first($search, $replace, $subject) {
		return implode($replace, explode($search, $subject, 2));
	}

	/**
	 * Checks the given code for all language definitions and replaces them with the corresponding string.
	 * 
	 * @param string $text text to check for language definitions
	 * @return string rendered text code
	 */
	private function renderLanguage($text){
		global $LANGUAGE;
		
		if(!isset($LANGUAGE)){
			return $text;
		}

		//search for all language strings
		preg_match_all("/\_\_(.*?)\_\_/mis", $text, $matches, PREG_PATTERN_ORDER);

		for($x=0;$x<sizeof($matches[0]);$x++){
			$replace = $matches[0][$x]; //whole part which will be replaced
			$text = str_replace($replace, $LANGUAGE->getText($matches[1][$x]), $text);
		}
		return $text;
	}

	/**
	 * Searches for variable formatting in template code and replaces the corresponding code with the result
	 * of the formatting code.
	 * 
	 * @param string $partial code to check
	 * @param array $objects objects and variables which are required to render
	 * @param bool $inner is set to true if it is inside a condition or a function call
	 * @return string rendered code
	 */
	private function varReplace($partial, $objects, $inner = false){
		//search for outer variables
		preg_match_all("/^(?:(?!\{\{).)*?(\[\[(.*?)\]\])/is", $partial, $matches, PREG_PATTERN_ORDER);
		while(sizeof($matches[0]) > 0){
			$matches[2][0] = "!!".$matches[2][0]."!!";
			$val = $this->getCondition($matches[2][0], $objects);
			if($inner){
				$partial = $this->str_replace_first($matches[1][0], str_replace("\n", "---NEWLINE---", $val), $partial);
			}
			else{
				$partial = $this->str_replace_first($matches[1][0], str_replace("\n", "---NEWLINE---", eval("return ".$val.";")), $partial);
			}
			preg_match_all("/^(?:(?!\{\{).)*?(\[\[(.*?)\]\])/is", $partial, $matches, PREG_PATTERN_ORDER);
		}
		
		//search for inner variables
		preg_match_all("/^(?:(?!\{\{).)*?(\%\%(.*?)\%\%)/is", $partial, $matches, PREG_PATTERN_ORDER);
		while(sizeof($matches[0]) > 0){
			$matches[2][0] = "!!".$matches[2][0]."!!";
			$val = $this->getCondition($matches[2][0], $objects);
			if($inner){
				$partial = $this->str_replace_first($matches[1][0], str_replace("\n", "---NEWLINE---", $val), $partial);
			}
			else{
				$partial = $this->str_replace_first($matches[1][0], str_replace("\n", "---NEWLINE---", eval("return ".$val.";")), $partial);
			}
			preg_match_all("/^(?:(?!\{\{).)*?(\%\%(.*?)\%\%)/is", $partial, $matches, PREG_PATTERN_ORDER);
		}
		
		return $partial;
	}

	/**
	 * Searches for the loops and if statements inside the code and executes the actions to be done on them.
	 * 
	 * @param string $partial code part to render
	 * @param array $objects objects and variables required to render
	 * @return mixed false on failure, otherwise the rendered code is returned
	 */
	private function renderExecute($partial, $objects){
		$partial = $this->varReplace($partial, $objects);

		//search for if, for and foreach loops
		preg_match_all("/\{\{(.*?)\s(.*?)\}\}(.*?)\{\{(END\\1)\}\}/mis", $partial, $matches, PREG_PATTERN_ORDER);
		
		$rest = $partial;
		for($x=0;$x<sizeof($matches[0]);$x++){
			$rest = str_replace($matches[0][$x], "", $rest);
		}
		
		//if not a valid configuration is detected with lazy search, try it with greedy
		if(substr_count($rest, "{{ENDIF}}") != substr_count($rest, "{{IF ") || substr_count($rest, "{{ENDFOR}}") != substr_count($rest, "{{FOR ") || substr_count($rest, "{{ENDFOREACH}}") != substr_count($rest, "{{FOREACH ")){
			preg_match_all("/\{\{(.*?)\s(.*?)\}\}(.*)\{\{(END\\1)\}\}/mis", $partial, $matches, PREG_PATTERN_ORDER);
		}
		for($z=0;$z<sizeof($matches[0]);$z++){
			switch($matches[1][$z]){
				case "IF":
					if($matches[4][$z] != "ENDIF"){
						break; //block is not closed correctly
					}
					$condition = $this->getCondition($matches[2][$z], $objects);
					if(eval("return $condition;")){
						//statement is valid -> if block part will be included
						$partial = str_replace($matches[0][$z], $this->renderExecute($matches[3][$z], $objects), $partial);
					}
					else{
						//statement is false -> skip block
						$partial = str_replace($matches[0][$z], "", $partial);
					}
					break;
				case "FOR":
					if($matches[4][$z] != "ENDFOR"){
						break; //block is not closed correctly
					}
					$setting = explode(",", $this->getCondition($matches[2][$z], $objects));
					if(sizeof($setting) != 3){
						break; //invalid FOR configuration
					}
					$varname = $setting[0];
					$objects[$varname] = eval("return ".$setting[1].";");
					$content = "";
					for(;$objects[$varname] < eval("return ".$setting[2].";");$objects[$varname]++){
						$content .= $this->renderExecute($matches[3][$z], $objects);
					}
					$partial = str_replace($matches[0][$z], $content, $partial);
					break;
				case "FOREACH":
					if($matches[4][$z] != "ENDFOREACH"){
						return false; //block is not closed correctly
					}
					$setting = explode(",", $matches[2][$z]);
					if(sizeof($setting) != 2 && sizeof($setting) != 3){
						break; //invalid FOREACH configuration
					}
					$varname = $setting[0];
					$setting[1] = $this->getCondition($setting[1], $objects);
					$content = "";
					$countVar = false;
					if(isset($setting[2])){
						$count = 0;
						$countVar = true;
					}
					else if($setting[1] == "false"){
						$partial = str_replace($matches[0][$z], "", $partial);
						break;
					}
					foreach(eval("return ".$setting[1].";") as $entry){
						if($countVar){
							$objects[$setting[2]] = $count;
							$count++;
						}
						$objects[$varname] = $entry;
						//render the subpart of the foreach
						$content .= $this->renderExecute($matches[3][$z], $objects);
					}
					$partial = str_replace($matches[0][$z], $content, $partial);
					break;
				default:
					break;
			}
		}
		
		//include all templates
		preg_match_all("/\{\%(.*?)\%\}/mis", $partial, $matches, PREG_PATTERN_ORDER);
		
		for($x=0;$x<sizeof($matches[0]);$x++){
			$command = explode("->", $matches[1][$x]); //just the command
			$replace = $matches[0][$x]; //whole part which will be replaced
		
			if(sizeof($command) != 2){
				return false;
			}
			switch($command[0]){
				case "TEMPLATE":
					$tmp = new Template($command[1]);
					if($tmp === false){
						return false;
					}
					$render = $tmp->render($objects);
					if($render === false){
						return false;
					}
					$partial = str_replace($replace, $render, $partial);
					break;
				default:
					return false;
					break;
			}
		}
		
		return $this->varReplace($partial, $objects); //varReplace is required again for variables after all loops
	}

	/**
	 * Generates the condition to check on a if or loop condition which then can be checked with eval.
	 * 
	 * @param string $condition code from the condition
	 * @param array $objects objects and variable required to render
	 * @return string condition which can be checked with eval
	 */
	private function getCondition($condition, $objects){
		$condition = $this->varReplace($condition, $objects, true);

		//search for variable identifications inside of a expression used for calling objects
		//or accessing variables
		preg_match_all("/\!\!(.*?)\!\!/mis", $condition, $vals, PREG_PATTERN_ORDER);
		//$vals[0] contains complete match, $vals[1] contains inside match
		for($x=0;$x<sizeof($vals[0]);$x++){
			$complete = $vals[1][$x];
			$vals[1][$x] = explode(".", $vals[1][$x]);
			$varname = $vals[1][$x][0];
			unset($vals[1][$x][0]);
			$calls = implode("->", $vals[1][$x]);
			if(strlen($calls) > 0){
				$calls = "->$calls";
			}
			if(isset($objects[$varname])){
				//is a variable/object provided in objects
				$condition = str_replace($vals[0][$x], "\$objects['$varname']".$calls, $condition);
			}
			else if(is_callable(preg_replace("/\(.*\)/", "", $complete))){
				//is a static function call
				$condition = str_replace($vals[0][$x], $complete, $condition);
			}
			else{
				//not valid condition
				$condition = "false";
			}
		}
		return $condition;
	}
}
