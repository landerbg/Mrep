$result .= <<<RESULT
        <li data-theme="a" class="liresult" data-spravka2="0" data-obektnum="$row[12]" data-num="$row[0]"> 
        <div class="containerspravka"><div class="importantdiv" >
                <p>
                <span class="obektvspravka">fix_str($row[11])</span>
                <span class="datavspravka">'.fix_date($row[1])."</span>
                </p>";
                if ($row[5] != NULL) //Ако има клиент
                $result .='<p><span class="partnervspravka">'.fix_str($row[5]).'</span></p>';
                $result .='<p><span>'.fix_str($row[10]).' : '.$row[6].'</span></p>';
                $result .='<p><span class="sumaspravka">Сума : ' . fix_numb($row[13],2) . ' лв.</span></p></div>';
                
                //НАЧАЛО НА НЕЩАТА КОИТО СЕ ВИЖДАТ САМО НА ЛАНДСКЕЙП
                $result .='<div class="notimportant">';
                if ($row[2] != NULL)//АКО ИМА МАСА И ЧАС НА ОТВАРЯНЕ
                {
                if(hoursToSecods(date('H:i:s', strtotime($row[3]))) + 10800 < hoursToSecods(date('H:i:s', strtotime($row[1]))))
                
                        $color = "red";//АКО Е ОТВОРЕНА ПРЕДИ ПОВЕЧЕ ОТ ТРИ ЧАСА ЧЕРВЕНО....
                
                else
                $color = "black";  
                $result .='<p><span class="datavspravka" '.$color.';"><strong>Отворена в: '.fix_date($row[3]).'</strong></span></p>';
                $result .='<p><span >Маса: <strong>'.$row[2].'</strong></span></p>';
                
                }
                
                $result .='<p>Сметка номер : <strong> '.$row[0].'</strong></p>
                    ';
                
                
                if($row[7] === 0)
                $result .='<p>Разсрочено <strong> '.$row[7].'</strong>,  Сума разср. : <strong>'.$row[8].'  </strong>,    Каса : <strong>'.$row[9].'</strong></p>';
                else
                $result .='<p>Платено ,  Каса : <strong>'.$row[9].'</strong></p>';
                if ($row[4] != NULL)
                $result .='<p style="color:#ED4337">Редакция: '.fix_date($row[4]).'</p>';   
               
            
                
                $result .='</div></div></li>';
        }
                $result .=					
                                '</ul>';
                               
                                