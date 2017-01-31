<?php
include('functions.php');

$parola = htmlspecialchars($_POST['password']);
$account = htmlspecialchars(strtoupper($_POST['accountname']));
$scriptversion = 60;//Версия на API
$version = $_POST['version'];
$role="";//АКО ИМА ПАРОЛА В БАЗАТА НА МИСТРАЛ тези се заместват от стойност във конфиг файла
$user = "SYSDBA";
$pass = "masterkey";
$usertype = 0;
$login_status = "";
if($scriptversion > $version)//Проверка на версия на API
 die('<script>toast("Грешена версия на клиентското приложение.<br/>Версия на сървъра '.$scriptversion.'<br/>Версия на приложението '.$version.'<br/>Моля обновете Андроид приложението или затворете и пуснете отново за IOS!", {"colour":"#d85c67", "fade_time":"3500"});</script>');    


if (is_file("settings/".$account.".php"))
include("settings/".$account.".php");
else
{   
   //die('<script>toast("Грешен потребител или грешна парола :  '.$parola.'", {"colour":"#d85c67", "fade_time":"1500"});</script>');
	die('<script>location.reload(true);</script>');
   }


//Проверява дали въведената от клиента база отговаря на md5 хеша
if( $conn_string == "none" )
{   if($_POST['databasepath'] == NULL)
    die('<script>toast("Не сте попълнили път до базата данни. Моля направете го през бутон настройки!", {"colour":"#d85c67", "fade_time":"1500"});</script>');
        if(md5(strtoupper($_POST['databasepath'])) != $hash)
    {
        
        die('<script>toast("Ползвате нелицензирана база данни! Ако сте сменили път до базата в обекта поискайте повторна регистрация през меню настройки.", {"colour":"#d85c67", "fade_time":"1500"});</script>');
        
    }
    else
    {   
        $conn_string = $_POST['databasepath'];
        $user = $_POST['databaseuser'];
        $pass = $_POST['databasepassword'];
       

    }
}

$today = date("d.m.Y");
$licenzdate = new DateTime($endaccount);
$todaydif = new DateTime($today);
$myFile = "log.txt";
$conn_string = "firebird:dbname=".$conn_string. "; role=".$role ;
//echo $conn_string;
//ДОБАВЯ СЕ СТРИНГА ЗА PDO

$log = $version.", " .$account.", ".$_SERVER['REMOTE_ADDR'].",".date("d.m.Y H:i:s").", ".$_SERVER['HTTP_USER_AGENT']."\r\n";




if ($licenzdate > $todaydif)//Ако не е изтекъл лиценза....
{





try {
			$Databasecon = new PDO($conn_string, $user, $pass);
			$Databasecon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        
                        
	}
catch (PDOException $e)
                    {
                    
                    $msg = $e->getMessage();
                    $fh = fopen($myFile, 'a');
                    fwrite($fh, $msg);
                    fclose($fh);
                    
                    die('<script>toast("Нямаме връзка с вашата база данни. Проверете интернет свързаността в търговския си обект или опитайте по късно! '.addslashes($msg).'",  {"colour":"#d85c67", "fade_time":"5000"} );</script>');
                    }

header('Content-Type: text/html; charset=utf-8');

$user2 = login($Databasecon, $parola, $usertype);
echo $usertype;
$objects = get_objects($Databasecon);//Масив от обекти

	if ($user2 != 1)
		{
            if(isset($_SESSION['conn_string'])){
                destroy_session();
                
            }
           
            session_status();       
            //session_destroy();
			session_start();            
			$_SESSION['admin'] = 'ok';
			$_SESSION['conn_string'] = $conn_string;
			$_SESSION['user'] = $user;
			$_SESSION['pass'] = $pass;
                        //Ако има зададен масив с къстъм sql заявки да го вдигне в сесията.
                        if(isset($customscript1))
                        {
                        $_SESSION['customsql1'] = $customscript1;
                        echo '<script> databasemanip = 1 </script>';//ДА ПОКАЗВА БУТОНА ЗА ИЗТРИВАНЕ НА ДАННИ
                        }
                        else
                        $_SESSION['customsql1'] = NULL;
                        
			echo '<script>  
                        toast("Добре дошъл '.mb_convert_encoding($user2, "UTF-8", "windows-1251").'<br/>Валиден лиценз до '.$endaccount.' г.", {"colour":"#d8cd5c", "fade_time":"1500"});'
                                . 'start_mrep('.$usertype.');</script>';
                        
                       
			}
	else
			{
			session_start();            
			$_SESSION['admin'] = 'notok';
                        echo '<script>toast("Неуспешен вход с парола :  \n  '.$parola.'", {"colour":"#d85c67", "fade_time":"900"});</script>';
			$login_status = "Грешна парола!"; 
			
			
			}
}
else
{
echo '<script>toast("Изтекъл лиценз на дата : '.$endaccount.' г.<br/>Обърнете се към доставчика на услугата за съдействие!", {"colour":"#d85c67", "fade_time":"3500"});</script>'; 
$login_status = "Изтекъл лиценз!";

}
 $fh = fopen($myFile, 'a');
 fwrite($fh, $login_status." ".$log);
 fclose($fh);

//echo '<script>alert("Несъществуващ потребител или грешна парола!");</script>';


?>