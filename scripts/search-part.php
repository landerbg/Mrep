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
$partname = mb_strtoupper($partname, "utf-8");
$key = $_GET['key']; 


$returndata = search_part($Databasecon, $partname, $key);
echo $returndata;





