<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

if(!$LOGIN->isLoggedin()){
	header("Location: index.php?err=4".time()."&fw=".urlencode($_SERVER['PHP_SELF']));
	die();
}
else if($LOGIN->getLevel() < 20){
	$TEMPLATE = new Template("restricted");
	die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("newhashlist");
$MENU->setActive("lists_new");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'newhashlistp':
			// new hashlist creator
			$name = $DB->quote(htmlentities($_POST["name"], false, "UTF-8"));
			$salted = (isset($_POST["salted"]) && intval($_POST["salted"]) == 1);
			$hexsalted = (isset($_POST["hexsalted"]) && $salted && intval($_POST["hexsalted"]) == 1);
			if($hexsalted){
				$hexsalted = 1;
			}
			else{
				$hexsalted = 0;
			}
			$fs = substr($DB->quote($_POST["separator"]), 1, -1);
			$format = $_POST["format"];
			$hashtype = intval($_POST["hashtype"]);
			$message = "<div class='alert alert-neutral'>";
			if ($format >= 0 && $format <= 2) {
				if (strlen($name) == 0) {
					$message .= "You must specify hashlist name";
				} 
				else {
					$message .= "Creating hashlist in the DB...";
					$res = $DB->exec("INSERT INTO hashlists (name, format,hashtype, hexsalt) VALUES ($name, $format, $hashtype, $hexsalted)");
					if($res){
						// insert succeeded
						$id = $DB->lastInsertId();
						$message .= "OK (id: $id)<br>";
						$source = $_POST["source"];
						switch ($source) {
							case "paste":
								$sourcedata = $_POST["hashfield"];
								break;
							case "upload":
								$sourcedata = $_FILES["hashfile"];
								break;
							case "import":
								$sourcedata = $_POST["importfile"];
								break;
							case "url":
								$sourcedata = $_POST["url"];
								break;
						}
						$tmpfile = "hashlist_$id";
						if (Util::uploadFile($tmpfile, $source, $sourcedata)) {
							$hsize = filesize($tmpfile);
							if ($hsize>0) {
								$message .= "Opening file $tmpfile ($hsize B)...";
								$hhandle = fopen($tmpfile,"rb");
								$message .= "OK<br>";
					
								$pocet = 0;
								$chyby = 0;
								$cas_start = time();
					
								switch ($format) {
								case 0:
									$message .= "Determining line separator...";
									// read buffer and get the pointer back to start
									$buf = fread($hhandle, 1024);
									$seps = array("\r\n", "\n", "\r");
									$ls = "";
									foreach ($seps as $sep) {
										if (strpos($buf, $sep) !== false) {
											$ls = $sep;
											$message .= Util::bintohex($ls);
											break;
										}
									}
									if ($ls == "") {
										$message .= "nothing (assuming one hash)";
									}
									$message .= "<br>";
									if ($salted) {
										// find out if the first line contains field separator
										$message .= "Searching for field separator inside the first line...";
										rewind($hhandle);
										$bufline = stream_get_line($hhandle, 1024, $ls);
										if (strpos($bufline,$fs) === false) {
											$message .= "NOTHING - assuming unsalted hashes";
											$fs = "";
										} 
										else {
											$message .= "OK - assuming salted hashes";
										}
										$message .= "<br>";
									} 
									else {
										$fs = "";
									}
									// now read the lines
									$message .= "Importing hashes from text file...<br>";
									rewind($hhandle);
			
									$tmpfull = $DB->quote(dirname(__FILE__)."/chunk_$id");
			
									// how many hashes to import at once:
									$loopsize = 100000;
			
									while(!feof($hhandle)) {
										$tmpchunk = fopen("chunk_$id", "w");
                      					$chunklines=0;
										while (!feof($hhandle) && $chunklines < $loopsize) {
											$dato = stream_get_line($hhandle, 1024, $ls);
											if ($dato==""){
												continue;
											}
											fwrite($tmpchunk,$dato.$ls);
											$chunklines++;
                      					}
										fclose($tmpchunk);
										$message .= "Loading $chunklines lines...";
										$cas_xstart = time();
                      					// try fast load data
										$kv = "LOAD DATA INFILE $tmpfull IGNORE INTO TABLE hashes ".($fs=="" ? "" : "FIELDS TERMINATED BY '$fs' ")."LINES TERMINATED BY ".$DB->quote($ls)." (hash, salt) SET hashlist=$id";
										$kvr = false;
										try{
											$kvr = $DB->query($kv);
										}
										catch(Exception $e){
											$kvr = false;
										}
										if ($kvr) {
											$message .= "OK";
											$pocet += $kvr->rowCount();
										} 
										else {
											// load data failed, could be bad privileges or mysql on different server than www
											$message .= "fail, inserting...";
											$slow = fopen("chunk_$id", "r");
											$DB->exec("START TRANSACTION");
											$buffer = array();
											$bufferCount = 0;
											while (!feof($slow)) {
												$dato = stream_get_line($slow, 1024, $ls);
												if ($fs == "") {
													$hash = $dato;
													$salt = "";
												} 
												else {
													$poz = strpos($dato,$fs);
													if ($poz !== false) {
														$hash = substr($dato,0,$poz);
														$salt = substr($dato,$poz+1);
													} 
													else {
														$hash = $dato;
														$salt = "";
													}
												}
												if(strlen($hash) == 0){
													continue; //this is a problem from files which contain empty lines
												}
												$hash = $DB->quote($hash);
												$salt = $DB->quote($salt);
												$buffer[] = "($id, $hash, $salt)";
												$bufferCount++;
												if($bufferCount >= 10000){
													$check = $DB->query("INSERT IGNORE INTO hashes (hashlist,hash,salt) VALUES ".implode(", ", $buffer));
													$pocet += $check->rowCount();
													$buffer = array();
													$bufferCount = 0;
												}
											}
											if(sizeof($buffer) > 0){
												$check = $DB->query("INSERT IGNORE INTO hashes (hashlist,hash,salt) VALUES ".implode(", ", $buffer));
												$pocet += $check->rowCount();
											}
											fclose($slow);
											$DB->exec("COMMIT");
										}
										$message .= " (took ".(time()-$cas_xstart)."s, total $pocet)<br>";
									}
									unlink("chunk_$id");
									break;
								case 1:
									$message .= "Importing wireless networks...<br>";
									while (!feof($hhandle)) {
										$dato = fread($hhandle, 392);
										if (strlen($dato) == 392) {
											$nazev = "";
											for ($i=0;$i<36;$i++) {
												$znak = $dato[$i];
												if ($znak != "\x00") {
													$nazev .= $znak;
												} 
												else {
													break;
												}
											}
											$message .= "Found network $nazev";
											$res = $DB->query("INSERT INTO hashes_binary (hashlist, essid, hash) VALUES ($id, '$nazev',x'".Util::bintohex($dato)."')");
											if ($res) {
												$pocet += $res->rowCount();
											} 
											else {
												$chyby++;
											}
										} 
										else {
											if (strlen($dato)>0){
												$message .= "Found garbage (only ".strlen($dato)." bytes)";
											}
										}
										$message .= "<br>";
									}
									break;
								case 2:
									if (!feof($hhandle)) {
										$dato = fread($hhandle, $hsize);
										$message .= "Inserting binary file as one hash...<br>";
										$res = $DB->query("INSERT INTO hashes_binary (hashlist, hash) VALUES ($id, x'".Util::bintohex($dato)."')");
										if ($res) {
											$message .= "OK";
											$pocet = $res->rowCount();
										} 
										else {
											$message .= "ERROR";
											$chyby++;
										}
										$message .= "<br>";
									}
								break;
								}
								fclose($hhandle);
								$message .= "<br>";
								$cas_stop = time();

								// evaluate, what have we accomplished
								if ($pocet>0) {
									$DB->exec("UPDATE hashlists SET hashcount=$pocet WHERE id=$id");
									$message .= "Insert completed ($pocet hashes inserted, $chyby errors, took ".($cas_stop-$cas_start)." sec)";
								} 
								else {
									$message .= "ERROR";
									$DB->exec("DELETE FROM hashlists WHERE id=$id");
									$message .= "Nothing was inserted ($chyby errors). Perhaps empty hashlist or database problem?";
								}
							} 
							else {
								$message .= "Hashlist file is empty!";
							}
							unlink($tmpfile);
						}
						/*header("Location: hashlists.php");
						die();*/
					} 
					else {
						$message .= "ERROR: ".$DB->errorInfo();
					}
					$message .= "<br>";
				}
			} 
			else {
				$message .= "Select correct hashlist format";
			}
			$message .= "</div>";
			break;
	}
}

$OBJECTS['impfiles'] = array();
if(file_exists("import") && is_dir("import")) {
	$impdir = opendir("import");
	$impfiles = array();
	while ($f=readdir($impdir)) {
		if (($f!=".") && ($f!="..") && (!is_dir($f))) {
			$impfiles[] = $f;
		}
	}
	$OBJECTS['impfiles'] = $impfiles;
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT id,description FROM hashtypes WHERE 1 ORDER BY id");
$res = $res->fetchAll();
$hashtypes = array();
foreach($res as $type){
	$hashtypes[] = new DataSet($type);
}

$OBJECTS['hashtypes'] = $hashtypes; 
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




