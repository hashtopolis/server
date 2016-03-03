<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("hashlists");
$MENU->setActive("lists_norm");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'export':
			// export cracked hashes to a file
			$hlist = intval($_POST["hashlist"]);
			$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT format FROM hashlists WHERE id=$hlist");
			$list = $res->fetch();
			if($list){
				$format = $list['format'];
				// create proper superhashlist field if needed
				list($superhash, $hlisty) = Util::superList($hlist, $format);
			
				$tmpfile = "Pre-cracked_".$hlist."_".date("Y-m-d_H-i-s").".txt";
				$tmpfull = "files/".$tmpfile;
				$salted = false;
				$kvery1 = "SELECT ";
				switch ($format) {
					case 0:
						$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT 1 FROM hashes WHERE hashlist IN ($hlisty) AND salt!='' LIMIT 1");
						if($res->rowCount() > 0){
							$kvery1 .= "hash,salt,plaintext";
							$salted = true;
						} 
						else{
							$kvery1 .= "hash,plaintext";
						}
						break;
					case 1:
						$kvery1 .= "essid AS hash,plaintext";
						break;
					case 2:
						$kvery1 .= "plaintext";
						break;
				}
				$kvery2 = " INTO OUTFILE '$tmpfull' FIELDS TERMINATED BY ".$FACTORIES::getagentsFactory()->getDB()->quote($CONFIG->getVal("fieldseparator"))." ESCAPED BY '' LINES TERMINATED BY '\\n'";
				$kvery3 = " FROM ".Util::getStaticArray($format, 'formattables')." WHERE hashlist IN ($hlisty) AND plaintext IS NOT NULL";
				if (!file_exists("files")){
					mkdir("files");
				}
				$kvery = $kvery1.$kvery2.$kvery3;
				$res = $FACTORIES::getagentsFactory()->getDB()->exec($kvery);
				$message = "<div class='alert alert-neutral'>";
				if(!$res){
					$message .= "File export failed, trying SELECT with file output<br>";
					$kvery = $kvery1.$kvery3;
					$res = $FACTORIES::getagentsFactory()->getDB()->query($kvery);
					$res = $res->fetchAll();
					$fexp = fopen("files/".$tmpfile, "w");
					foreach($res as $entry){
						fwrite($fexp, $entry["hash"].($salted ? $CONFIG->getVal("fieldseparator").$entry["salt"] : "").$CONFIG->getVal("fieldseparator").$entry["plaintext"]."\n");
					}
					fclose($fexp);
				}
				if($res) {
					if (insertFile("files/".$tmpfile)) {
						$message .= "Cracked hashes from hashlist $hlist exported.</div>";
					} 
					else {
						$message .= "Cracked hashes exported, but the file is missing.</div>";
          			}
        		} 
        		else {
					$message .= "Could not export hashlist $hlist</div>";
				}
			}
			else {
				$message = "<div class='alert alert-danger'>No such hashlist.</div>";
			}
			break;
		case 'hashlistzap':
			$hlist = intval($_POST["hashlist"]);
			$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT hashlists.*,IFNULL(hashes.salted,0) AS salted FROM hashlists LEFT JOIN (SELECT hashlist,1 AS salted FROM hashes WHERE hashlist=$hlist AND salt!='' LIMIT 1) hashes ON hashlists.format=0 AND hashes.hashlist=hashlists.id WHERE hashlists.id=$hlist");
			$list = $res->fetch();
			if($list){
				$listSet = new DataSet();
				$listSet->setValues($list);
				$OBJECTS['list'] = $listSet;
				$OBJECTS['zap'] = true;
				$impfiles = array();
        		if(file_exists("import") && is_dir("import")){
			        $impdir = opendir("import");
			        $impfiles = array();
			        while($f=readdir($impdir)){
			        	if (($f!=".") && ($f!="..") && (!is_dir($f))) {
			        		$impfiles[] = $f;
			        	}
			        }
			        $OBJECTS['impfiles'] = $impfiles;
        		}
			}
			else{
				$message = "<div class='alert alert-danger'>Invalid hashlist!</div>";
			}
			break;
		case 'hashlistdelete':
			// delete hashlist
			$message = "<div class='alert alert-neutral'>";
			$hlist = intval($_POST["hashlist"]);
			$FACTORIES::getagentsFactory()->getDB()->exec("START TRANSACTION");
			$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT hashlists.format,hashlists.hashcount FROM hashlists WHERE hashlists.id=$hlist");
			$list = $res->fetch();
			$hcount = $list["hashcount"];
			
			// decrease supercount by that of deleted hashlist
			$ans0 = $FACTORIES::getagentsFactory()->getDB()->exec("UPDATE hashlists JOIN superhashlists ON superhashlists.id=hashlists.id AND hashlists.format=3 AND superhashlists.hashlist=$hlist JOIN hashlists hashlists2 ON hashlists2.id=superhashlists.hashlist SET hashlists.cracked=hashlists.cracked-hashlists2.cracked,hashlists.hashcount=hashlists.hashcount-hashlists2.hashcount");
			
			// then actually delete the list
			$ans1 = $ans0 && $FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM hashlists WHERE id=$hlist");
			$ans2 = $ans1 && $FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM hashlistusers WHERE hashlist=$hlist");
			$ans3 = $ans2 && $FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM zapqueue WHERE hashlist=$hlist");
			
			// and its tasks
			$ans4 = $ans3 && $FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM taskfiles WHERE task IN (SELECT id FROM tasks WHERE hashlist=$hlist)");
			$ans5 = $ans4 && $FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM assignments WHERE task IN (SELECT id FROM tasks WHERE hashlist=$hlist)");
			$ans6 = $ans5 && $FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM chunks WHERE task IN (SELECT id FROM tasks WHERE hashlist=$hlist)");
			$ans7 = $ans6 && $FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM tasks WHERE hashlist=$hlist");
			
			$ans8 = $ans7 && $FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM superhashlists WHERE hashlist=$hlist");
			
			if($ans8){
				$FACTORIES::getagentsFactory()->getDB()->exec("COMMIT");
				$message .= "Deleted hashlist and associated zaps.<br>";
				switch($list["format"]) {
					case 0:
						$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT 1 FROM hashlists WHERE format=0");
						if($res->rowCount() > 0){
							$message .= "Deleting the actual rows (this is going to take A LONG TIME!)...<br>";
							$hdelete = 0;
							$kolik = 1;
							$cas_pinfo = time();
							$cas_start = time();
							$FACTORIES::getagentsFactory()->getDB()->exec("START TRANSACTION");
							while($kolik > 0) {
								$kver = "DELETE FROM ".$formattables[$list["format"]]." WHERE hashlist=$hlist LIMIT 1000";
								$ans1 = $FACTORIES::getagentsFactory()->getDB()->exec($kver);
								$kolik = $ans1->rowCount();
								$hdelete += $kolik;
								if(time() >= $cas_pinfo + 10){
									$message .= "Progress: $hdelete/$hcount, time spent: ".(time()-$cas_start)." sec<br>";
									$FACTORIES::getagentsFactory()->getDB()->exec("COMMIT");
									$FACTORIES::getagentsFactory()->getDB()->exec("START TRANSACTION");
									$cas_pinfo = time();
								}
							}
							$FACTORIES::getagentsFactory()->getDB()->exec("COMMIT");
						} 
						else {
							$message .= "This was the last hashlist, truncating the table.";
							$FACTORIES::getagentsFactory()->getDB()->exec("TRUNCATE TABLE ".$formattables[$list["format"]]);
           	 			}
           	 			header("Location: hashlists.php");
           	 			die();
						break;
			
					case 1:
					case 2:
            			$message .= "Deleting binary hashes...<br>";
						$FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM hashes_binary WHERE hashlist=$hlist");
						header("Location: hashlists.php");
						die();
						break;
         	 		case 3:
						$message .= "Deleting superhashlist links...<br>";
						$FACTORIES::getagentsFactory()->getDB()->exec("DELETE FROM superhashlists WHERE id=$hlist");
						header("Location: hashlists.php");
						die();
						break;
				}
			} 
			else {
        		$FACTORIES::getagentsFactory()->getDB()->exec("ROLLBACK");
			    $message .= "Problems deleting hashlist!";
			}
			$message .= "</div>";
			break;
	}
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT hashlists.id,hashlists.name,hashlists.hashtype,hashlists.format,hashlists.hashcount,hashlists.cracked,hashlists.secret,hashtypes.description FROM hashlists LEFT JOIN hashtypes ON hashtypes.id=hashlists.hashtype WHERE format!=3 ORDER BY id ASC");
$res = $res->fetchAll();
$hashlists = array();
foreach($res as $list){
	$set = new DataSet();
	$set->setValues($list);
	$hashlists[] = $set;
}

$OBJECTS['hashlists'] = $hashlists;
$OBJECTS['numHashlists'] = sizeof($hashlists);
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




