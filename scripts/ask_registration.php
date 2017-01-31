<?php

$user = $_POST['user']; 
$hash = $_POST['hash'];
$dbp = $_POST['dbp'];

$myFile = "reg/".$user.".txt";
$fh = fopen($myFile, 'a');
$log = $_SERVER['REMOTE_ADDR']." , ".date("d.m.Y H:i:s").", Хеш : ".$hash ."\r\n";
fwrite($fh, $log);
fclose($fh);
echo $myFile;


?>