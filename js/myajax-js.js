function start_mrep(usertype) {
    check_databasetype();
    var page;
    if(usertype === 1)//Ако е лимитиран потребител праща в страницата за проверка на артикули и забранява навигацията.
    {
        page = "#searchart";
    $('.footerul a').addClass("ui-disabled");
    $('.ui-btn-left').addClass("ui-disabled");
    
    }
    else
        page = "#home";
        
     $(":mobile-pagecontainer").pagecontainer("change", page, {
            transition: "none"
        });
        $(".result").empty();//Чисти ако има заредена справка на екран 1
        $("#todaysales").empty().hide();//Чисти ако има останали резултати в дива с резултат от справка
        } 
     //Инициализация на променливи :
 
 

function check_databasetype() {
    $.ajax({
        type: "POST",
        url: urldata + "scripts/test2.php",
        timeout: 20000,
        data: {
            "spravka2": 112
        },
        success: function(data) {
            $("#select-objects").empty();
            if (data == 1) //Ако е централна базата активира бутона за зареждане на обектите
            
            {
                $("#objects-checker").removeClass("ui-disabled");
                toast("Ползвате база от тип централен склад!");
            }
            else $("#objects-checker").addClass("ui-disabled");
            
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            toast("Няма връзка с база!", {"colour":"#d85c67", "fade_time":"1100"});
        }
    });
}

function ask_registration() {
    var hash = $("#hash").html();
    var user = $("#reg_user").val();
    
    //toast();  
    $.mobile.loading('show', {
        text: 'Изпращане хеш кода...',
        textVisible: true,
        theme: temaloading,
        html: ""
    });
    $.ajax({
        type: "POST",
        url: urldata + "scripts/ask_registration.php",
        data: {
            "hash": hash,
            "user": user
        },
        success: function(data) {
            $.mobile.loading('hide');
            toast("Данните са изпратени успешно!");
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            toast("Няма връзка с база!", {"colour":"#d85c67", "fade_time":"1100"});
            $.mobile.loading('hide');
        }
    });
}

function get_obekti() {
    $.mobile.loading('show', {
        text: 'Извличане на колони...',
        textVisible: true,
        theme: "b",
        html: ""
    });
    $.ajax({
        type: "POST",
        url: urldata + "scripts/test2.php",
        timeout: 20000,
        data: {
            "spravka2": 111
        },
        success: function(data) {
            var obekti = JSON.parse(data);
            var myselect = $("#select-objects");
            var content = "";
            myselect.empty();
            for (index in obekti) {
                value = obekti[index];
                content += '<input type="checkbox" data-mini="true" checked="checked" value="' + index + '" name="checkbox-h-' + index + 'a" id="checkbox-h-' + index + 'a"> <label for="checkbox-h-' + index + 'a">' + obekti[index] + '</label>';
            }
            myselect.html(content);
            $(myselect).trigger("create");
            $.mobile.loading('hide');
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            pipilove
            ("Няма връзка с база!", {"colour":"#d85c67", "fade_time":"1100"});
            $.mobile.loading('hide');
        }
    });
}
//ПОКАЗВА РЕДАКЦИИТЕ НА СМЕТКА

function show_smetkared(num, object) {
    var spravka2 = '1';
    $('.final2').html('<div id ="loadinggifdiv"></div>');
    $.ajax({
        type: "POST",
        url: urldata + "scripts/test2.php",
        data: "number=" + num + "&spravka2=" + spravka2 + "&obektid=" + object,
        success: function(data) {
            $('.final2').html(data);
            $('#data3').trigger('create');
        }
    });
}

function show_partnerbalans(klientid) {
    $('#filterform').val("15");
    var fullDate = new Date();
    var twoDigitMonth = ((fullDate.getMonth().length + 1) === 1) ? (fullDate.getMonth() + 1) : '0' + (fullDate.getMonth() + 1);
    var currentDate = fullDate.getDate() + "." + twoDigitMonth + "." + fullDate.getFullYear();
    $('#otdata').val(currentDate);
    $('#dodata').val(currentDate);
    $show
}
//ФУНКЦИЯ ЗА БЪРЗАТА СПРАВКА НА ПЪРВИЯ ЕКРАН

function show_quick(spravkano) {
    var day = 0;
    if (spravkano == 991) {
        spravkano = 99;
        day = 1;
    }
    if (spravkano == 990) {
        spravkano = 99;
        day = 0;
    }
    var hours = $('#hours').val();
    //ТОВА ДА ГО ПРОВЕРЯ ЗАЩО СЪМ ГО НАПРАВИЛ ТАКА.ИЗГЛЕЖДА СТРАННО, НО ПРОМЕНЛИВАТА DAY МОЖЕ ДА ТРЯБВА ЗА НЕЩО
    $.mobile.loading('show', {
        text: 'Извличат се данни...',
        textVisible: false,
        theme: temaloading,
        html: ""
    });
    $.ajax({
        type: "POST",
        url: urldata + "scripts/test.php",
        timeout: 120000,
        data: {
            "ot": 1,
            "do": 2,
            "fromrow": 1,
            "torow": 1,
            "spravka": spravkano,
            "objects": 1,
            "hours": hours,
            "klientid": 1,
            "day": day,
            "check": 1
        },
        success: function(data) {
            $('#todaysales').html(data).show();
            $.mobile.loading('hide');
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $.mobile.loading('hide');
            $('#todaysales').html(errorThrown + "Изглежда че има проблем с връзката или е надвишен 120 секундния период на конекция.Моля опитайте отново!" + textStatus + XMLHttpRequest.responseText);
        }
    });
}
//ПРЕИЗЧИСЛЯВА СУМАТА В ЛИСТА СЛЕД ФИЛТЪР

function recalcbyprod() {
    var runningTotal = 0;
    $('.sumbyprod:visible').each(function() {
        runningTotal += ($(this).html() * 1);
    });
    $('.totalbyprod').html(runningTotal.toFixed(2));
}

function get_spravka() {
    $('.result').html('<div id ="loadinggifdiv"></div>');
    var data1 = $('#otdata').val();
    var data2 = $('#dodata').val();
    var hours = $('#hours').val();
    var klientid = $('#klientnameid').val();
    //toast(hours);
    var spravka = $('#filterform').val(); //Коя справка да покаже
    var objects = new Array(); //Масив с обекти за изключване от справките
    //Наливат се стойности в масива object ако има обекти които ще се изключват от резултата
    //в зависимост от махнатите чекове
    $("#select-objects input:not(:checked)").each(function() {
        var value = $(this).val();
        objects.push(value);
    });
    var jsonobject = JSON.stringify(objects);
    var fromrow = $('#lastrow').val(); //Взима стойността на последния показан ред
    fromrow = parseInt(fromrow, 10);
    var torow = fromrow + parseInt($('#rows').val(), 10); //Събира послдния показан плюс скрола за редове
    $('#lastrow').val(torow); //Записва новия последен поакaзан ред 
    $.ajax({
        type: "POST",
        url: urldata + "scripts/test.php",
        timeout: 120000,
        data: {
            "ot": data1,
            "do": data2,
            "fromrow": fromrow,
            "torow": torow,
            "spravka": spravka,
            "objects": jsonobject,
            "hours": hours,
            "klientid": klientid,
            "check": 0
        },
        success: function(data) {
            $('.result').html(data);
            $('#data').trigger('create');
            //слага стил на листа който връща скрипта
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('.result').html(errorThrown + "Изглежда че има проблем с връзката или е надвишен 2 минутния период на конекция.Моля опитайте отново!" + textStatus + XMLHttpRequest);
        }
    });
}
//ПОКАЗВА СЪДЪРЖАНИЕТО НА СМЕТКА

function show_record_sdr(num, object, spravka2) {
    $.mobile.loading('show', {
        text: 'Зареждане на данни...',
        textVisible: false,
        theme: temaloading,
        html: ""
    });
    $.ajax({
        type: "POST",
        url: urldata + "scripts/test2.php",
        timeout: 20000,
        data: {
            "number": num,
            "spravka2": spravka2,
            "obektid": object
        },
        success: function(data) {
            $.mobile.loading('hide');
            $('#spravkaresult').find('#dialog-header').html("Резултат");
            $('#myMessage').html(data);
            $("#lnkDialog").click(); //ОТВАРЯ ДИАЛОГА С ИЗКУСТВЕНО НАСТИКСАНЕ НА ЛИНК
            $('#myMessage').trigger('create'); //СЛАГА СТИЛ НА ДИАЛОГА В КОЙТО СА НАЛЯТИ ДАННИ С AJAX
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $.mobile.loading('hide');
            toast("Няма връзка с база!", {"colour":"#d85c67", "fade_time":"1100"});
        }
    });
}

function get_columnnames() {
    var data1 = '01.01.1980';
    var data2 = '01.01.2999';
    var hours = $('#hours').val();
    var klientid = $('#klientnameid').val();
    var spravka = $('#filterform').val();
    //toast(hours); 
    var objects = 1; //ДА се развие по нататък - ако базата е с повече обекти
    var fromrow = 1;
    var torow = 1;
    $.mobile.loading('show', {
        text: 'Извличане на колони...',
        textVisible: true,
        theme: "b",
        html: ""
    });
    $.ajax({
        type: "POST",
        url: urldata + "scripts/test.php",
        data: {
            "ot": data1,
            "do": data2,
            "fromrow": fromrow,
            "torow": torow,
            "spravka": spravka,
            "objects": -1,
            "hours": hours,
            "klientid": klientid,
            "check": 1
        },
        success: function(data) {
            $('#filtercolumns').html(data);
            $('#filtercolumns').trigger('create');
            $.mobile.loading('hide');
        }
    });
}
//РЕАЛИЗИРА ЛОГ ИН.ИСКА СТАРТ НА СЕСИЯ

function login(password, account) {
    $.mobile.loading('show', {
        text: 'Свързване с базата...',
        textVisible: false,
        theme: temaloading,
        html: ""
    });
    $.ajax({
        type: "POST",
        url: urldata + "scripts/login.php",
        timeout : 60000,//ДА НЕ Е ПО МАЛКО ЗАЩОТО НЕ ДОЧАКВА ГРЕШКАТА ОТ ФАЙЪРБЪРДА ДА СЕ ВЪРНЕ
        data: {
            "password": password,
            "accountname": account,
            "databasepath": localStorage.getItem('databasepath'),
            "databaseuser": localStorage.getItem('databaseuser'),
            "databasepassword": localStorage.getItem('databasepassword'),
            "version": version
        },
        success: function(data) {
            $.mobile.loading('hide');
            is_logged = true;
            $('#loginresult').html(data);
           
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $.mobile.loading('hide');
            alert("Грешка при връзка със сървъра от тип : " + errorThrown + "Грешка: " + XMLHttpRequest.status);
           
            
        }
    });
}