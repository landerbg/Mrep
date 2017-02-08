<?PHP
 
 
 
 
 class Crypt_object{
		var $mcrypt_cipher = MCRYPT_RIJNDAEL_128;
		var $mcrypt_mode = MCRYPT_MODE_CBC;
		public function decrypt($key, $iv, $encrypted)
		{
			$encrypted = base64_decode($encrypted);
			$decrypted = mcrypt_decrypt($this->mcrypt_cipher, $key, $encrypted, $this->mcrypt_mode, $iv);
			$block = mcrypt_get_block_size($this->mcrypt_cipher, $this->mcrypt_mode);
			$pad = ord($decrypted[($len = strlen($decrypted)) - 1]);
			return substr($decrypted, 0, strlen($decrypted) - $pad);
		}
		public function encrypt($key, $iv, $decrypted)
		{
			
			$block = mcrypt_get_block_size($this->mcrypt_cipher, $this->mcrypt_mode);
			$pad = $block - (strlen($decrypted) % $block);
			$decrypted .= str_repeat(chr($pad), $pad);
			$encrypted = mcrypt_encrypt($this->mcrypt_cipher, $key, $decrypted, $this->mcrypt_mode, $iv);
			return base64_encode($encrypted);
		}
		}
   

function get_columnnames($sth)
{
    $columns = $sth->fetch(PDO::FETCH_ASSOC);
//var_dump($columns);
 while (key($columns))      
 {      
        echo key($columns).': <input name="'.key($columns).'"></br>';
    
    next($columns);
} 
    exit();
    
}

function print_footerspravka($counter, $fromrow, $torow){
$diff = $torow-$fromrow;

return <<<Footer
   <br/><br/> <ul data-role="listview" class="li-zapisi">
   <li>
   <p>Намерени: $counter записа.</p>
   <p>Показани от : $fromrow до $torow</p>
   
   </li>    
   <li><a href="#data" data-nextratio="1"  data-mini="true" id="next1"></a></li> 
   <li><a href="#data" data-nextratio="2" data-mini="true" id="next2"></a></li> 
   <li><a href="#data" data-nextratio="3" data-mini="true" id="next3"></a></li>
   
   
     </li>
      </ul>
        <script>
        $('#next1').html("Още " + $('#rows').val() + " записа");
        $('#next2').html("Още " + $('#rows').val() * 2 + " записа");
        $('#next3').html("Още " + $('#rows').val() * 3 + " записа");
        </script>
Footer;
}
function apply_obektifilter($objects, $field)
{

$objects=json_decode($objects);
$objectfilters = "";
if($objects == -1)
    return $objectfilters;
        foreach($objects as $value)
            {  
            $objectfilters.= " AND ".$field." != ".$value;
             //echo $value;
            }
return $objectfilters;
}

function apply_rowsfilter($fromrow, $torow, $check)
{
  if($check == 1)
      $rows = "ROWS 1 to 1";
  else
      $rows = "ROWS ".$fromrow." TO ".$torow;
  return $rows;  
}

function fix_str($str)
{
    
$newstr = mb_convert_encoding($str, "UTF-8", "windows-1251");
return $newstr;
}
function fix_date($date)
{
    
$newdate = date('d.m.Y H:i:s', strtotime($date));
return $newdate;
}

function fix_numb($numb, $round)
{
   $newnumb = sprintf("%01.".$round."f", $numb);
   return $newnumb;
}
function hoursToSecods ($hour) { // $hour must be a string type: "HH:mm:ss"

    $parse = array();
    if (!preg_match ('#^(?<hours>[\d]{2}):(?<mins>[\d]{2}):(?<secs>[\d]{2})$#',$hour,$parse)) {
         // Throw error, exception, etc
         throw new RuntimeException ("Hour Format not valid");
    }

         return (int) $parse['hours'] * 3600 + (int) $parse['mins'] * 60 + (int) $parse['secs'];

}

function encrypt_mistralpass($str)
{
  for ($i = 0, $j = strlen($str); $i < $j; $i++) {
 
	$str{$i} = chr(ord($str{$i}) + 30);
	
  }
  return $str;
}

function startsWith($haystack,$needle,$case=true) {
    if($case){return (strcmp(substr($haystack, 0, strlen($needle)),$needle)===0);}
    return (strcasecmp(substr($haystack, 0, strlen($needle)),$needle)===0);
}

function strToHex($string)
		{
			$hex='';
			for ($i=0; $i < strlen($string); $i++)
			{
				$hex .= dechex(ord($string[$i]));
			}
			return $hex;
		}

		function hexToStr($hex)
		{
			$string='';
			for ($i=0; $i < strlen($hex)-1; $i+=2)
			{
				$string .= chr(hexdec($hex[$i].$hex[$i+1]));
			}
			return $string;
		}



?>


