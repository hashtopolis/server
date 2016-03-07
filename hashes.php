<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("hashes");
$MENU->setActive("hashes");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

// show hashes based on provided criteria
$hlist=intval($_GET["hashlist"]);
$chunk=intval($_GET["chunk"]);
$task=intval($_GET["task"]);
echo "<form action=\"$myself\" method=\"GET\">";
echo "<input type=\"hidden\" name=\"a\" value=\"hashes\">";
      if ($chunk>0) {
      $fmt=mysqli_query_wrapper($dblink,"SELECT hashlists.id,hashlists.format FROM chunks JOIN tasks ON chunks.task=tasks.id JOIN hashlists ON hashlists.id=tasks.hashlist WHERE chunks.id=$chunk");
        $er=mysqli_fetch_array($fmt,MYSQLI_ASSOC);
        $hlist=$er["id"];
        $format=$er["format"];
        $src="chunk";
} else {
		if ($task>0) {
			$fmt=mysqli_query_wrapper($dblink,"SELECT hashlists.id,tasks.name,hashlists.format FROM tasks JOIN hashlists ON hashlists.id=tasks.hashlist WHERE tasks.id=$task");
					$er=mysqli_fetch_array($fmt,MYSQLI_ASSOC);
					$hlist=$er["id"];
					$format=$er["format"];
					$src="task";
			} else {
					if ($hlist>0) {
							$fmt=mysqli_query_wrapper($dblink,"SELECT name,format FROM hashlists WHERE id=$hlist");
							$er=mysqli_fetch_array($fmt,MYSQLI_ASSOC);
							$format=$er["format"];
							$src="hashlist";
							}
							}
							}

							// create proper superhashlist field if needed
							list($superhash,$hlisty)=superList($hlist,$format);

							switch ($src) {
							case "chunk":
							echo "<input type=\"hidden\" name=\"chunk\" value=\"$chunk\">";
							echo "Hashes of chunk $chunk, filter: ";
							$viewfilter="WHERE chunk=$chunk";
							break;

							case "task":
							echo "<input type=\"hidden\" name=\"task\" value=\"$task\">";
							echo "Hashes of task <a href=\"$myself?a=taskdetail&task=$task\">".$er["name"]."</a>, filter: ";
							$viewfilter="JOIN chunks ON chunk=chunks.id WHERE ".$formattables[$format].".chunk IS NOT NULL AND chunks.task=$task";
          break;

        case "hashlist":
        echo "<input type=\"hidden\" name=\"hashlist\" value=\"$hlist\">";
							echo "Hashes of hashlist <a href=\"$myself?a=hashlistdetail&hashlist=$hlist\">".$er["name"]."</a>, filter: ";
							$viewfilter="WHERE hashlist IN ($hlisty)";
							break;
							}
							$what=$_GET["display"];
      $displays=array("hash"=>"Hashes only",""=>"Hashes + plaintexts","plain"=>"Plaintexts only");
      $filters=array("cracked"=>"Cracked","uncracked"=>"Uncracked",""=>"All");

      echo "<select name=\"display\">";
							foreach ($displays as $disid=>$distext) {
									echo "<option value=\"$disid\"";
        if ($disid==$what) echo " selected";
        echo ">$distext</option>";
							}

							echo "</select>";
							if ($src=="hashlist") {
							$filt=$_GET["filter"];
							echo "<select name=\"filter\">";
							foreach ($filters as $filid=>$filtext) {
							echo "<option value=\"$filid\"";
          if ($filid==$filt) echo " selected";
          echo ">$filtext</option>";
							}
							echo "</select>";
							}
							$filter=array("cracked"=>" AND plaintext IS NOT NULL","uncracked"=>" AND plaintext IS NULL");
									$kve="SELECT ";
									switch ($format) {
        case 0:
											// get regular hashes
          $kve.="hashes.hash,hashes.salt,hashes.plaintext";
          	break;

          	case 1:
          	// get access points and their passwords
          $kve.="hashes_binary.essid AS hash,hashes_binary.plaintext";
          break;

          case 2:
          // get binary - only passwords
          $kve.="'' AS hash,hashes_binary.plaintext";
          break;
          }
          $kve.=" FROM ".$formattables[$format]." ".$viewfilter.$filter[$filt];
      $kver=mysqli_query_wrapper($dblink,$kve);
      echo "<input type=\"submit\" value=\"OK\"></form> Matching hashes: ".mysqli_num_rows($kver);
      echo "<br>";
          echo "<textarea cols=\"100\" rows=\"30\" readonly>";
      while($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
      $out="";
        $hash=$erej["hash"];
        $salt=$erej["salt"];
        $plain=$erej["plaintext"];

        switch ($what) {
        case "hash":
        $out.=$hash;
        if ($salt!="") $out.=$config["fieldseparator"]."$salt";
        break;

        	case "":
        	$out.=$hash;
        	if ($salt!="") $out.=$config["fieldseparator"]."$salt";
        	$out.=$config["fieldseparator"];
        	case "plain":
        	if ($plain!="") $out.=$plain;
        	break;
        }
        			if ($out!="") echo "$out\n";
        }
      echo "</textarea>";
      		echo "<br>HEX convertor: <script>function hex2a(hex) { var str = ''; for (var i = 0; i < hex.length; i += 2) str += String.fromCharCode(parseInt(hex.substr(i, 2), 16)); return str; }</script>";
      echo "\$HEX[<input type=\"text\" id=\"conv\">] -> <input type=\"text\" id=\"convr\" readonly> <button onclick=\"javascript:document.getElementById('convr').value=hex2a(document.getElementById('conv').value);\">Convert</button>";

      break;

$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




