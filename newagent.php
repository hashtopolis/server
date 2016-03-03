<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("agents.new");
$MENU->setActive("agents_new");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

// manage registration vouchers
echo "Provide agent with valid voucher and this link:<br>";
echo "<a href=\"server.php?a=update\">Download agent</a><br><br>";
if (isset($_POST["newvoucher"])) {
	mysqli_query_wrapper($dblink,"INSERT INTO regvouchers (voucher,time) VALUES ('".mysqli_real_escape_string($dblink,$_POST["newvoucher"])."',$cas)");
}
$kver=mysqli_query_wrapper($dblink,"SELECT voucher,time FROM regvouchers");
if (mysqli_num_rows($kver)>0) {
	echo "Existing vouchers:<br>";
	echo "<table class=\"styled\"><tr><td>Voucher</td><td>Issued</td><td>Action</td></tr>";
	while ($erej=mysqli_fetch_array($kver,MYSQLI_ASSOC)) {
		$id=$erej["voucher"];
		echo "<tr><td>$id</td>";
		echo "<td>".date($config["timefmt"],$erej["time"])."</td>";
				echo "<td><form action=\"$myself?a=voucherdelete\" method=\"POST\" onSubmit=\"if (!confirm('Really delete this voucher?')) return false;\">";
				echo "<input type=\"hidden\" name=\"return\" value=\"a=deploy\">";
          echo "<input type=\"hidden\" name=\"voucher\" value=\"$id\">";
          echo "<input type=\"submit\" value=\"Delete\"></form></td></tr>";
        }
        echo "</table>Used vouchers are automaticaly deleted to prevent double spending.<br><br>";
      }
      echo "<form action=\"$myself?a=deploy\" method=\"POST\">";
      echo "<table class=\"styled\"><tr><td>New voucher</td></tr>";
      echo "<tr><td><input type=\"text\" name=\"newvoucher\" value=\"".generate_random(8)."\"></td></tr>";
      echo "<tr><td><input type=\"submit\" value=\"Create\"></td></tr>";
      echo "</table></form>";
		break;

$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




