<?PHP
require_once('custom_functions.php');
//Функция която трансформира obektnameid в obektid

function get_procedures_params($DB)
//TAZI PROVERKA E ZARADI NOVIA PARAMETAR V PROCEDURITE SLED 1947 versia
{
$sth = $DB->query("select MINORVER from verdb");
$row = $sth->fetch(PDO::FETCH_OBJ);
$version = $row->MINORVER;
$DB = null;
if($version < 1947)
	return 
"";//NIAMA NOVIA TRETI PARAMETAR
else	
	return ", null";//TOVA MOJE DA SE POLZVA DA SE PODAVAT ID NA OBEKTITE RAZDELENI SAS ZAPETAIA
	
}

function print_interval($ot, $do, $hours)
{
	return '
				<p class="ui-mini">'.$ot.' '. $hours.':00:00 - '.$do.' '.$hours.':00:00</p>';
}
function get_obektid($DB, $obektnameid)
{
    $result = 0;
    $sth    = $DB->query("SELECT obekti.id from obekti 
                join obektinames on obekti.name = obektinames.obekt 
                where obektinames.id = " . $obektnameid);
    if ($sth) {
        foreach ($sth as $row) {
            $result = $row[0];
        }
    }
    $DB = NULL;
    if ($result > 0)
        return $result;
    else
        return 1;
}
function get_obektnameid($DB, $obektid)
{
    //puska rejim na pokazvane na greshki
    $result = 0;
    $sth    = $DB->query("SELECT obektinames.id from obektinames 
                join obekti on obektinames.obekt = obekti.name 
                where obekti.id = " . $obektid);
    if ($sth)
        foreach ($sth as $row) {
            $result = $row[0];
        }
    if ($result > 0)
        return $result;
    else
        return 1;
}
function check_dbtype($DB)
{
    //puska rejim na pokazvane na greshki
    $result = 0;
    $sth    = $DB->query("SELECT typedb from verdb");
    if ($sth)
        foreach ($sth as $row) {
            $result = $row[0];
        }
    echo $result;
}
//POKAZVA VSICHKI SMETKI ZA DADENI PERIOD
//AKO NIAMA RESULTAT DA SE DOBAVI NIAMA DANNI.V MOMENTA BIE GRESHAK CHE NIAMA ROW
function get_smetki($DB, $from, $to, $fromrow, $torow, $hours, $filters, $check, $objects)
{
    $counter       = 0;
    $objectfilters = apply_obektifilter($objects, "SMETKI.OBEKTID");
    $rows          = apply_rowsfilter($fromrow, $torow, $check);
    
    $sql           = <<<SQL
      SELECT
        smetki.nomer AS NOMER,
        smetki.prdate AS PRDATE,
        smetki.macare AS MACARE,
        smetki.timeopen AS TIMEOPEN,
        smetki.editdate AS EDITDATE,
        partnernames.partnername AS PARTNERNAME,
        smetki.docno AS DOCNO,
        smetki.razcr AS RAZCR,
        smetki.razcrsuma AS RAZCRSUMA,
        smetki.kaca AS KACA,
        doctypenames.doctype AS DOCTYPE,
        obekti.name AS OBEKTNAME,
        obekti.id  AS OBEKTID,
        (select min(moneyoper.suma) from moneyoper where smetki.obektid = moneyoper.obektid and smetki.nomer = moneyoper.smetkano) AS SUMA
        from smetki 
        join partnernames on smetki.partnernameid = partnernames.id 
        join doctypenames on smetki.doctypeid = doctypenames.id
        join obekti on smetki.obektid = obekti.id 
        where smetki.prdate > '$from $hours:00:00'   and smetki.prdate < '$to $hours:00:00' $objectfilters
        order by prdate
        $rows
SQL;
    //echo $sql;
    //echo $sql;
    $sth           = $DB->query($sql);
    if ($check == 1) //ОПИТ ЗА ИЗЧИТАНЕ НА КОЛОНИТЕ.МОЖЕ ДА СЕ ПРОМЕНЯ ТОВА
        {
        get_columnnames($sth); //Печата колоните и излиза с exit();
    }
    //ЗАПОЧВА ГЕНЕРИРАНЕТО НА HTML в $result
    $result = '<ul data-role="listview" data-theme="a" data-filter="true" class="spravka-ul" data-input="#filterBasic-input">';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $counter++;
        $result .= '
<li data-theme="a" class="liresult" data-spravka2="0" data-obektnum="' . $row->OBEKTID . '" data-num="' . $row->NOMER . '"> 
<div class="containerspravka"><div class="importantdiv" >
<p>
<span class="obektvspravka">' . fix_str($row->OBEKTNAME) . '</span>
<span class="datavspravka">' . fix_date($row->PRDATE) . "</span>
</p>";
        if ($row->PARTNERNAME != NULL) //Ако има клиент го печата
            $result .= '<p><span class="partnervspravka">' . fix_str($row->PARTNERNAME) . '</span></p>';
        $result .= '<p><span>' . fix_str($row->DOCTYPE) . ' : ' . $row->DOCNO . '</span></p>';
        if ($row->EDITDATE != NULL)
            $result .= '<p style="color:#ED4337">Редакция: ' . fix_date($row->EDITDATE) . '</p>';
        $result .= '<p><span class="sumaspravka">Сума : ' . fix_numb($row->SUMA, 2) . ' лв.</span></p></div>';
        //НАЧАЛО НА НЕЩАТА КОИТО СЕ ВИЖДАТ САМО НА ЛАНДСКЕЙП
        $result .= '<div class="notimportantdiv">';
        if ($row->MACARE != NULL) //АКО ИМА МАСА И ЧАС НА ОТВАРЯНЕ
            {
            if (hoursToSecods(date('H:i:s', strtotime($row->TIMEOPEN))) + 10800 < hoursToSecods(date('H:i:s', strtotime($row->PRDATE))))
                $color = "red"; //АКО Е ОТВОРЕНА  ПОВЕЧЕ ОТ ТРИ ПРЕДИ ПРИКЛЮЧВАНЕ  ЧЕРВЕНО....
            else
                $color = "black";
            $result .= '<p><span class="datavspravka" ' . $color . ';"><strong>Отворена в: ' . fix_date($row->TIMEOPEN) . '</strong></span></p>
           <p><span >Маса: <strong>' . $row->MACARE . '</strong></span></p>';
        }
        $result .= '<p>Сметка номер : <strong> ' . $row->NOMER . '</strong></p>';
        if ($row->RAZCR == 1)
            $result .= '<p>Разсрочено,  Сума разср. : <strong>' . fix_numb($row->RAZCRSUMA, 2) . '  </strong>,    Каса : <strong>' . $row->KACA . '</strong></p>';
        else
            $result .= '<p>Платено ,  Каса : <strong>' . $row->KACA . '</strong></p>';
        $result .= '</div></div></li>';
    }
    $result .= '</ul>';
    echo $result; //ПРИНТИРА ГЕНЕРИРАНИЯ HTML
    echo print_footerspravka($counter, $fromrow, $torow); //ДОБАВЯ СТАНДАРТНИЯ ФУТЪР ЗА СПРАВКИТЕ С ПРОДЪЛЖЕНИЕ
}
//FUNKCIA KOIATO VIZUALIZIRA KONKRETNA PRODAJBA PO ID
//DA SE NAPRAVI DA POKAZVA KOREKTNO PRODAJBITE PRI BAZA S OBEKTI!!!!!!!MOJE BI E NAPRAVENO VECHE
function print_smetka($DB, $number, $object)
{
    $sum      = 0;
    $sum2     = 0;
    $edittime = false;
    $sql2     = <<<SQL
SELECT
smetki.nomer as NOMER,
smetki.prdate as PRDATE,
smetki.macare AS MACARE,
smetki.timeopen AS TIMEOPEN,
smetki.editdate AS EDITDATE,
partnernames.partnername AS PARTNERNAME,
smetki.docno AS DOCNO,
smetki.razcr AS RAZCR,
smetki.razcrsuma AS RAZCRSUMA,
smetki.kaca AS KACA,
doctypenames.doctype AS DOCTYPE,
obekti.name AS OBEKTNAME,
obekti.id  AS OBEKTID
from smetki 
join partnernames on smetki.partnernameid = partnernames.id 
join doctypenames on smetki.doctypeid = doctypenames.id
join obekti on smetki.obektid = obekti.id
where smetki.nomer = $number and smetki.obektid = $object
SQL;
    $sth2     = $DB->query($sql2);
    $result   = '<ul data-role="listview" data-theme="a">';
    while ($row2 = $sth2->fetch(PDO::FETCH_OBJ)) {
        $result .= '<li id=' . $row2->NOMER . '><div align="center">';
        if ($row2->PARTNERNAME != NULL)
            $result .= '<h4>' . fix_str($row2->PARTNERNAME) . '</h4>';
        $result .= '<h5>' . fix_str($row2->DOCTYPE) . ' - ' . $row2->DOCNO;
        $result .= '</h5><h6>Дата : ' . fix_date($row2->PRDATE) . '</h6></div></li></ul>';
        $edittime = $row2->EDITDATE;
    }
 
    $sql = <<<SQL
SELECT  
smetkisdr.artnomer as NOMER, 
artikulnames.artikul AS ARTIKUL, 
smetkisdr.kol AS KOL, 
smetkisdr.salesprice AS SALESPRICE, 
smetkisdr.defsalesprice AS DEFSALESPRICE
from smetkisdr 
join artikulnames on smetkisdr.artikulid = artikulnames.id
where smetkisdr.nomer = $number and smetkisdr.obektid = $object
order by artikulnames.artikul ASC
SQL;
    $sth = $DB->query($sql);
    $result .= '<ul data-role="listview" data-theme="a" data-filter="true" data-input="#filter-spravka"><input type="search" id="filter-spravka" data-mini="true">';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $result .= '<li id=' . $row->NOMER . ' data-theme="a">';
        $result .= '<p>No.' . $row->NOMER . ' : <b>' . fix_str($row->ARTIKUL) . '</b></p>';
        //opraviame kirilicata
        $result .= '<p>' . $row->KOL;
        $result .= "  *  " . fix_numb($row->SALESPRICE, 2) . "    =  ";
        $result .= fix_numb($row->KOL * $row->SALESPRICE, 2) . " лв.";
        $sum += $row->KOL * $row->SALESPRICE;
        $sum2 += $row->KOL * $row->DEFSALESPRICE;
        if ($row->SALESPRICE != $row->DEFSALESPRICE && $row->DEFSALESPRICE != 0)
            $result .= ' - <span style="color:#FF704D;">(Отстъпка: ' . fix_numb(100 * ($row->DEFSALESPRICE - $row->SALESPRICE) / $row->DEFSALESPRICE, 2) . ')</span></p>';
        $result .= "</li>";
    }
    $result .= '<li data-theme="a"><div align="center"><h1>Обща стойност: ' . sprintf("%01.2f", $sum) . ' лв.</h1>';
    //var_dump($row2);
    if ($sum != $sum2 && $sum2 != 0) //Проверява дали има отстъпка като сравнява сумата по реални и стандартни цени
        $result .= '<p style="color:red;"><strong>Отстъпка: ' . fix_numb(100 * ($sum2 - $sum) / $sum2, 2) . '% - ' . fix_numb(($sum2 - $sum), 2) . ' лв.</strong>';
    if ($edittime != false)
        $result .= '<p><a href="#data3" type="button" id="redakcia" data-transition="flip" onclick="show_smetkared(' . $number . ')">Провери редакция:</a></p></div></li></ul>';
    echo $result;
}
function print_smetkared($DB, $number, $object)
//DA SE OPTIMIZIRA POKAZVANETO NA REDAKCIITE.PO SLOJNA E SITUACIATA!!!!!!!!!
    
//MOJE BI E GOTOVO
{
    $theme  = "a";
    $id     = 0;
    $date   = 0;
    $docno  = 0;
    $count  = 0;
    $sum    = 0;
    $sql    = "select
                                editsmetki.smetkanomer AS SMETKANOMER,
                                editsmetkisdr.artnomer AS ARTNOMER,
                                artikulnames.artikul AS ARTIKUL,
                                editsmetkisdr.kol AS KOL,
                                editsmetkisdr.salesprice AS SALESPRICE,
                                editsmetkisdr.defsalesprice AS DEFSALESPRICE, 
                                editsmetki.id AS SMETKAID,
                                editsmetki.editdate AS EDITDATE,
                                editsmetki.docno AS DOCNO
                                from editsmetkisdr join artikulnames
                                on editsmetkisdr.artikulid = artikulnames.id
                                join editsmetki on editsmetkisdr.editsmetkiid = editsmetki.id
                                where editsmetki.smetkanomer = " . $number . "
                                order by editsmetkisdr.id desc, artikulnames.artikul asc";
    $sth    = $DB->query($sql);
    $result = '
            <ul data-role="listview" data-theme="a">
            ';
    //iconv('UTF-8', 'Windows-1251', $row->ARTNOMER)
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        if ($id == 0)
            $id = $row->SMETKAID;
        if ($date == 0) ///!!! да се шрпвери какво е това
            $date = $row->EDITDATE;
        if ($docno == 0)
            $docno = $row->DOCNO;
        $result .= '<li id=' . $row->SMETKANOMER . ' data-theme="' . $theme . '">';
        $result .= '<p>No.' . $row->ARTNOMER . ' - <b>' . mb_convert_encoding($row->ARTIKUL, "UTF-8", "windows-1251") . '</b></p>';
        $result .= '<p>' . $row->KOL;
        $result .= "  *  " . sprintf("%01.2f", $row->SALESPRICE) . "    =  ";
        $result .= sprintf("%01.2f", $row->KOL * $row->SALESPRICE) . "";
        if ($id != $row->SMETKAID) {
            $count++;
            $sum += $row->KOL * $row->SALESPRICE;
            $result .= '     
                    <li data-theme="' . $theme . '"><p>Обща стойност: ' . sprintf("%01.2f", $sum) . ' лв.</p>
                    <p style="color:#FF704D">Редактирана на: ' . date('d.m.Y H:i:s', strtotime($row->EDITDATE)) . '</p>
                    <p>Документ номер: ' . $row->DOCNO . '</p>';
            $date  = $row->EDITDATE;
            $docno = $row->DOCNO;
            if ($theme == "a")
                $theme = "c";
            else
                $theme = "a";
            $sum = 0;
        } else
            $sum += $row->KOL * $row->SALESPRICE;
    }
    if ($count == 0) {
        $result .= '     
                    <li data-theme="' . $theme . '"><p>Обща стойност: ' . sprintf("%01.2f", $sum) . ' лв.</p>
                    <p style="color="red";">Редактирана на: ' . date('d.m.Y H:i:s', strtotime($date)) . '</p>';
    }
    echo $result;
}
///////////////////////
//////////////////////
//Функция която показва на екран оборотите за всички обекти групирани по дни за зааддения период
//DA LI NE MOJE DA IZLIZA PO BYRZO
//////////////////////
//////////////////////            
function get_oboroti($DB, $from, $to) // DA SE prenapishe vidi dali moje da izlizat broj artikuli
{
    //$objectfilters = apply_obektifilter($objects, "SMETKI.OBEKTID");
    //$rows = apply_rowsfilter($fromrow, $torow, $check);
	global $inobekti;
$sql             = <<<SQL
SELECT
OBEKTNAME,
DATEONLY,
SUM(OBOR) AS OBOR,
SUM(COUNTSMETKISDR) AS COUNTSMETKI,
SUM(NUMBERCLIENTS) AS NUMBERCLIENTS
FROM QOBOR('$from 00:00:00', '$to 00:00:00' $inobekti)
GROUP BY OBEKTNAME, DATEONLY
ORDER BY OBEKTNAME, DATEONLY
SQL;
    //echo $sql;
    $totaldoct       = 0;
    $totalsum        = 0;
    $totalbroismetki = 0;
    //броячи на тоталите
    //резултатния HTML
	try{ $sth             = $DB->query($sql);
	}
	catch (Exception $e) 
	{	$result = '<p>Тази справка не се поддържа от вашата версия на базата на Мистрал.</p>';
		echo $result;
		exit();
	}
    $result          = '<table data-role="table" data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a" data-filter="true" data-input="#filterBasic-input">
                    <thead>
                        <tr class="ui-bar-d">
                          <th >Обект</th>
                          <th>Дата</th>
                          <th >Сума пр.цени</th>
                          <th data-priority="5">Брой сметки</th>
                          <th data-priority="6">Брой клиенти.</th>
                        </tr>
                    </thead>
                    <tbody>';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $result .= '
                            <tr>
                            <th>' . fix_str($row->OBEKTNAME) . '</th>
                            <td>' . $row->DATEONLY . '</td>
                            <td>' . fix_numb($row->OBOR, 2) . ' лв.</td>
                            <td>' . $row->COUNTSMETKI . '</td>
                            <td>' . $row->NUMBERCLIENTS . '</td>  
                            </tr>';
        $totalsum += $row->OBOR;
        $totalbroismetki += $row->COUNTSMETKI;
    }
    $result .= '
                        <tr>
                        <td></td>
                        <td></td>
                        <td><strong>' . sprintf("%01.2f", $totalsum) . ' лв.</strong></td>  
                        <td><strong>' . $totalbroismetki . ' бр.</strong></td>
                        <td></td>
                        </tr></tbody></table>';
    echo $result;
}
///////////////////////
//////////////////////
//Функция която показва на екран статистика за всеки обект за зададен период
//печалба, брой  смети , ср.надценка и др.
//////////////////////
//////////////////////
function get_pechalba($DB, $from, $to, $fromrow, $torow, $obekti, $hours)
{
    $sumstorno = 0;
    $result    = '
<table data-role="table"  data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a">
<thead>
    <tr class="ui-bar-d">
      <th>Обект</th>
      <th>Продажби</th>
      <th>Дост.цени</th>
      <th>Печалба</th>
      <th>Надценка</th>
      <th data-priority="4">Брой сметки</th>
      <th data-priority="5">Ср.сметка </th>
      <th data-priority="6">Сторнирания </th>
    </tr>
</thead>
<tbody>';
    //НАМИРА НАЙ МАЛЪК И НАЙ ГОЛЯМ НОМЕР НА СМЕТКА ОТ SMETKI за ВСЕкиИ ОБЕКТ ЗА ЗАДАДЕНИЯ ПЕРИОД
    $sql       = "select 
        min(nomer) as minnomer, 
        max(nomer) as maxnomer, 
        obekti.id from smetki
        join obekti on smetki.obektid = obekti.id 
        where smetki.prdate > '" . $from . " " . $hours . ":00:00' and smetki.prdate < '" . $to . " " . $hours . ":00:00'
        group by obekti.id";
    $midsth    = $DB->query($sql);
    if ($midsth)
        foreach ($midsth as $row1) {
            $sql1 = "select
                obekti.name,
                sum (smetkisdr.kol*smetkisdr.salesprice),
                sum (smetkisdr.kol*smetkisdr.doctprice),
                count (*),
                obekti.id
                from smetkisdr
                join obekti on smetkisdr.obektid = obekti.id
                where 
                smetkisdr.nomer between " . $row1[0] . " and " . $row1[1] . "
                and obekti.id = " . $row1[2] . "
                group by obekti.name, obekti.id
                order by obekti.name";
            //echo $sql1;
            $laststh = $DB->query($sql1);
            foreach ($laststh as $row) {
                //ИЗЧИСЛЯВА СТОРНИРАНИТЕ СУМИ ЗА ПЕРИОДА
                $sqlstorno = "select  
            sum(kol*CAST(salesprice AS BIGINT))
            from clearrows
            where dateclear > '" . $from . " " . $hours . ":00:00'
            and dateclear < '" . $to . " " . $hours . ":00:00'
            and obektnameid = " . get_obektnameid($DB, $row[4]);
                //echo $sqlstorno;
                $stornosth = $DB->query($sqlstorno);
                foreach ($stornosth as $storno) {
                    $sumstorno = $storno[0];
                }
                //КРАЙ $ sumstorno съдържа сумата
                $result .= '
        <tr>
        <th><h3>' . mb_convert_encoding($row[0], "UTF-8", "windows-1251") . '</h3></th>
        <td>' . sprintf("%01.2f", $row[1]) . ' лв. </td>
        <td>' . sprintf("%01.2f", $row[2]) . ' лв. </td>
        <td>' . sprintf("%01.2f", $row[1] - $row[2]) . ' лв.</td>';
                if ($row[1] != 0)
                    $result .= '<td>' . sprintf("%01.2f", ($row[1] - $row[2]) / $row[1] * 100) . '%</td>';
                $result .= '<td>' . $row[3] . '</td>';
                if ($row[3] != 0)
                    $result .= '<td>' . sprintf("%01.2f", $row[1] / $row[3]) . ' лв.</td>';
                $result .= '<td><span style="color:red;">' . sprintf("%01.2f", $sumstorno) . ' лв.</span></td></tr>';
            }
            /*$broi = $row[3];
            
            $seconds = 0;
            //Izchisliava sredna prodyljitelnost na masata.Ne raboti ako sa poveche obekti.DA SE DOBAVI!!!!
            $sth = $DB->query("select prdate, timeopen from smetki where smetki.prdate > '".$from." 00:00:00' and smetki.prdate < '".$to." 23:59:59'");
            if($sth)
            foreach($sth as $row) 
            {
            if ((strtotime(substr($row[0], 11, 8)) - strtotime($row[1])) > 0)
            $seconds += (strtotime(substr($row[0], 11, 8)) - strtotime($row[1]));
            else 
            $seconds += (strtotime(substr($row[0], 11, 8)) - strtotime($row[1])) + 86400;
            //Ako masata e zatvorena sled 24.00 chasa
            }
            //DA SE IMPLEMENTIRA SREDNO VREME ZA OTVORENA MASA
            //$result .='<td><p>Средно отворена маса: '.sprintf("%01.2f", $seconds/$broi/60).' минути</p></td>';*/
        }
    $result .= '</tbody></table>';
    echo $result;
}


function get_pechalba2($DB, $from, $to)
{
    $sumsales = 0;
    $sumdoct = 0;
    $counter = 0;
    $totalnad1 = 0;
    $totalnad2 = 0;
    $sumrev = 0;
    $sumrazh = 0;
    $sumprofit = 0;
    $result    = '
<table data-role="table"  data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a">
<thead>
    <tr class="ui-bar-d">
      <th>Обект</th>
      <th>Продажби</th>
      <th>Дост.цени</th>
      <th>Печалба</th>
      <th>Надценка</th>
      <th data-priority="4">Разходи</th>
      <th data-priority="5">% печ.</th>
      <th data-priority="6">Ревизии</th>
    </tr>
</thead>
<tbody>';
global $inobekti;
$sql1 = <<<SQL
select * from QPROFITBYPERIOD('$from', '$to' $inobekti)
SQL;
 //echo $sql1;
            $laststh = $DB->query($sql1);
        while ($row = $laststh->fetch(PDO::FETCH_OBJ)) {
              
                $result .= '
        <tr>
        <th><h3>' .fix_str($row->OBEKTNAME). '</h3></th>
        <td>' . fix_numb($row->SUMASALESPRICE, 2) . '</td>
        <td>' . fix_numb($row->SUMADOCTPRICE, 2) . '</td>
        <td>' . fix_numb($row->SUMAPROFIT, 2) . '</td>
        <td>' . $row->NAD. ' %</td>
        <td>' . fix_numb($row->SUMCOST, 2) . '</td>
        <td>' . $row->NADSUMSALESPRICE . ' %.</td>
        <td>' . fix_numb($row->SUMREV, 2) . '</td>
                </tr>';
    $sumsales += $row->SUMASALESPRICE;
    $sumdoct += $row->SUMADOCTPRICE;
    $sumprofit += $row->SUMAPROFIT;
    $counter++;
    $totalnad1 += $row->NAD;
    $totalnad2 += $row->NADSUMSALESPRICE;
    $sumrev += $row->SUMREV;
    $sumrazh += $row->SUMCOST;
            }
    if($counter == 0)
        $counter = 1;
        
    $result .= '<tr>'
            . '<td>Общо</td>'
            . '<td>'. fix_numb($sumsales, 2).'</td>'
            . '<td>' . fix_numb($sumdoct, 2) . '</td>'
            . '<td>' . fix_numb($sumprofit, 2) . '</td>'
            . '<td>' . fix_numb($totalnad1/$counter , 2). ' %</td>'
            . '<td>' . fix_numb($sumrazh, 2) . '</td>'
            . '<td>' . fix_numb($totalnad2/$counter , 2). ' %</td>'
            . '<td>' . fix_numb($sumrev, 2) . '</td>'
            . '</tr></tbody></table>';
    echo $result;
}
///////////////////////
//////////////////////
//Функция която показва на екран изтритите редове - таблицата CLEARROWS
//////////////////////
//////////////////////
function get_storno($DB, $from, $to, $fromrow, $torow, $obekti, $hours, $filters, $check) //ПРОВЕРЕНА
{
    $counter  = 0;
    $newtheme = 0;
	global $inobekti;
    $theme    = "a";
    $sql      = <<<SQL
SELECT
DATECLEAR,
MACA,
KOL,
SALESPRICE,
OBEKTNAME,
OPER,
ARTIKUL,
NOMER,
DELFROMOPER
FROM QCLEARROWS('$from $hours:00:00', '$to $hours:00:00' $inobekti)
ORDER BY DATECLEAR DESC
ROWS $fromrow TO $torow
SQL;
    $sth      = $DB->query($sql);
    if ($check == 1) {
        get_columnnames($sth);
    }
    $result = '<ul data-role="listview" data-theme="a" data-filter="true"  data-input="#filterBasic-input">';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $counter++;
        if ($newtheme != $row->DATECLEAR) {
            if ($theme == "a")
                $theme = "b";
            else
                $theme = "a";
            $newtheme = $row->DATECLEAR;
        }
        $result .= '<li data-theme="' . $theme . '" class="liresult">';
        $result .= '<p><span class="obektvspravka">' . fix_str($row->OBEKTNAME) . '</span><span class="datavspravka">' . fix_date($row->DATECLEAR) . '</p>';
        $result .= '<p><strong><span class="partnervspravka">' . fix_str($row->ARTIKUL) . '</span></strong></p>';
        $result .= '  <p>' . $row->KOL;
        $result .= "  *  " . fix_numb($row->SALESPRICE, 2) . "    =  ";
        $result .= fix_numb($row->KOL * $row->SALESPRICE, 2) . "</p>";
        $result .= '<p>Оператор: ' . fix_str($row->OPER) . ' (<span style="color:#ED4337">' . fix_str($row->DELFROMOPER) . '</span>)</p></li>';
    }
    $result .= '</ul>';
    echo $result;
    echo print_footerspravka($counter, $fromrow, $torow);
}
///////////////////////
//////////////////////
//Функция която показва на екран Всички партньори които имат разсрочени плащания
//////////////////////
//////////////////////
function get_razcr($DB) //NE MI SE OCVETIAVA REDA CLICKEDLI
{
    $sql    = <<<SQL
select
        partners.nomer AS NOMER
      , partners.name AS NAME
      , actionid AS ACTIONID     
      , sum (RAZCR.SUMA) as SUMA
      , count (*)
     from RAZCR
     join partners on razcr.partnernomer = partners.nomer and  RAZCR.OBEKTID = PARTNERS.OBEKTID
     group by  partners.nomer, partners.name,  actionid
     order by  suma desc
SQL;
    $sum    = 0;
    $sum2   = 0;
    $sth    = $DB->query($sql);
    $result = '<ul data-role="listview"  data-filter="true" class="spravka-ul" data-input="#filterBasic-input">';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        if ($row->ACTIONID == 1)
            $sum += $row->SUMA;
        else
            $sum2 += $row->SUMA;
        $result .= '<li class="liresult"  data-num="' . $row->NOMER . '" data-obektnum="1" data-spravka2="2" >';
        $result .= '<p><span class="obektvspravka">No.' . $row->NOMER . ' - ' . fix_str($row->NAME) . '</span></p>';
        $result .= '<p>От <strong>' . $row->COUNT . '</strong> сметки</p>';
        if ($row->ACTIONID == 1) {
            $result .= '<p><span class="sumaspravka">Сума : ' . sprintf("%01.2f", $row->SUMA) . 'лв.</span></p>';
        }
        if ($row->ACTIONID == 2) {
            $result .= '<p><span class="sumaspravka" style="color:#ED4337">Сума : ' . sprintf("%01.2f", -$row->SUMA) . 'лв.</span></p>';
        }
        $result .= '</li>';
        //opraviame kirilicata
    }
    $result .= ' 
                        <li data-theme="a">
                        <h1>От клиенти:  ' . sprintf("%01.2f", $sum) . ' лв.
                        </h1>
                        <h1>Към доставчици: ' . sprintf("%01.2f", $sum2) . ' лв.</h1></li></ul>';
    echo $result;
}
///////////////////////
//////////////////////
//Функция която показва на екран сметките на разсрочено за зададен партньор $klient  номера на партньора в таблицата PARTNERS
//////////////////////
//////////////////////
function get_razcrdetails($DB, $klient)
{
    $totalsum = 0;
    $sql      = <<<SQL
select
   obekti.name AS OBEKTNAME
 , partners.name AS PARTNERNAME
 , RAZCR.SMETKADATE AS SMETKADATE
 , RAZCR.SUMA AS SUMA
 , RAZCR.DOCNO AS DOCNO
 , RAZCR.DOCTYPE AS DOCTYPE
 , RAZCR.SUMLASTDOCTPRICE AS SUMADOCT
 , RAZCR.DTBACK AS DTBACK
 from RAZCR 
   JOIN  partners on razcr.partnernomer = partners.nomer
   JOIN  obekti on razcr.obektid = obekti.id
 where RAZCR.PARTNERNOMER = $klient 
and
RAZCR.OBEKTID = PARTNERS.OBEKTID
and
RAZCR.OBEKTID = OBEKTI.ID    
order by RAZCR.SMETKADATE ASC
SQL;
    $sth      = $DB->query($sql);
    $result   = '
        <table data-role="table"  data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a">
        <thead>
                <tr class="ui-bar-d">
                  <th data-priority="3">Обект</th> 
                  <th >Партньор</th>
                  <th>Дата</th>
                  <th>Сума</th>
                  <th>Док.номер</th>
                  <th data-priority="5">Док тип</th>
                  <th>Сума дост.цени</th>
                  <th data-priority="6">Падеж</th>
                </tr>
        </thead>
        <tbody>';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $result .= '
                        <tr>
                        <th>' . fix_str($row->OBEKTNAME) . '</th>
                        <td>' . fix_str($row->PARTNERNAME) . '</td>
                        <td>' . date('d.m.Y', strtotime($row->SMETKADATE)) . '</td>
                        <td>' . fix_numb($row->SUMA, 2) . ' лв.</td>
                        <td>' . $row->DOCNO . '</td>
                        <td>' . fix_str($row->DOCTYPE) . '</td>
                        <td>' . fix_numb($row->SUMADOCT, 2) . '</td>
                        <td>' . date('d.m.Y', strtotime($row->DTBACK)). '</td>
                        </tr>';
        $totalsum += $row->SUMA;
    }
    $result .= '<tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><strong>' . fix_numb($totalsum, 2) . ' лв.</strong></td>                        
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        </tr></tbody></table>';
    echo $result;
}
function test($DB)
{
	global $inobekti;
	if($inobekti == ', null')
		$inobekti2 = '(null)';
	else
		$inobekti2 = '';
    $sql     = "
select 
       ARTIKUL
     , KOL
     , SALESPRICE
     , MACA
     , KLIENT
     , TIMEOPEN
     , POS
     , OPER
     , NUMBERCLIENTS
    FROM QTEKSMETKI$inobekti2 where maca > 0
    ORDER BY MACA ";
	
    $sth     = $DB->query($sql);
    /*$result = '
    <table data-role="table" id="monitor1" data-mode="reflow" class="ui-body-d ui-shadow ui-responsive mytable">
    <thead>
    <tr class="ui-bar-d">
    <th >Арт.</th> 
    <th>Кол.</th>
    <th>Цена.</th>
    <th>Маса</th> 
    <th>Час</th>
    </tr>
    </thead>
    <tbody>';
    if($sth)
    foreach($sth as $row) 
    {
    //$today = time();
    //echo $today;
    //echo '<br>';
    //echo strtotime($row->TIMEOPEN);
    
    $result .= '
    <tr align="left">
    <th>'.mb_convert_encoding($row->ARTIKUL, "UTF-8", "windows-1251").'</th>
    <td>'.sprintf("%01.0f", $row->KOL).'</td>
    <td>'.sprintf("%01.2f", $row->SALESPRICE).'</td>
    <td>'.$row->MACA.'</td>';
    //TODO - da se napravi da ocvetiava v cherveno reda ako ima razlika 3 chasa ot tekustia chas
    $time = hoursToSecods($row->TIMEOPEN);
    $now = (date("H:i:s"));
    $now = hoursToSecods($now);
    //echo $now."..".$time;
    $diff =  $now - $time;
    //echo $diff."  ";
    if(abs($diff) > 10800 or $time > $now + 3700)
    $result .='<td style="background:gold">'.$row->TIMEOPEN.'</td>
    </tr>';
    else
    $result .='<td>'.$row->TIMEOPEN.'</td>
    </tr>';
    
    
    
    
    }
    $result.='</tbody></table>';
    
    echo $result;*/ //$counter=0;
    $maca    = 0;
    $sum     = 0;
    $counter = 0;
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        if ($maca != $row->MACA) {
            if ($maca != 0)
                echo '</div><span>' . fix_numb($sum, 2) . ' лв.</span></div>';
            $maca = $row->MACA;
            $sum  = 0;
            //$sum += $row->KOL*$row->SALESPRICE;
            $time = $row->TIMEOPEN;
            $now  = (date("H:i:s"));
            $diff = $now - $time;
            if (abs($diff) > 4 or $time > $now + 4)
                echo '<div class="maca alertmaca maincontent" ><div>';
            else {
                echo '<div class="maca maincontent"><div>';
            }
            echo '<p>Маса : ' . $row->MACA . ', (' . (($row->NUMBERCLIENTS > 1) ? $row->NUMBERCLIENTS : "1") . ')<br/>' . fix_str($row->OPER) . '<br/>';
            echo 'Отв. ' . $row->TIMEOPEN . "<br/>";
            echo "</div><div class='monitorartcontent' style='display:none;'>";
            echo "<p class='monitorart '>" . fix_str($row->ARTIKUL) . "<br/><i>" . $row->KOL . " * " . fix_numb($row->SALESPRICE, 2) . " = " . fix_numb($row->KOL * $row->SALESPRICE, 2) . "</i></p>";
            $sum += $row->KOL * $row->SALESPRICE;
        } else {
            echo "<p class='monitorart '>" . fix_str($row->ARTIKUL) . "<br/><i>" . $row->KOL . " * " . fix_numb($row->SALESPRICE, 2) . " = " . fix_numb($row->KOL * $row->SALESPRICE, 2) . "</i></p>";
            $sum += $row->KOL * $row->SALESPRICE;
        }
    }
    echo '</div><span>' . fix_numb($sum, 2) . ' лв.</span></div>';
}
//logva v bazata
//$parola - kriptirana parola za log v mistral
function login($DB, $parola, $usertype)
{
    $encryptedpass = encrypt_mistralpass($parola);
    $sql           = "SELECT name from users where pass = '" . $encryptedpass . "' and typeaccount = ".$usertype;
    
    $sth           = $DB->query($sql);
    $result        = '';
    if ($sth)
        foreach ($sth as $row) {
            $result = $row[0];
        }
    if (strlen($result) > 0)
        return $result;
    else
        return 1; //Ако не намери обект връща обект 1
}
//ТЕГЛИ ОБЕКТИТЕ ОТ БАЗАТА
function get_objects($DB)
{
    $sql = "SELECT ID, NAME from OBEKTI";
    $sth = $DB->query($sql);
    $obj = array();
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $obj[$row->ID] = fix_str($row->NAME);
    }
    return $obj;
}
//NAMIRA DOSTAVKI
//DB obekt za vryzka s DB / from - to dati /
//from row - torow kolko reda da poakzva / 
// hours chasovo otmestvane                            
function get_doct($DB, $from, $to, $fromrow, $torow, $hours, $filter, $check, $objects) //ПРОВЕРЕНА
{
    $counter       = 0;
    $objectfilters = apply_obektifilter($objects, "DOCT.OBEKTID");
    global $inobekti;
   
    $sql           = <<<SQL
 select
DOCTNOMER as nomer,
DATEDOCT,
EDITDATE,
PARTNERNAME,
RAZCR,
RAZCRSUMA,
DTRAZCRBACK as DTBACK,
OBEKTNAME,
(select id from obekti where name like obektname) as obektid,
FROMOBEKTNAME,
sum(kol*edprice) as suma,
DOC2NO as DOCNOMER,
DOC2DATE as DOCDATE
  from "QDOCT"('$from $hours:00:00',
'$to $hours:00:00' $inobekti)
where fromobektname is null $objectfilters
group by
DOCTNOMER, obektid, DATEDOCT, EDITDATE, PARTNERNAME, RAZCR, RAZCRSUMA, DTBACK, OBEKTNAME, FROMOBEKTNAME, DOCNOMER, DOC2DATE
order by nomer desc
   rows $fromrow to $torow
SQL;
    //echo $sql;
    $sth           = $DB->query($sql);
    if ($check == 1) {
        get_columnnames($sth);
    }
    $result = '<ul data-role="listview" data-theme="a" data-filter="true" class="spravka-ul" data-input="#filterBasic-input">';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $counter++;
        $result .= '<li data-theme="a" data-icon="false" class="liresult" data-num="' . $row->NOMER . '" data-obektnum="' . $row->OBEKTID . '" data-spravka2="5">
        <div class="containerspravka"><div class="importantdiv">
        <p><span class="obektvspravka">' . fix_str($row->OBEKTNAME) . '</span><span class="datavspravka">' . fix_date($row->DATEDOCT) . '</span></p>
        <p>Доставка номер : <strong>' . $row->NOMER . '</strong></p>
        <p><span class="partnervspravka">' . fix_str($row->PARTNERNAME) . '</span></p>';
        if ($row->EDITDATE != NULL)
            $result .= '<p style="color:#ED4337">Редактирана на: ' . fix_date($row->EDITDATE) . '</p>';
        $result .= '<p><span class="sumaspravka">Сума : ' . fix_numb($row->SUMA, 2) . ' лв.</span></p></a></div>
        <div class="notimportantdiv">';
        if ($row->RAZCR == 1) {
            $result .= '
               <p>Сума разср: ' . fix_numb($row->RAZCRSUMA, 2) . '</p>
               <p>Връщане на: ' . $row->DTBACK . '</p>';
        } else {
            $result .= '<p>Разсрочено: не</p>';
        }
        $result.= '<p>Док.No: '.$row->DOCNOMER.' / '.$row->DOCDATE.'</p></div></div></li>';
    }
    $result .= '</ul>';
    $DB = NULL; //zatvaria konekciata
    echo $result;
    echo print_footerspravka($counter, $fromrow, $torow);
}
function get_doctsdr($DB, $num, $object) //ПРОВЕРЕНА
{
    $sumdoct = 0;
    $sumprod = 0;
    $counter = 0;
    $sql     = <<<SQL
  SELECT
       OBEKTI.NAME AS OBEKTNAME
     , doctnomer 
     , artikulnames.artikul AS ARTIKUL
     , kol
     , edprice
     , salesprice
     , partno
     FROM doctsdr
     join obekti on doctsdr.OBEKTID = obekti.id
     JOIN ARTIKULNAMES ON doctsdr.ARTIKULID = ARTIKULNAMES.ID
     where doctnomer = $num  and doctsdr.obektid = $object
SQL;
    $sth     = $DB->query($sql);
    $result  = '<ul data-role="listview" data-theme="a" data-filter="true" data-input="#filter-spravka"><input type="search" id="filter-spravka" data-mini="true">';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $counter++;
        $result .= '<li data-theme="a">';
        $result .= '<p><b>' . fix_str($row->ARTIKUL) . '</b></p>';
        $result .= '<p>' . $row->KOL;
        $result .= "  *  " . fix_numb($row->EDPRICE, 2) . "    =  ";
        $result .= fix_numb($row->EDPRICE * $row->KOL, 2) . " лв.";
        if ($row->PARTNO != NULL)
            $result .= "<p><span style='font-style:italic; color:green;';>Партида: " . fix_str($row->PARTNO) . " </span></p> ";
        $result .= '</li>';
        $sumdoct += $row->EDPRICE * $row->KOL;
        $sumprod += $row->SALESPRICE * $row->KOL;
    }
    $result .= "
</ul><ul data-role='listview'>
<li data-theme='e'>
<p><strong>Сума доставни цени: " . fix_numb($sumdoct, 2) . " лв.</strong></p>
<p>Сума продажни цени " . fix_numb($sumprod, 2) . " лв.</p>
<p>Редове в документа: " . $counter . "
</li></ul>";
    $DB = NULL; //zatvaria konekciata
    echo $result;
}
//NAMIRA revizii
//DB obekt za vryzka s DB / from - to dati /
//from row - torow kolko reda da poakzva / 
// hours chasovo otmestvane                            
function get_revizia($DB, $from, $to, $fromrow, $torow, $hours, $filters, $check, $objects)
{
    $counter       = 0;
    $objectfilters = apply_obektifilter($objects, "REVIZIA.OBEKTID");
    $sql           = <<<SQL
SELECT
obekti.name AS OBEKTNAME,
obekti.id AS OBEKTID,
revno,
daterev,
countart,
countcheckart,
(select sum(razlv) from reviziasdr where reviziasdr.revno = revizia.revno and revizia.obektid = reviziasdr.obektid) AS SUMA
from revizia
join obekti on revizia.obektid = obekti.id

where daterev > '$from $hours:00:00' and daterev < '$to $hours:00:00' $objectfilters 
ORDER BY daterev DESC
ROWS $fromrow TO $torow
SQL;
    //echo $sql;
    $sth           = $DB->query($sql);
    if ($check == 1) {
        get_columnnames($sth);
    }
    $result = '<ul data-role="listview" data-theme="a" data-filter="true" class="spravka-ul" data-input="#filterBasic-input">';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $counter++;
        $sum = 0;
        $result .= '<li data-theme="a" data-spravka2="4" data-obektnum="' . $row->OBEKTID . '" data-num="' . $row->REVNO . '">';
        $result .= '<p><span class="obektvspravka">' . fix_str($row->OBEKTNAME) . ' / </span><span class="datavspravka">' . fix_date($row->DATEREV) . '</span></p>';
        $result .= '<span><p>Ревизия N.<strong> ' . $row->REVNO . '</strong></p><p> Проверени: <strong>' . fix_numb($row->COUNTCHECKART, 2) . ' артикула</strong></p></span>';
        if ($row->SUMA < 0)
            $result .= '<p><span class="sumaspravka" style="color:#ED4337">Липси : ' . fix_numb($row->SUMA, 2) . ' лв.</span></p></li>';
        else
            $result .= '<p><span class="sumaspravka">Излишъци : ' . fix_numb($row->SUMA, 2) . ' лв.</span></p></li>';
    }
    $result .= "</ul><br>";
    $DB = NULL; //zatvaria konekciata
    echo $result;
    echo print_footerspravka($counter, $fromrow, $torow);
}
//NAMIRA revizii
//DB obekt za vryzka s DB / from - to dati /
//from row - torow kolko reda da poakzva / 
// hours chasovo otmestvane                            
function get_reviziasdr($DB, $num, $object)
{
    $counter    = 0;
    $total      = 0;
    $totalpr    = 0;
    $sumlipsi   = 0;
    $sumizl     = 0;
    $sumlipsipr = 0;
    $sumizlpr   = 0;
    $sql        = <<<SQL
 SELECT 
  OBEKTI.NAME AS OBEKTNAME
, artikulnames.artikul AS ARTIKUL
, REVIZIASDR.KOMKOL AS KOMKOL
, REVIZIASDR.NAL AS NAL
, REVIZIASDR.RAZKOL AS RAZKOL
, REVIZIASDR.RAZLV AS RAZLV
, REVIZIASDR.DOCTPRICE AS DOCTPRICE
, REVIZIASDR.SALESPRICE AS SALESPRICE
, REVIZIASDR.ARTNOMER AS ARTNOMER
FROM REVIZIASDR
join obekti on REVIZIASDR.OBEKTID = obekti.id
JOIN ARTIKULNAMES ON REVIZIASDR.ARTIKULID = ARTIKULNAMES.ID
where REVIZIASDR.REVNO = $num  and REVIZIASDR.obektid = $object
SQL;
    $sth        = $DB->query($sql);
    $result     = '<ul data-role="listview" data-theme="a" data-filter="true" data-input="#filter-spravka"><input type="search" id="filter-spravka" data-mini="true">';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $counter++;
        $result .= '<li>';
        $result .= '<p><b>' . fix_str($row->ARTIKUL) . ' / Арт.No. ' . $row->ARTNOMER . '</b></p>';
        $result .= '<p>Търсени: ' . $row->KOMKOL . ' / Намерени: ' . $row->NAL . '</p>';
        $result .= '<p>Дост.цена: ' . fix_numb($row->DOCTPRICE, 2) . ' / Пр.цена: ' . fix_numb($row->SALESPRICE, 2) . '</p>';
        if ($row->RAZKOL < 0) {
            $result .= '<p style="color:red"><b>Липси: ' . fix_numb($row->RAZLV, 2) . " лв. / Липси пр.цена: " . fix_numb($row->RAZKOL * $row->SALESPRICE, 2) . "</b></p></li>";
            $total += $row->RAZLV;
            $totalpr += $row->RAZKOL * $row->SALESPRICE;
            $sumlipsi += $row->RAZLV;
            $sumlipsipr += $row->RAZKOL * $row->SALESPRICE;
        }
        if ($row->RAZKOL > 0) {
            $total += $row->RAZLV;
            $totalpr += $row->RAZKOL * $row->SALESPRICE;
            $result .= '<p style="color:green"><b>Излишъци: ' . fix_numb($row->RAZLV, 2) . " лв. / Изл.пр.цена: " . fix_numb($row->RAZKOL * $row->SALESPRICE, 2) . "</b></p></li>";
            $sumizl += $row->RAZLV;
            $sumizlpr += $row->RAZKOL * $row->SALESPRICE;
        }
        if ($row->RAZKOL == 0) {
            $result .= '</li>';
        }
    }
    $DB         = NULL; //zatvaria konekciata
    $total      = fix_numb($total, 2);
    $totalpr    = fix_numb($totalpr, 2);
    $sumlipsi   = fix_numb($sumlipsi, 2);
    $sumizl     = fix_numb($sumizl, 2);
    $sumlipsipr = fix_numb($sumlipsipr, 2);
    $sumizlpr   = fix_numb($sumizlpr, 2);
    if ($total < 0)
        $result .= "</ul>
    <ul data-role='listview' >
    <li><p style='color:red'><strong>Резултат от ревизията: " . $total . " лв.</strong></p><p>Липси: " . $sumlipsi . " лв. /  Излишъци: " . $sumizl . " лв.</p></li>
     <li>
    <p><strong>Резултат по пр.цени: " . $totalpr . " лв.</strong></p><p>Липси: " . $sumlipsipr . " лв. / Излишъци: " . $sumizlpr . " лв.</p></li>   
</ul>";
    if ($total > 0)
        $result .= "</ul>
    <ul data-role='listview' >
    <li data-theme='e'><p style='color:green'><strong>Резултат от ревизията: " . $total . " лв.</strong></p><p>Липси: " . $sumlipsi . " лв. /  Излишъци: " . $sumizl . " лв.</p></li>
      <li data-theme='e'>
    <p><strong>Резултат по пр.цени: " . $totalpr . " лв.</strong></p><p>Липси: " . $sumlipsipr . " лв. / Излишъци: " . $sumizlpr . " лв.</p></li>  
</ul>";
    if ($total == 0)
        $result .= "</ul>
    <ul data-role='listview'>
    <li data-theme='e'>
    <p><strong>Резултат от ревизията: " . $total . " лв.</strong></p><p>Липси: " . $sumlipsi . " лв. / Излишъци: " . $sumizl . " лв.</p></li>
    <li data-theme='e'>
    <p><strong>Резултат по пр.цени: " . $totalpr . " лв.</strong></p><p>Липси: " . $sumlipsipr . " лв. / Излишъци: " . $sumizlpr . " лв.</p></li></ul>";
    echo $result;
}
function search_part($DB, $strings, $key)
{
    $result = '<ul data-role="listview" data-theme="a" data-inset="true">';
    $value  = mb_convert_encoding($strings, "windows-1251", "UTF-8");
    $value = str_replace( array( '\'', '"', ',' , ';', '<', '>' ), '', $value);
    if ($key == "bulstat")
        $sql = "SELECT (select id from partnernames where partnername = name) as nomer2, name, bylstat from partners where bylstat like '%" . $value . "%' and partners.obektid = (select min(id) from obekti) order by name asc ROWS 1 TO 30";
    else
        $sql = "SELECT (select id from partnernames where partnername = name) as nomer2, name, bylstat from partners where upper(name) like '%" . $value . "%' and partners.obektid = (select min(id) from obekti) order by name asc ROWS 1 TO 30";
    $sth = $DB->query($sql);
    if ($sth)
        foreach ($sth as $row) {
            $result .= '<li data-theme="a" class="partners" data-nomer="'.$row[0].'">';
            $result .= '<p class="partname">' . fix_str($row[1]) . '</p>' . '<p>Булстат : ' . fix_str($row[2]) . '</p></li>';
        }
    $DB = NULL;
    $result .= '</ul>';
    return $result;
}
function search_barcode($DB, $barcode)
{   if(strlen($barcode) == 0)
        {
        die();   
        }
    $result = '<ul data-role="listview" data-theme="a" data-inset="true">';
    $sql    = <<<SQL
select
       CKLAD.ARTNOMER
     , CKLAD.SEARCHNAME
     , CKLAD.OBEKTID
     , CKLAD.CKLADID
     , CKLAD.KOL
     , CKLAD.AVGPRICE
     , CKLAD.LASTDOCTPRICE
     , CKLAD.SALESARTNOMER
     , BARCODE.CODE
     FROM CKLAD
JOIN BARCODE ON BARCODE.CKLADARTNOMER = CKLAD.ARTNOMER AND BARCODE.OBEKTID = CKLAD.OBEKTID AND BARCODE.CKLADID = CKLAD.CKLADID
where barcode.code = '$barcode' and cklad.obektid = (select min(id) from obekti) 
SQL;
    $sth    = $DB->query($sql);
    //echo $sql;
    if ($sth) {
        while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
            $result .= '<li data-theme="a" class="articul">';
            //$result .= '<input type="hidden" id="ckladid" val="'.$.'">';
            $result .= '<p><span class="articulname">' . $row->ARTNOMER . '. ' . fix_str($row->SEARCHNAME) . '</span></p>';
            $result .= '<input type="hidden" id="artikul-obektid" value="' . fix_str($row->OBEKTID) . '">';
            $result .= '<input type="hidden" id="artikul-ckladid" value="' . fix_str($row->CKLADID) . '">';
            $result .= '<input type="hidden" id="artikul-artnomer" value="' . $row->ARTNOMER . '">
                        <input type="hidden" id="artikul-name" value="' . fix_str($row->SEARCHNAME) . '">
                        </li>';
        }
    }
    $DB = NULL;
    $result .= '</ul>';
    return $result;
}
/*Търсене на артикули в базата
ПАраметри DB - обект за конекцията
strings - масив с стойностите за търсене__halt_compiler
key - по какво да търси номер или артикулно име
is_full - ако е нула търси по начало на името %name
ако е 1 търси по %name, %name% и name%
*/
function search_art($DB, $strings, $key, $is_full)
{   
    
    
   
    $strings[0] = str_replace( array( '\'', '"', ',' , ';', '<', '>' ), '', $strings[0]);
    //Маха специални символи
    $result = '<ul data-role="listview" data-theme="a" data-inset="true">';
    if ($key == "art" and $is_full == 1) {
        $sql = "SELECT
        cklad.artnomer,
        artikul,
        kol,
        avgprice,
        lastdoctprice,
        searchname, 
        lasteditdate,
        obekti.name,
        nameckladove.ckladname,
        ckladid,
        cklad.obektid
        from cklad
        join obekti on cklad.obektid = obekti.id
        join nameckladove on cklad.ckladid = nameckladove.id and cklad.obektid = nameckladove.obektid
        where";
        foreach ($strings as $value) {
            $value = str_replace( array( '\'', '"', ',' , ';', '<', '>' ), '', $value);
            $value = mb_convert_encoding($value, "windows-1251", "UTF-8");
            $sql .= " (searchname like '%" . $value . "%') and";
        }
        $sql .= " cklad.obektid = (select min(id) from obekti)
        ORDER BY searchname DESC
        ROWS 1 TO 50";
        //echo $sql;
    } else if ($key == "art" and $is_full == 0) {
        $sql = "SELECT
        cklad.artnomer,
        artikul,
        kol,
        avgprice,
        lastdoctprice,
        searchname, 
        lasteditdate,
        obekti.name, 
        nameckladove.ckladname,
        ckladid,
        cklad.obektid
        from cklad
        join obekti on cklad.obektid = obekti.id
        join nameckladove on cklad.ckladid = nameckladove.id and cklad.obektid = nameckladove.obektid
        where searchname like '" . mb_convert_encoding($strings[0], "windows-1251", "UTF-8") . "%' and cklad.obektid = (select min(id) from obekti)
        ORDER BY searchname DESC
        ROWS 1 TO 50";
        //echo $sql;
    } else if ($key == "num" and is_numeric($strings[0])) {
        $sql = "SELECT
        cklad.artnomer,
        artikul,
        kol,
        avgprice,
        lastdoctprice,
        searchname, 
        lasteditdate,
        obekti.name,
        nameckladove.ckladname,
        ckladid,
        cklad.obektid
        from cklad
        join obekti on cklad.obektid = obekti.id
        join nameckladove on cklad.ckladid = nameckladove.id and cklad.obektid = nameckladove.obektid
        
        where cklad.artnomer = " . $strings[0] . " and cklad.obektid = (select min(id) from obekti) order by cklad.kol desc ROWS 1 TO 50";
        //echo $sql;
    } else {
        return "Няма съвпадения";
    }
    //echo $strings[0];
    //echo $sql;
    $sth = $DB->query($sql);
    //echo $sql;
    //var_dump($sth);
    if ($sth) {
        foreach ($sth as $row) {
            $result .= '<li data-theme="a" class="articul">';
            //$result .= '<input type="hidden" id="ckladid" val="'.$.'">';
            $result .= '<p><span class="articulname">' . $row[0] . '. ' . mb_convert_encoding($row[5], "UTF-8", "windows-1251") . '</span></p>';
            $result .= '<input type="hidden" id="artikul-obektid" value="' . $row[10] . '">';
            $result .= '<input type="hidden" id="artikul-ckladid" value="' . $row[9] . '">';
            $result .= '<input type="hidden" id="artikul-artnomer" value="' . $row[0] . '">
                            <input type="hidden" id="artikul-name" value="' . mb_convert_encoding($row[5], "UTF-8", "windows-1251") . '">
                        </li>';
        }
    }
    $DB = NULL;
    $result .= '</ul>';
    //zatvaria konekciata
    //echo $result;
    return $result;
}
function get_user_activity($DB, $from, $to, $hours)
{
    $sql    = <<<SQL
select 
username,
func,
dtlog
from userlastlog
where dtlog > '$from $hours:00:00' and dtlog < '$to $hours:00:00'
group by username, func, dtlog            
order by dtlog desc
SQL;
    $sth    = $DB->query($sql);
    $result = '<ul data-role="listview" data-theme="a" data-filter="true"  data-input="#filterBasic-input">';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $result .= '
    <li>
          <p>' . fix_str($row->USERNAME) . ' : ' . $row->DTLOG . '</p>
              <p>' . fix_str($row->FUNC) . '</p>
         
        </li>';
    }
    $result .= "</ul>";
    $DB = NULL;
    echo $result;
}
function get_finishedkol($DB, $from, $to, $fromrow, $torow, $hours, $filters, $check)
{
    $sql     = <<<SQL
SELECT
ARTIKULNAMES.ARTIKUL as ARTIKUL,
oldartnomer,
DATELOG ,
OLDKOL,
NEWKOL ,
ACTIONSTYPEID ,
OPERID,
OLDOBEKTNAMEID,
OLDCKLADNAMEID,
OBEKTINAMES.OBEKT as OBEKTNAME,
ckladnames.ckladname as CKLADNAME,
(SELECT lastdeliverydate FROM CKLAD WHERE CKLAD.OBEKTID =
    (select id from obekti where obekti.name =
        (select obekt from obektinames where obektinames.id = LOGCKLAD.OLDOBEKTNAMEID ) )
    AND CKLAD.CKLADID = (select id from nameckladove where nameckladove.ckladname =
        (select ckladnames.ckladname from ckladnames where ckladnames.id = LOGCKLAD.OLDCKLADNAMEID)
        and nameckladove.obektid = (select id from obekti where obekti.name = (select obekt from obektinames where obektinames.id = LOGCKLAD.OLDOBEKTNAMEID ))) AND CKLAD.artnomer = logcklad.oldartnomer) as DELIVERYDATE
FROM LOGCKLAD
join ARTIKULNAMES ON LOGCKLAD.OLDARTIKULID = ARTIKULNAMES.ID
JOIN OBEKTINAMES ON LOGCKLAD.OLDOBEKTNAMEID = OBEKTINAMES.ID
join ckladnames on ckladnames.id = LOGCKLAD.OLDCKLADNAMEID
WHERE OLDKOL > 0 and NEWKOL <= 0 AND ACTIONSTYPEID = 1
AND DATELOG > '$from $hours:00:00' AND DATELOG < '$to $hours:00:00'
and
datelog - (SELECT lastdeliverydate FROM CKLAD WHERE CKLAD.OBEKTID =
    (select id from obekti where obekti.name =
        (select obekt from obektinames where obektinames.id = LOGCKLAD.OLDOBEKTNAMEID ) )
    AND CKLAD.CKLADID = (select id from nameckladove where nameckladove.ckladname =
        (select ckladnames.ckladname from ckladnames where ckladnames.id = LOGCKLAD.OLDCKLADNAMEID)
        and nameckladove.obektid = (select id from obekti where obekti.name = (select obekt from obektinames where obektinames.id = LOGCKLAD.OLDOBEKTNAMEID ))) AND CKLAD.artnomer = logcklad.oldartnomer) > 0
order by logcklad.id
desc
ROWS $fromrow TO $torow
SQL;
    $counter = 0;
    //da se dobavi (select kol from cklad where logcklad.oldartnomer = cklad.artnomer and cklad.obektid = 1),
    //ECHO $sql;
    $sth     = $DB->query($sql);
    $result  = '<ul data-role="listview" data-theme="a" data-filter="true"  data-input="#filterBasic-input">';
    if ($sth)
       while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
            $counter++;
            
                $result .= "<li><p>
                <strong><span>". fix_str($row->ARTIKUL) ."</span></strong> 
                </p><p>".fix_str($row->OBEKTNAME)." / ".fix_str($row->CKLADNAME)."</p>";
                $result .= "<p>Старо кол.: <strong>" . $row->OLDKOL . "</strong> / Ново кол.: <strong>" . $row->NEWKOL . "</strong></p> ";
                $result .= "<p>Дата събитие: " .fix_date($row->DATELOG) . "</p><p>Посл.доставка: " . fix_date($row->DELIVERYDATE) . "</p></li>";
            
        }
    $result .= "</ul>";
    $DB = NULL;
    echo $result;
    echo print_footerspravka($counter, $fromrow, $torow);
}
function get_outprod($DB, $from, $to, $fromrow, $torow, $hours, $filter, $check, $objects)
{
    $objectfilters = apply_obektifilter($objects, "OUTPROD.OBEKTID");
    $counter       = 0;
    $sql           = <<<SQL
SELECT
outprod.nomer as NOMER,
DTOUTPROD,
EDITDATE,
obekti.name AS OBEKTNAME,
obekti.id AS OBEKTID,
partnernames.partnername AS PARTNERNAME,
OUTPROD.ZAOBEKT AS ZAOBEKTNAME,
(select sum(kol*edprice) from outprodsdr where outprodsdr.nomer = outprod.nomer and outprod.obektid = outprodsdr.obektid) AS SUMA
from OUTPROD
join obekti on OUTPROD.obektid = obekti.id
join partnernames on outprod.partnernameid = partnernames.id
where OUTPROD.DTOUTPROD > '$from $hours:00:00'
and OUTPROD.DTOUTPROD < '$to $hours:00:00'
and OUTPROD.ZAOBEKT IS NULL $objectfilters
ORDER BY OUTPROD.dtoutprod DESC
ROWS $fromrow TO $torow
SQL;
    $sth           = $DB->query($sql);
    if ($check == 1) {
        get_columnnames($sth);
    }
    $result = '<ul data-role="listview" data-theme="a" data-filter="true" data-input="#filterBasic-input" class="spravka-ul">';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $counter++;
        $result .= '<li data-theme="a" data-spravka2="6" data-obektnum="' . $row->OBEKTID . '" data-num="' . $row->NOMER . '">';
        $result .= '<p><span class="obektvspravka">' . fix_str($row->OBEKTNAME) . "</span><span class=\"datavspravka\">" . fix_date($row->DTOUTPROD) . "</span><p>";
        if ($row->PARTNERNAME != NULL)
            $result .= '<p><span class="partnervspravka">' . fix_str($row->PARTNERNAME) . '</span></p>';
        $result .= '<p> Изписване N. ' . $row->NOMER . '</p>';
        if ($row->EDITDATE != NULL)
            $result .= '<p style="color:#ED4337">Редактирана на: ' . fix_date($row->EDITDATE) . '</p>';
        $result .= '<p><span class="sumaspravka">Сума с ДДС : ' . fix_numb($row->SUMA, 2) . ' лв.</span></p></a></li>';
    }
    $result .= '</ul>';
    $DB = NULL; //zatvaria konekciata
    echo $result;
    echo print_footerspravka($counter, $fromrow, $torow);
}
function get_transfers($DB, $from, $to, $fromrow, $torow, $hours, $filter, $check, $objects)
{
    $counter       = 0;
    $objectfilters = apply_obektifilter($objects, "OUTPROD.OBEKTID");
    $sql           = <<<SQL
          SELECT
            outprod.nomer as NOMER,
            DTOUTPROD,
            EDITDATE,
            obekti.name AS OBEKTNAME,
            obekti.id AS OBEKTID,
            partnernames.partnername AS PARTNERNAME,
            OUTPROD.ZAOBEKT AS ZAOBEKTNAME,
            (select sum(kol*edprice) from outprodsdr where outprodsdr.nomer = outprod.nomer and outprod.obektid = outprodsdr.obektid) AS SUMA
            from OUTPROD
            join obekti on OUTPROD.obektid = obekti.id
            join partnernames on outprod.partnernameid = partnernames.id
            where OUTPROD.DTOUTPROD > '$from $hours:00:00'
            and OUTPROD.DTOUTPROD < '$to $hours:00:00'
            and OUTPROD.ZAOBEKT IS NOT NULL $objectfilters
            ORDER BY OUTPROD.dtoutprod DESC
            ROWS $fromrow TO $torow
SQL;
    $sth           = $DB->query($sql);
    $result        = '<ul data-role="listview" data-theme="a" data-filter="true" class="spravka-ul" data-input="#filterBasic-input">';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $counter++;
        $result .= '<li data-theme="a" data-spravka2="6" data-obektnum="' . $row->OBEKTID . '" data-num="' . $row->NOMER . '">';
        $result .= '<p><span class="obektvspravka">От ' . fix_str($row->OBEKTNAME) . "</span><span class=\"datavspravka\">" . fix_date($row->DTOUTPROD) . "</span></p>
                     <p> <span class=\"obektvspravka\">За " . fix_str($row->ZAOBEKTNAME) . "</span></p>";
        if ($row->PARTNERNAME != NULL)
            $result .= '<p><span class="partnervspravka">' . fix_str($row->PARTNERNAME) . '</span></p>';
        $result .= '<p>Изписване N. ' . $row->NOMER . '</p>';
        if ($row->EDITDATE != NULL)
            $result .= '<p style="color:#ED4337">Редактирана на: ' . $row->EDITDATE . '</p>';
        $result .= '<p><span class="sumaspravka">Сума с ДДС : ' . fix_numb($row->SUMA, 2) . ' лв.</span></p></a></li>';
    }
    $result .= '</ul>';
    $DB = NULL; //zatvaria konekciata
    echo $result;
    echo print_footerspravka($counter, $fromrow, $torow);
}
function get_outprodsdr($DB, $num, $object)
{
    $sumdoct = 0;
    $sumprod = 0;
    $counter = 0;
    $sql     = <<<SQL
 SELECT
       OBEKTI.NAME AS OBEKTNAME
     , OUTPRODSDR.NOMER AS NOMER
     , artikulnames.artikul AS ARTIKUL
     , kol
     , edprice
     , salesprice
     , partno
     FROM OUTPRODSDR
     join obekti on OUTPRODSDR.OBEKTID = obekti.id
     JOIN ARTIKULNAMES ON OUTPRODSDR.ARTIKULID = ARTIKULNAMES.ID
     where OUTPRODSDR.NOMER = $num  and OUTPRODSDR.obektid = $object
SQL;
    $sth     = $DB->query($sql);
    $result  = '<ul data-role="listview" data-theme="a" data-filter="true" class="spravka-ul" data-input="#filter-spravka"><input type="search" id="filter-spravka" data-mini="true">';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $counter++;
        $result .= '<li>';
        $result .= '<p><b>' . fix_str($row->ARTIKUL) . '</b></p>';
        $result .= '<p>' . $row->KOL;
        $result .= "  *  " . fix_numb($row->EDPRICE, 2) . "    =  ";
        $result .= fix_numb($row->EDPRICE * $row->KOL, 2) . " лв.";
        if ($row->PARTNO != NULL)
            $result .= "<p><span style='font-style:italic; color:green;';>Партида: " . fix_str($row->PARTNO) . " </span></p> ";
        $result .= '</li>';
        $sumdoct += $row->EDPRICE * $row->KOL;
        $sumprod += $row->PARTNO * $row->KOL;
    }
    $result .= "
        </ul><ul data-role='listview'>
        <li>
        <p><strong>Сума доставни цени: " . fix_numb($sumdoct, 2) . " лв.</strong></p>
        <p>Сума продажни цени " . fix_numb($sumprod, 2) . " лв.</p>
        <p>Редове в документа: " . $counter . "
        </li></ul>";
    $DB = NULL; //zatvaria konekciata
    echo $result;
}
function get_minkol($DB, $filter, $check, $objects)
{
    $objectfilters = apply_obektifilter($objects, "CKLAD.OBEKTID");
    $sql           = <<<SQL
select
obekti.name,
nameckladove.ckladname,
kol,
minkol,
artikul,
lasteditdate,
artnomer
from cklad
join obekti on cklad.obektid = obekti.id
join nameckladove on cklad.ckladid = nameckladove.id and cklad.obektid = nameckladove.obektid
where kol < minkol $objectfilters 
order by lasteditdate desc
SQL;
    //echo $sql;     
    $sth           = $DB->query($sql);
    $result        = '<ul data-role="listview" data-theme="a" data-filter="true" class="spravka-ul" data-input="#filterBasic-input">';
    if ($sth)
        foreach ($sth as $row) {
            $result .= "<li><p>
                <strong><span>" . mb_convert_encoding($row[1], "UTF-8", "windows-1251") . "</span></strong> / 
                <strong><span style='color:green'>" . mb_convert_encoding($row[0], "UTF-8", "windows-1251") . "</span></strong>  / Арт." . $row[6] . "
                </p><p><strong>" . mb_convert_encoding($row[4], "UTF-8", "windows-1251") . "</strong></p>";
            $result .= "<p>Наличност: <strong>" . $row[2] . "</strong> / Мин.кол.: <strong>" . $row[3] . "</strong></p> ";
            $result .= "<p>Последно използван: " . $row[5] . " </p></li>";
        }
    $result .= "</ul>";
    $DB = NULL;
    echo $result;
}
function get_grouprodukti($DB, $ot, $do, $from, $to, $hours, $filters, $check, $objects)
{
    $sumdoct       = 0;
    $counter       = 0;
    global $inobekti;
$sql           = <<<SQL
select obektname, cklad, artnomer, artikul, sum(kol) AS KOL, avg(avgprice) AS AVGPRICE, sum(kol*avgprice) AS SUMA 
from "QBYPROD"('$ot $hours:00:00', '$do $hours:00:00' $inobekti) 
group by obektname, cklad, artnomer, artikul
order by obektname, cklad, artnomer, artikul
SQL;
    //echo $sql;
    $sth    = $DB->query($sql);
    $result        = '<table data-role="table" data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a" data-filter="true" data-input="#filterBasic-input">
                <thead>
                <tr class="ui-bar-d">
                  <th data-priority="4">Обект</th>                    
                  <th data-priority="6">Склад</th>
                  <th data-priority="6">No.</th>
                  <th>Артикул</th>
                  <th>Кол.</th>
                  <th>Дост.цена</th>
                  <th>Сума</th>
                </tr>
                </thead>
                <tbody>';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
            $counter++;
            $result .= '
                <tr>
                <td>' . fix_str($row->OBEKTNAME) . '</td>
                <td>' . fix_str($row->CKLAD) . '</td>
                <td>' . $row->ARTNOMER.'</td>
                <td>' . fix_str($row->ARTIKUL) . '</td>
                <td>' . fix_numb($row->KOL, 3) . '</td>
                <td>' . fix_numb($row->AVGPRICE, 2) . '</td>
                <td>' . fix_numb($row->SUMA, 2) . '</td> 
                </tr>';  
            $sumdoct += $row->AVGPRICE;
        }
    $result .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td>' . fix_numb($sumdoct, 2) . '<td></tr></tbody></table>';
    $DB = NULL;
    echo $result;
}
function get_klientbalans($DB, $from, $to, $klientid)
{
    $counter = 0;
    $pay     = 0;
    $sale    = 0;
    $doct = 0;
    $theme = 'a';
$sql     = <<<SQL
SELECT
prdate as DATE2,
obektinames.obekt as OBEKT,
partnernames.partnername as PARTNER,
(select sum(kol*salesprice) from smetkisdr where smetkisdr.nomer = smetki.nomer and smetkisdr.obektid = smetki.obektid) as SUMA,
nomer as NOMER,
docno as DOKUMENT,
'1' as STATUS,
'Продажби' as STATUSNAME
from smetki
join obektinames on smetki.obektnameid = obektinames.id
join partnernames on smetki.partnernameid = partnernames.id 
where  partnernameid = $klientid and smetki.razcr = 1
and smetki.prdate > '$from 00:00:00'  and smetki.prdate < '$to 00:00:00'
union
select
dtdate as DATE2,
obektinames.obekt as OBEKT,
partnernames.partnername as PARTNER,
suma as SUMA,
smetkano as NOMER,
moneyoper.docno as DOKUMENT,
'2' as STATUS,
'Плащане' as STATUSNAME            
from moneyoper
join obektinames on moneyoper.obektnameid = obektinames.id
join partnernames on moneyoper.frompartnernameid = partnernames.id
join smetki on moneyoper.smetkano = smetki.nomer and moneyoper.obektnameid = smetki.obektnameid
where frompartnernameid = $klientid and moneyoper.dtdate > '$from 00:00:00' and moneyoper.dtdate < '$to 00:00:00'
union 
SELECT
datedoct as DATE2,
obektinames.obekt as OBEKT,
partnernames.partnername as PARTNER,
(select sum(kol*edprice) from doctsdr where doctsdr.doctnomer = doct.doctnomer and doctsdr.obektid = doct.obektid) as SUMA,
doctnomer as NOMER,
doctnomer as DOKUMENT,
'3' as STATUS,
'Доставка' as STATUSNAME
from doct
join obektinames on doct.obektnameid = obektinames.id
join partnernames on doct.partnernameid = partnernames.id 
where  partnernameid = $klientid and doct.razcr = 1
and doct.datedoct > '$from 00:00:00'  and doct.datedoct < '$to 00:00:00'
SQL;
//echo $sql;
    $sth     = $DB->query($sql);
    $result  = '<ul data-role="listview" data-theme="a" data-filter="true"  data-input="#filterBasic-input">';
    if ($sth)
        while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        
            if ($row->STATUS == "1") {
                $theme = "a";
                $pay += $row->SUMA;
            }
            if ($row->STATUS == "2") {
                $theme = "b";
                $sale += $row->SUMA;
            }
            if ($row->STATUS == "3") {
                $theme = "b";
                $doct += $row->SUMA;
            }
            $counter++;
            $result .= '<li data-theme="' . $theme . '"><p>Дата: ' . fix_date($row->DATE2) . ' /<strong> ' . fix_str($row->OBEKT) . '</strong></p>';
            $result .= '<p><strong><span>' . fix_str($row->PARTNER) . '</span></strong></p>';
            $result .= '<p><b>' . $row->STATUSNAME . ': ' . fix_numb($row->SUMA,2) . ' лв.</b>, См.N. ' . $row->NOMER . ', Док.No. ' . $row->DOKUMENT . '</p>';
            $result .= '</li>';
        }
    $result .= "
        </ul>
                <ul data-role='listview'>
        <li data-theme='b'>
        <p><strong>Сума продажби: " . sprintf("%01.2f", $sale) . " лв.</strong></p>
        <p><strong>Сума плащания: " . sprintf("%01.2f", $pay) . " лв.</strong></p>
        <p><strong>Сума доставки: " . sprintf("%01.2f", $doct) . " лв.</strong></p>
<p>Редове в документа: " . $counter . "
        </li></ul>";
    $DB = NULL; //zatvaria konekciata
    echo $result;
}

function get_partnerinfo($DB, $klientid)
{
   

    echo "Скоро!";
}
function get_groupartikuli($DB, $ot, $do, $fromrow, $torow, $hours, $filters, $check, $objects) //НАПРАВЕНА СЪС СТОРНАТА ПРОЦЕДУРА //ПРОВЕРЕНА
{
    $sumprod       = 0;
    $sumdoct       = 0;
    $counter       = 0;
    $objectfilters = apply_obektifilter($objects, "BYPROD.OBEKTID");
	global $inobekti;
    $sql           = <<<SQL
     select
       
       ARTNOMER
     , ARTIKUL
	 , GRYPA
     , SUM(KOL) AS KOL
     , SUM(KOL*SALESPRICE) AS SUMPROD
     , SUM(KOL*DOCTPRICE) AS SUMA
     , AVG(SALESPRICE) AS SALESPRICE
     FROM QSMETKIGROUP('$ot $hours:00:00', '$do $hours:00:00' $inobekti) 
     GROUP BY ARTNOMER, ARTIKUL, GRYPA
     ORDER BY GRYPA
     
SQL;
    $sth           = $DB->query($sql);
	//$result = print_interval($ot, $do, $hours);
    $result        = '
	<p class="ui-mini">'.$ot.' '. $hours.':00:00 - '.$do.' '.$hours.':00:00</p>
	<table data-role="table" data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a" data-filter="true" data-input="#filterBasic-input">
                <thead>
                <tr class="ui-bar-d">
                  <th data-priority="6">No.</th>                    
                  <th>Артикул</th>
                  <th>Кол.</th>
                  <th data-priority="6">Пр.цена</th>
                  <th>Сума пр.</th>
                  <th data-priority="2">Сума дост.</th> 
<th data-priority="3">Group</th>				  
                </tr>
			
                </thead>
                <tbody>';
				
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $counter++;
        $sumprod += $row->SUMPROD;
        $sumdoct += $row->SUMA;
        $result .= '
                <tr>
                
                <td>' . $row->ARTNOMER . '</td>
                <td>' . fix_str($row->ARTIKUL) . '</td>
                <td>' . fix_numb($row->KOL, 2) . '</td>
                <td>' . fix_numb($row->SALESPRICE, 2) . '</td>
                <td>' . fix_numb($row->SUMPROD, 2) . '</td>
                <td>' . fix_numb($row->SUMA, 2) . '</td> 
<td>' . fix_str($row->GRYPA) . '</td>				
                </tr>';
    }
    $result .= '<tr><td></td><td></td><td></td><td></td><td>' . fix_numb($sumprod, 2) . '<td>' . fix_numb($sumdoct, 2) . '</td></tr></tbody></table>';
    $DB = NULL;
    echo $result;
}
function get_revaluation($DB, $ot, $do, $fromrow, $torow, $hours, $filter, $check)
{	global $inobekti;
    $sql     = <<<SQL
     select
       OBEKTNAME
     , TSDATETIME
     , ARTNOMER
     , ARTIKUL
     , OLDSALESPRICE
     , NEWSALESPRICE
     , NAL
     , OPER 
     FROM QREVALUATION('$ot $hours:00:00', '$do $hours:00:00' $inobekti )
     ORDER BY TSDATETIME DESC
     
SQL;
    $sth     = $DB->query($sql);
    $counter = 0;
    $result  = '<table data-role="table" data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a" data-filter="true" data-input="#filterBasic-input">
                <thead>
                <tr class="ui-bar-d">
                  <th data-priority="3">Обект</th>
                  <th>Дата</th>                    
                  <th data-priority="3">Арт.номер</th>
                  <th>Арт</th>
                  <th>Стара цена</th>
                  <th>Нова цена</th>
                  <th data-priority="4">Нал.</th>
                  <th data-priority="2">Оператор</th>   
                </tr>
                </thead>
                <tbody>';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $counter++;
        $result .= '
                <tr>
                <td>' . fix_str($row->OBEKTNAME) . '</td>
                <td>' . fix_date($row->TSDATETIME) . '</td>    
                <td>' . $row->ARTNOMER . '</td>
                <td>' . fix_str($row->ARTIKUL) . '</td>
                <td>' . fix_numb($row->OLDSALESPRICE, 2) . '</td>
                <td>' . fix_numb($row->NEWSALESPRICE, 2) . '</td>
                <td>' . fix_numb($row->NAL, 4) . '</td>
                <td>' . fix_str($row->OPER) . '</td>   
                </tr>';
    }
    $result .= '</tbody></table>';
    $DB = NULL;
    echo $result;
   
}
function get_oborbyoper($DB, $ot, $do, $hours, $filter, $check)
{	
	global $inobekti;
    $sql          = <<<SQL
   select *
     FROM QOBORBYOPER('$ot $hours:00:00', '$do $hours:00:00' $inobekti ) where OBOR > 0 
     order by OBEKTNAME, OPER 
     
SQL;
    $sth          = $DB->query($sql);
    $sumobor      = 0;
    $sumteksmetki = 0;
    $result       = '<table data-role="table" data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a" data-filter="true" data-input="#filterBasic-input">
                <thead>
                <tr class="ui-bar-d">
                  <th>Опер.</th>
                  <th>Оборот</th>                    
                  <th data-priority="3">Отст.</th>
                  <th>Отв.сметки</th>
                  <th data-priority="4">Обект</th>
                  <th data-priority="1">Сторно</th>
                  </tr>
                </thead>
                <tbody>';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $result .= '
                <tr>
                <td>' . fix_str($row->OPER) . '</td>
                <td>' . fix_numb($row->OBOR, 2) . '</td>    
                <td>' . fix_numb($row->DISCOUNT, 2) . '</td>
                <td>' . fix_numb($row->SUMTEKSMETKI, 2) . '</td>
                <td>' . fix_str($row->OBEKTNAME) . '</td>';
        if (property_exists($row, 'SUMCLEARROWS'))
            $result .= '<td data-priority="4">' . fix_str($row->SUMCLEARROWS) . '</td>';
        else
            $result .= '<td data-priority="4">Вер.</td>';
        '</tr>';
        $sumobor += $row->OBOR;
        $sumteksmetki += $row->SUMTEKSMETKI;
    }
    $result .= '<tr>
                <td></td>
                <td>' . fix_numb($sumobor, 2) . '</td>    
                <td></td>
                <td>' . fix_numb($sumteksmetki, 2) . '</td>
                <td></td>
                <td></td>
                </tr>';
    $result .= '</tbody></table>';
    $DB = NULL;
    echo $result;
}
function get_parpotoci($DB, $ot, $do, $hours, $filter, $check)
{	global $inobekti;
    $sql    = <<<SQL
     select
       OBEKTNAME
     , NAME
     , SUMA  
     FROM QSUMPROFIT('$ot $hours:00:00', '$do $hours:00:00' $inobekti )
     
     
SQL;
    $sth    = $DB->query($sql);
    $result = '<table data-role="table" data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a" data-filter="true" data-input="#filterBasic-input">
                <thead>
                <tr class="ui-bar-d">
                  <th>Обект</th>
                  <th></th>                    
                  <th>Сума</th>
                  
                   
                </tr>
                </thead>
                <tbody>';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $result .= '
                <tr>
                <td>' . fix_str($row->OBEKTNAME) . '</td>
                <td>' . fix_str($row->NAME) . '</td>
                <td>' . fix_numb($row->SUMA, 2) . '</td>      
                </tr>';
    }
    $result .= '</tbody></table>';
    $DB = NULL;
    echo $result;
}


function get_moneymove($DB, $ot, $do, $hours, $filter, $check)
{   $sales = 'Продажби';
	global $inobekti;
    $sales = mb_convert_encoding($sales, "windows-1251" , "UTF-8");
    $sql    = <<<SQL
    select obektname, dtdate, dthour, moneyinname, suma, actionname, username, note, frompartnername, typeaction, nal
    FROM Qmoneyoper('$ot $hours:00:00', '$do $hours:00:00' $inobekti ) where typeaction != '$sales' order by obektname, moneyinname, dtdate, dthour
SQL;
    $sth    = $DB->query($sql);
    $result = '<table data-role="table" data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a" data-filter="true" data-input="#filterBasic-input">
                <thead>
                <tr class="ui-bar-d">
                  <th data-priority="6">Дата</th>
                  <th>Час</th>

                  <th data-priority="3">Обект</th>
                  <th>Каса</th>
                  <th>Сума</th>
                  <th data-priority="1">Тип</th>
                  <th>Партньор</th>
                  <th data-priority="2">Забележка</th>
                  <th data-priority="4">Наличност</th>
                </tr>
                </thead>
                <tbody>';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $result .= '
                <tr>
                <td>'.$row->DTDATE.'</td>
                <td>' .  $row->DTHOUR.'</td>
                <td>' . fix_str($row->OBEKTNAME) . '</td>
                <td>' . fix_str($row->MONEYINNAME) . '</td>
                <td>' . fix_numb($row->SUMA, 2) . '</td>
                <td>' . fix_str($row->TYPEACTION) . '</td>
                <td>' . fix_str($row->FROMPARTNERNAME) . '</td>
                <td>' . fix_str($row->NOTE) . '</td>
                <td>' . fix_str($row->NAL) . '</td>
                </tr>';
    }
    $result .= '</tbody></table>';
    $DB = NULL;
    echo $result;
}
function get_nalichnost($DB)
{
    $total  = 0;
    $result = "";
	global $inobekti;
	if($inobekti == ', null')
		$inobekti2 = '(null)';
	else
		$inobekti2 = '';
    $sql    = <<<SQL
	
select
obektname,
sum(kol * lastdoctprice) as sumadoct,
sum(kol * avgprice) as sumaavddoct
from qnal$inobekti2
group by obektname
SQL;
    
    $midsth = $DB->query($sql);
    //$result.= '<p><span style="float:left;font-weight: bold;font-size: 12px;color:green;">'.$from.'</span></p><br/>';
    if ($midsth)
        foreach ($midsth as $row1) {
            $result .= '<div class="quickresults">';
            $result .= '<p>' . mb_convert_encoding($row1[0], "UTF-8", "windows-1251") . ' :' . '<span style="float:right"> ' . sprintf("%01.2f", $row1[1]) . ' лв.</span></p></div>';
            $total += $row1[1];
        }
    $result .= '<span class="hf">Общо: ' . sprintf("%01.2f", $total) . ' лв.</span><br/>';
    
	echo $result;
}

function get_razcrobekti($DB)
{
 
    $sql    = "select obekti.name as OBEKTNAME, sum(suma) as SUMA from razcr join obekti on obekti.id = razcr.obektid group by obekti.name";
	$total = 0;
	$result = "";
    $sth    = $DB->query($sql);
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $result .= '<div class="quickresults">';
        $result .= '<p>' . fix_str($row->OBEKTNAME) . ' : <strong><span style="float:right"> ' . fix_numb($row->SUMA, 2) . ' лв.</strong> </span></p></div>';
        $total += $row->SUMA;
    }
    $result .= '<span class="hf">Общо: ' . fix_numb($total, 2) . ' лв.</span><br/>';
    echo $result;
}
function get_nalkaca($DB)
{
    $total  = 0;
    $result = "";
	global $inobekti;
	if(!$inobekti)
    $sql    = "select * from qmoneyinnal where NAL <> 0 order by obektname, name";
	else
	$sql    = "select * from qmoneyinnal(null) where NAL <> 0 order by obektname, name";
    $sth    = $DB->query($sql);
    //$result.= '<p><span style="float:left;font-weight: bold;font-size: 12px;color:green;">'.$from.'</span></p><br/>';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $result .= '<div class="quickresults">';
        $result .= '<p>' . fix_str($row->OBEKTNAME) . ' : ' . fix_str($row->NAME) . '<strong>' . '<span style="float:right"> ' . fix_numb($row->NAL, 2) . ' лв.</strong> </span></p></div>';
        $total += $row->NAL;
    }
    $result .= '<span class="hf">Общо: ' . fix_numb($total, 2) . ' лв.</span><br/>';
    echo $result;
}
function get_pechalbamainscreen($DB, $day, $hours)
{//DA SE DOBAVI promenliwata hours kam from i to
    $total = 0;
	$oldversion = false;//Ako e stara versia
	
	global $inobekti;
    if ($day == 0) {
        $from = date("d.m.Y")." ".$hours.":00:00";
        $to   = date("d.m.Y", time() + 60 * 60 * 24)." ".$hours.":00:00";
    } else {
        $from = date("d.m.Y", time() - 60 * 60 * 24)." ".$hours.":00:00";
        $to   = date("d.m.Y")." ".$hours.":00:00";
    }
    $sumstorno = 0;
    $result    = "";
    $result .= '<span class="hf">От: ' . $from . '.</span><br>';
    $sql = <<<SQL
select
r.*,
(select sum(kol*salesprice) from teksmetki WHERE TEKSMETKI.OBEKTID = (SELECT ID FROM OBEKTI WHERE OBEKTI.NAME LIKE r.OBEKTNAME)) AS TEKSMETKI,
(select sum(kol*CAST(salesprice AS BIGINT)) from "QCLEARROWS"('$from', '$to' $inobekti) where QCLEARROWS.OBEKTNAME = r.OBEKTNAME and kol < 1000) as SUMSTORNO,
0 AS NOMER,
0  AS SUMDISCOUNT
from QPROFITBYPERIOD('$from', '$to' $inobekti) r
ORDER BY SUMASALESPRICE DESC
SQL;
//echo $sql;
    //kol < 1000 e za da ne sumira greshkite s 8 cifreni kolichestva.skaniran barkod v kol....
   //5 часа отместване на диапазона.Да се върже от настройките
    $sth = $DB->query($sql);
	//echo $version;
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
	if (!property_exists($row, 'COUNTSMETKI'))//Ако е със старата версия на базата и липсват полета за брой сметки в процедурата
	{
	$row->COUNTSMETKI = 1;
	$row->COUNTSMETKISDR = 1;
	
	}
        $sql2 = "select max(dtstop) as LOG from logsync where status = 'ok' and obektid = (SELECT ID FROM OBEKTI WHERE OBEKTI.NAME LIKE '".$row->OBEKTNAME."')";
        try
		{
		$log = $DB->query($sql2);
		$row2 = $log->fetch(PDO::FETCH_OBJ);
		$logtime = $row2->LOG;
		} catch(Exception $e)
		{ $logtime = '01.01.1970 00:00:00';//Ако е стара версия и няма такова поле
		$oldversion = true;
		$msgversion = "Стара версия, непълна справка";	
		}
        $pechalba = fix_numb((fix_numb($row->SUMASALESPRICE, 2)*($row->NAD/100))/(($row->NAD/100) + 1), 2);
        $result .= '<div class="quickresults">'
                . '<p>' . fix_str($row->OBEKTNAME) . ' :<strong><span style="float:right"> ' . fix_numb($row->SUMASALESPRICE, 2) . ' лв.</strong></span>'
                . '<p>Ср.сметка <strong>'.fix_numb(fix_numb($row->SUMASALESPRICE, 2)/$row->COUNTSMETKI, 2).' лв.</strong>, Надц.<b> '.$row->NAD.' % </b>'
                . '<p><strong> ' . $row->COUNTSMETKI . ' </strong>сметки,<strong> ' . $row->COUNTSMETKISDR . ' </strong>артикула, Печалба <strong>'.$pechalba.' лв.</strong> </p>';
if($logtime > '01.01.1970 00:00:00')  //Ако е стара версия на базата и лиспва таблица за лог на синхронизациите да не показва              
$result .= 'Синхронизиран на : <i style="color:green ">'.fix_date($logtime).'</i>' ; 
else if($oldversion)
{$result .= '<i style="color:red ">'.$msgversion.'</i>';}             
if($row->SUMSTORNO > 0 || $row->NOMER > 0 || $row->SUMDISCOUNT > 0)
{
$result.= '<div class="alert_mainscreen" style="margin-top:4px;"><p>'; 
if($row->SUMSTORNO > 0)   
$result.='<p>Сторно: ' . fix_numb($row->SUMSTORNO, 2).' лв.</p>';   
if($row->NOMER > 0)
$result.='<p>Редакт сметки: ' . $row->NOMER. ' бр.</p>'; 
if($row->SUMDISCOUNT > 0)
$result.='<p>Отстъпки: '. fix_numb($row->SUMDISCOUNT, 2) .' лв.</p>';
$result.='</p></div>';
}
$result.= "</div>";
        $total += $row->SUMASALESPRICE;
        if ($row->TEKSMETKI > 0 and $day == 0) {
            $result .= '<p style="font-style: italic;">Отворени сметки : ' . fix_numb($row->TEKSMETKI, 2) . ' лв.</p>';
            $total += $row->TEKSMETKI;
        }
    }
    $result .= '<span class="hf">' . sprintf("%01.2f", $total) . ' лв.</span><br>';
    echo $result;
}
//ИЗПЪЛНЯВА SQL ЗАЯВКИ ПО БАЗАТА DB , sqlarray е масив от sql зявки.
function custom_script($DB, $sqlarray)
{
    $result = "";
    $i      = 0;
    while ($i < count($sqlarray) / 2 - 1) {
        $DB->query($sqlarray[$i]);
        $result .= $sqlarray["text" . $i];
        $result .= "<br>";
        $i++;
    }
    $DB = NULL;
    echo $result;
}
function get_nalichnostpoobekti($DB, $number, $object, $cklad)
{
    $sql    = <<<SQL
SELECT
        obekti.name,
        nameckladove.ckladname,
        cklad.artnomer,
        kol,
        avgprice,
		pricelist.price
        from cklad
        join obekti on cklad.obektid = obekti.id
        join nameckladove on cklad.ckladid = nameckladove.id and cklad.obektid = nameckladove.obektid
		join pricelist on cklad.obektid = pricelist.obektid and cklad.artnomer = pricelist.artnomer and pricelistid = 1
        where cklad.ckladid = $cklad and cklad.artnomer = $number
        order by obekti.name asc
SQL;
    //echo $sql;
    $sth    = $DB->query($sql);
    $result = '
<table data-role="table"  data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a">   
        <thead>
                <tr class="ui-bar-d">
                  <th>Обект</th>
                  <th data-priority="5">Склад</th>
                  <th>Наличност</th>
                  <th >Ед.цена</th>
				  <th>Пр.цена</th>
                </tr>
        </thead>
        <tbody>';
    foreach ($sth as $row) {
        $result .= '
                <tr>
                <th>' . mb_convert_encoding($row[0], "UTF-8", "windows-1251") . '</th>
                 <th>' . mb_convert_encoding($row[1], "UTF-8", "windows-1251") . '</th>
                <td>' . sprintf("%01.3f", $row[3]) . '</td>
                <td>' . sprintf("%01.2f", $row[4]) . ' лв.</td>
				<td>' . sprintf("%01.2f", $row[5]) . ' лв.</td>
                </tr>';
    }
    $result .= '</tbody></table>';
    echo $result;
}
function get_artikuli($DB, $fromrow, $torow, $cklad, $check, $objects)
{	
    $counter       = 0;
	global $inobekti;
	if($inobekti == ', null')
		$inobekti2 = '(null)';
	else
		$inobekti2 = '';//МАЗЕН ФИКС НА НОШИТЕ СТОРНАТИ ПРОЦЕДУРИ Ш МИСТРАЛ КОГАТО ПАРАМЕТЪРА Е 1
  
    $sql           = <<<SQL
select
       ARTNOMER
     , ARTIKUL
     , KOL
     , AVGPRICE
     , LASTDOCTPRICE
     , OBEKTNAME
     , CKLADNAME
     , TYPEMEASURE
     , PARTNERNAME
	 from qnal$inobekti2
WHERE KOL > 0
ORDER BY OBEKTNAME, CKLADNAME, ARTIKUL ASC           
SQL;

    //echo $sql;
    $sth           = $DB->query($sql);
    $totaldoct     = 0;
    $rowcount      = 0;
    $result        = '
                <table data-role="table" id="artikuli-table" data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a" data-filter="true" data-input="#filterBasic-input">
                <thead>
                <tr class="ui-bar-d">
                  <th>Име</th>
                  <th data-priority="6">No.</th>                    
                  <th>Нал.</th>
                  <th>Посл.д.цена</th>
                  <th data-priority="6">Ср.цена</th>
                  <th data-priority="6">Код в прод.</th>
                  <th data-priority="5">Обект</th>
                  <th data-priority="5">Склад</th>
                  <th data-priority="6">Мер.ед.</th>
                  <th>Дост.</th>  
                </tr>
                </thead>
                <tbody>';
    while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
        $counter++;
        $result .= '
                        <tr>
                        <td>' . fix_str($row->ARTIKUL) . '</td>
                        <td>' . $row->ARTNOMER . '</td>
                        <td>' . sprintf("%01.3f", $row->KOL) . '</td>
                        <td>' . sprintf("%01.2f", $row->AVGPRICE) . ' лв.</td>
                        <td>' . sprintf("%01.2f", $row->LASTDOCTPRICE) . ' лв.</td>
                        <td>' . $row->ARTNOMER . '</td>
                        <td>' . fix_str($row->OBEKTNAME) . '</td>
                        <td>' . fix_str($row->CKLADNAME) . '</td>
                        <td>' . fix_str($row->TYPEMEASURE) . '</td>
                        <td>' . fix_str($row->PARTNERNAME) . '</td> 
                        </tr>';
        $totaldoct += $row->AVGPRICE * $row->KOL;
        $rowcount++;
    }
    $result .= '</tbody></table>';
                
    echo $result;
   
}
function get_dvijenieartikul($DB, $number, $object, $cklad)
{
    $sql    = <<<SQL
SELECT * from qstokiregister('01.11.2013 00:00:00', '01.12.2013 00:00:00',$object, $cklad, $number) 
SQL;
    //echo $sql;
    $sth    = $DB->query($sql);
    $result = '
<table data-role="table"  data-mode="columntoggle" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" data-column-btn-text="Още колонки..." data-column-popup-theme="a">   
        <thead>
                <tr class="ui-bar-d">
                  <th>Обект</th>
                  <th data-priority="5">Склад</th>
                  <th>Наличност</th>
                  <th >Ед.цена</th>
                </tr>
        </thead>
        <tbody>';
    foreach ($sth as $row) {
        $result .= '
                <tr>
                <th>' . mb_convert_encoding($row[0], "UTF-8", "windows-1251") . '</th>
                 <th>' . mb_convert_encoding($row[1], "UTF-8", "windows-1251") . '</th>
                <td>' . sprintf("%01.3f", $row[3]) . '</td>
                <td>' . sprintf("%01.2f", $row[4]) . ' лв.</td>
                </tr>';
    }
    $result .= '</tbody></table>';
    echo $result;
}
function get_artikulinfo($DB, $number, $object, $cklad)
{
    $sql    = " select
       CKLAD.SEARCHNAME
     , OBEKTI.NAME
     , nameckladove.ckladname
     , CKLAD.ARTNOMER
     , CKLAD.KOL
     , typemeasure.name
     , CKLAD.AVGPRICE
     , CKLAD.LASTDOCTPRICE
     , CKLAD.MINKOL
     , CKLAD.CANSALES
     , CKLAD.SALESARTNOMER
     , CKLAD.LASTEDITDATE
     , CKLAD.LASTDELIVERYDATE
     , CKLAD.LASTBRAKDATE
     , CKLAD.LASTSALESDATE
     , CKLAD.LASTOUTPRODDATE
     from cklad
     join typemeasure on cklad.typemeasureid = typemeasure.id and cklad.obektid = typemeasure.obektid
     join obekti on cklad.obektid = obekti.id
     join nameckladove on cklad.ckladid = nameckladove.id and cklad.obektid = nameckladove.obektid
     where cklad.ckladid = " . $cklad . " and cklad.artnomer =" . $number . " and cklad.obektid = (select min(id) from obekti)";
    //echo $sql;
    //echo $sql;
    $sth    = $DB->query($sql);
    $result = '
                    <table data-role="table"  data-mode="reflow" class="ui-body-d ui-shadow table-stripe ui-responsive mytable" data-column-btn-theme="a" >
                    <thead>
                        <tr class="ui-bar-d">
                          <th>Арткикул:</th> 
                          <th>Обект:</th>
                          <th>Склад:</th>
                          <th>Арт.номер:</th>
                          <th>Наличност:</th>
                          <th>Мер.ед:</th>
                          <th>Ср.цена:</th>
                                                  <th>Ед.цена:</th>
                                                  <th>Мин.нал:</th>
                                                  <th>Дир.продажба</th>
                                                  <th>Код в прод:</th>
                                                  <th>Редактиран:</th>
                                                  <th>Доставен:</th>
                                                  <th>Бракуван:</th>
                                                  <th>Продаден:</th>
                                                  <th>Изписан:</th>
                        </tr>
                    </thead>
                    <tbody>';
    //echo $sql;
    if ($sth)
        foreach ($sth as $row) {
            $result .= '
                                        <tr>
                                        <th>' . fix_str($row[0]) . '</th>
                                        <td>' . fix_str($row[1]) . '</td>
                                        <td>' . fix_str($row[2]) . '</td>
                                        <td>' . $row[3] . '</td>
                                        <td>' . fix_numb($row[4], 4) . '</td>
                                        <td>' . fix_str($row[5]) . '</td>
                                        <td>' . fix_numb($row[6], 4) . '</td>
                                        <td>' . fix_numb($row[7], 4) . '</td>
                                                                                <td>' . $row[8] . '</td>
                                                                                <td>' . $row[9] . '</td>
                                                                                <td>' . $row[10] . '</td>
                                                                               
                                                                                <td>' . fix_date($row[11]) . '</td>
                                                                                <td>' . fix_date($row[12]) . '</td>
                                                                                <td>' . fix_date($row[13]) . '</td>
                                                                                <td>' . fix_date($row[14]) . '</td>
                                                                                <td>' . fix_date($row[15]) . '</td>
                                                                                
                                                                                                        
                                        </tr></tbody></table>';
        }
    //var_dump($result);
    echo $result;
}

function renumber_smetki($DB)
{   
    $sql           = <<<SQL
      select * from cleartestsmetki(0);   
SQL;
    $sth = $DB->query($sql);
    $DB = null;//Затваря конекцията
    echo "Базата преномерирана успешно!";
}
?>