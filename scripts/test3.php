<?php
session_start();
if(!isset($_SESSION['conn_string']))
die('Не сте логнати в системата !
		<a href="#login" data-rel="dialog" data-transition="flip" data-role="button">Влез</a>'
		
		);
$conn_string = $_SESSION['conn_string'];
$user = $_SESSION['user'];
$pass = $_SESSION['pass'];
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 'ok')
    {
   
        die('Не сте логнати в системата !
		<a href="#login" data-rel="dialog" data-transition="flip" data-role="button">Влез</a>'

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
                                                                        die("<script>alert('Няма връзка с вашата база данни. Опитайте по-късно! ')</script>");
									}




$inobekti =  get_procedures_params($Databasecon);

   
test($Databasecon);
?>