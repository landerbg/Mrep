<?php

session_start();
if(!isset($_SESSION['conn_string']))
die('Не сте логнати в системата !
		<a href="#login"   data-rel="dialog" data-transition="flip" data-role="button">Влез</a>'
		
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
//$ckladid="0";

//$number = 6700;
//get_smetki("01.04.2012 13:12:29", "07.04.2012 15:58:45" );
$inobekti = get_procedures_params($Databasecon);//TOVA SE PUSKA AKO VERSIATA E PO GOLIAMA OT 1947 DA DOBAVIA OBEKTITE KAM PROCEDURITE
if( isset($_POST['spravka2']) )
{
$spravka2 = $_POST['spravka2'];
}
if( isset($_POST['obektid']) )
{
$object = $_POST['obektid'];
}
if( isset($_POST['number']) )
{
    $number = $_POST['number'];
}
if( isset($_POST['ckladid']) )
{
     $ckladid = $_POST['ckladid'];
}

//$cklad =  $_POST['ckladid'];

//echo $number;
switch($spravka2)
		{
			case 0 :
			{
			print_smetka($Databasecon, $number, $object );
			break;
			}
			case 1 :
			{
			print_smetkared($Databasecon, $number, $object);
			break;
			}
			case 2 :
			{
			get_razcrdetails($Databasecon, $number);
			break;
			}
			case 3 :
			{
			get_doctsdr($Databasecon, $number, $object);
			break;
			}
			case 4 :
			{
			get_reviziasdr($Databasecon, $number, $object);
			break;
			}
			case 5 :
			{
			get_doctsdr($Databasecon, $number, $object);
			break;
			}
                        case 6 :
			{
                        
			get_outprodsdr($Databasecon, $number, $object);
			break;
			}
                        
                        case 51 :
			{
                        
			get_nalichnostpoobekti($Databasecon, $number, $object, $ckladid);
			break;
			}
			
                        case 52:
                            {
                        
			get_dvijenieartikul($Databasecon, $number, $object, $ckladid);
			break;
			}
                        case 53:
                        {
                        
			get_artikulinfo($Databasecon, $number, $object, $ckladid);
			break;
			} 
                        
                        case 55:
                        {
                        
			database_check($Databasecon);
			break;
			}
                        case 111:
                        
                        {
                         echo  (json_encode(get_objects($Databasecon)));  
                         //Връща json с обектите които са регистрирани в базата
                         break;
                            
                        }
                        case 112:
                        
                        {
                         check_dbtype($Databasecon);  
                         //Връща json с обектите които са регистрирани в базата
                         break;
                            
                        }
                        
			}




//$content = 'tralalala';
//echo $content;
//echo "tratata";


?>