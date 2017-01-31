<?php


session_start();

//ВАЛИДАЦИЯ НА  ВАЖНИТЕ ДАННИ ЗА ЗАРЕЖДАНЕ НА СПРАВКАТА
If(!empty($_POST['ot']) and !empty($_POST['do']) and !empty($_POST['fromrow']) and !empty($_POST['torow']) and !empty($_POST['spravka']))
{  
     $ot = $_POST['ot'];
     $do = $_POST['do'];
     $from = $_POST['fromrow'];
     $to = $_POST['torow'];
     $spravka = $_POST['spravka'];
}
else
    die("Липсват данни за зареждане на справка!");
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

If(!empty($_POST['objects']))
     $objects = $_POST['objects'];
else
     $objects = -1; //Somethings wrong.
If(!empty($_POST['hours']))
    $hours = $_POST['hours'];
else
     $hours = -1; //Somethings wrong.

If(!empty($_POST['klientid']))
     $klientid = $_POST['klientid'];
else
     $klientid = -1; //Somethings wrong.

If(!empty($_POST['check']))
     $check = $_POST['check'];
else
     $check = -1; //Somethings wrong.


//var_dump($_POST);
$myFile = "logfunc.txt";
$fh = fopen($myFile, 'a');
fwrite( $fh, $spravka.",".$conn_string.",".$_SERVER['REMOTE_ADDR'].",".date("d.m.Y H:i:s")."\r\n");
fclose($fh);
$inobekti =  get_procedures_params($Databasecon);

	switch($spravka)
                {       case -1:
                        {
                            echo "Не сте избрали справка";
                            break;
                        }
			case 1 :
			{
			get_smetki($Databasecon, $ot, $do, $from, $to, $hours,1,$check, $objects);
			
                        break;
			}
			case 2 :
			{
			get_oboroti($Databasecon, $ot, $do);
			break;
			}
			case 3 :
			{
			get_pechalba($Databasecon, $ot, $do, $from, $to, $hours,1,$check,$objects);
			break;
			}
			case 4:
			{
			get_storno($Databasecon, $ot, $do, $from, $to,  $hours,1,$check,$objects);
			break;
			}
			case 5:
			{
			get_doct($Databasecon, $ot, $do, $from, $to, $hours,1,$check, $objects);
			break;
			}
			case 6:
			{
			get_outprod($Databasecon, $ot, $do, $from, $to, $hours,1,$check, $objects);
			break;
			}
			case 7:
			{
			get_transfers($Databasecon, $ot, $do, $from, $to, $hours,1,$check, $objects);
			break;
			}
			case 8:
			{
			get_revizia($Databasecon, $ot, $do, $from, $to, $hours,1,$check, $objects);
			break;
			}
			case 9:
			{
			get_user_activity($Databasecon, $ot, $do, $hours,1,$check, $objects);
			break;
			}
			case 10:
			{
			get_finishedkol($Databasecon, $ot, $do, $from, $to, $hours,1,$check, $objects);
			break;
			}
			case 11:
			{
			get_razcr($Databasecon,1,$check,$objects);
			break;
			}
                        case 12:
			{
			get_minkol($Databasecon,1,$check, $objects);
			break;
			}
                        case 14:
			{
			get_grouprodukti($Databasecon, $ot, $do, $from, $to, $hours,1,$check, $objects);
			break;
			}
                        case 15:
			{
			get_klientbalans($Databasecon, $ot, $do, $klientid);
			break;
			}
                        case 16:
                        {
                        get_groupartikuli($Databasecon, $ot, $do, $from, $to, $hours,1,$check, $objects);
                        break;
                        }
                       case 17 :
			{
			get_pechalba2($Databasecon, $ot, $do);
			break;
			}
                        
                        case 20://готова
                                                 {
                                                 get_parpotoci($Databasecon, $ot, $do, $hours,1,$check, $objects);
                                                 break;
                                                 }
                         case 21:
                                                {
                                                get_moneymove($Databasecon, $ot, $do, $hours,1,$check, $objects);
                                                break;
                                                }
                        
                        case 22://ГОТОВА
                        {
                        get_revaluation($Databasecon, $ot, $do, $from, $to, $hours,1,$check, $objects);
                        break;
                        }
                        case 23:
                        {
                        get_worktime($Databasecon, $ot, $do, $from, $to, $hours,1,$check, $objects);
                        break;
                        }
                        case 24://ГОТОВА
                        {
                        get_oborbyoper($Databasecon, $ot, $do, $hours,1,$check, $objects);
                        break;
                        }
                        case 71:
                        {
                        get_partnerinfo($Databasecon, $klientid);
                        break;
                        }
                        
                        case 99:
			{
                            $day = $_POST['day'];
                            get_pechalbamainscreen($Databasecon, $day, $hours,1,$check, $objects);
			    break;
			}
                        case 98:
			{
                            
                            get_nalichnost($Databasecon);
			    break;
			}
                        case 97:
                        {
                        get_nalkaca($Databasecon,$objects);
                        break;
                        }
						case 96:
                        {
                        get_razcrobekti($Databasecon);
                        break;
                        }
                        
                        case 101 :
			{
                        
			get_artikuli($Databasecon, $from, $to,  1, $check,$objects );
			break;
			}
                        
                        case 66: //СПЕЦИАЛНА СПРАВКА :)
                        {  
                            
                            custom_script($Databasecon, $_SESSION['customsql1']);
                            //renumber_smetki($Databasecon);
                            break;
			}
                }
//echo "tralala"

?>