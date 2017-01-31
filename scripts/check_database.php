<?php

$conn_string   = $_POST['databasepath']; 
$user = $_POST['databaseuser'];
$pass = $_POST['databasepassword'];
   

//Проверява дали въведената от клиента база отговаря на md5 хеша

       // if(md5($_POST['databasepath']) != $hash)
   
$myFile = "log.txt";
$fh = fopen($myFile, 'a');
$log = $_SERVER['REMOTE_ADDR'].",Проверена база! , ".date("d.m.Y H:i:s")."\r\n";
$stringforhash = $conn_string;
$conn_string = "firebird:dbname=".$conn_string;
try {
			$Databasecon = new PDO($conn_string, $user, $pass);
			$Databasecon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        fwrite($fh, $log);
                        
	}
catch (PDOException $e)
                    {
                    
                    echo $e->getMessage();
                    fwrite($fh, $e->getMessage());
                    fclose($fh);
                    die('Няма връзка с вашата база данни!');
                    
                    }

fclose($fh);
$sth = $Databasecon->query("select * from verdb");
while ($row = $sth->fetch(PDO::FETCH_OBJ))
{
    echo "<p>Успешна връзка с база данни!</p>";
    echo "<p>Версия на базата : ".$row->MAJORVER.".".$row->MINORVER.".".$row->VERDB."</p>";
    echo "</br>Лицензен ключ за тази база :<br><span id='hash'>".md5(strtoupper($stringforhash))."</span>";
    echo '<p>
            Желано потребителско име:
            <input type="text" id="reg_user" placeholder="Латиница!"/> 
          <p><br/>';
    echo '<a href="#" data-role="button" style="background:#007734;color:white;" id="askregistration">Поискай регистрация</a>'; 
    
}


?>