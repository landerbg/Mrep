<?php
session_start();
$conn_string = $_SESSION['conn_string'];
$user = $_SESSION['user'];
$pass = $_SESSION['pass'];
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 'ok')
    {
        die('Не сте логнати в системата !
		<a href="#login"   data-rel="dialog" data-transition="flip" data-role="button">Влез</a>'
		
		);
    }
require_once('functions.php');
try {
			$Databasecon = new PDO($conn_string, $user, $pass);
			$Databasecon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
catch (PDOException $e)
									{
									echo $e->getMessage();
									}	
$partname = $_GET['partname'];
$key = $_GET['key'];
$partname = mb_strtoupper($partname, "utf-8");
if ($key == "barcode")
{
$returndata = search_barcode($Databasecon, $partname); 
echo $returndata;
exit();
}
if (strlen($partname) > 0) {//по някаква причина три символа ги брои за 6.може би заради ъпър

    $test = explode(' ', $partname);
    if (isset($test[1])) {
        $is_full = 1;
    } else {
        $is_full = 0;
    }
	//echo "ISFULL : ".$is_full;
//дали да търси само по част от името.Ako e 1 = pylno tyrsene по масива тест
    $returndata = search_art($Databasecon, $test, $key, $is_full);
    echo $returndata;
} else {
    echo "<p style='color : red;'>Няма намерени съвпадения</p>";
}
