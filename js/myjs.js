//КОНФИГУРАЦИОННИ ПРОМЕНЛИВИ
var urldata = "http://www.mrep.info/";
//var urldata = "http://109.160.34.166:8887/mrepbuild/"; //СМЯНА НА УРЛ
var serverurl =  localStorage.getItem('urldata');
if(serverurl && serverurl != "")
    urldata = serverurl;
var databasemanip = 0;
var mrep_colours =
        {
            "alert" : "#d8cd5c", 
            "danger" : "#d85c67",
            
        }

var globalvars = {};//namespace s globalni promenlivi 
//#d8cd5c - Жълто, #d85c67 - Червено
var toast=function(msg, opts){//Алтернативен alert      
    var colour = "#5ca5d8";//стандартно излиза в този цвят
    var fade_time = 900;
    if(typeof opts !== 'undefined')
    {
    if (typeof opts.colour !== 'undefined')
        colour = opts.colour;
    if (typeof opts.fade_time !== 'undefined')
        fade_time = opts.fade_time;
}
    $("<div class='ui-loader ui-overlay-shadow ui-body-e ui-corner-all'><h3>"+msg+"</h3></div>") 
            .css({ display: "block", 
		opacity: 0.9, 
		position: "fixed",
		padding: "7px",
                background: colour,
                "font-size": "11px",
		"text-align": "center",
                "text-shadow": "none",
		width: "270px",
		left: ($(window).width() - 284)/2,
		top: $(window).height()/2 })
	.appendTo( $.mobile.pageContainer ).delay( fade_time )
	.fadeOut( 900, function(){
		$(this).remove();
	});
};//

var temaloading = "a"; //ЦВЯТ НА ЛОАДИНГ
var version = 60;//Версия на API
var presseda; //текущ линк.При натискане на линк се маха активния цвят на предния линк.Да се провери да не би да бави
var is_logged = false;
$.ajaxSetup({
    cache: false
}); //opravia buga v IOS6 pri koito se keshirat AJAX responses !!! Vajno
//
//Натиска бутона за 1 ден справка при показване на страницата със филтрите
$(document).on("pageshow", "#filter", function() {
   
    $("input[name=datefilter]:checked").click();
     //При показване на страницата с календара натиска инпута който последно е натискан
     //Зарежда стойности на филтрите от глобалните променливи
    
});
$(document).on("pageshow", "#filterpage", function() {
   
    if(databasemanip === 1)
    {
        $('#startsql').removeClass('ui-disabled');
    }
    else
    $('#startsql').addClass('ui-disabled');    
    
});


$(document).on("pageinit", "#filter", function() {
   
    if(localStorage.getItem('mrep_rows') && localStorage.getItem('mrep_hours') && localStorage.getItem('mrep_tekday'))
    {
    $("#rows").val(localStorage.getItem('mrep_rows')).slider("refresh");
    $("#hours").val(localStorage.getItem('mrep_hours')).slider("refresh");
    $("#tekday").val(localStorage.getItem('mrep_tekday')).slider("refresh");
}

$( "#otdata" ).mobipick({
        dateFormat: "dd.MM.yyyy",
        overlayTheme: "b",
        theme: "a" ,
        buttonTheme     : "b",
       
        
           
    });
$( "#dodata" ).mobipick(
        {
        dateFormat: "dd.MM.yyyy",
        overlayTheme: "b",
        theme: "a",
        buttonTheme     : "b",
        
    });
});

   



   
    
    


$(document).on("pageshow", function() { //Проверява дали е сетната променливата is_logged = true и променя страницата на тази за лог
     // ТОВА Е ЗАРАДИ КЕsha v IOS9
    if (is_logged === false && $.mobile.activePage.attr('id') != "settings" && $.mobile.activePage.attr('id') != "settings_choser" && $.mobile.activePage.attr('id') != "helplog" && $.mobile.activePage.attr('id') != "accsettings") $(":mobile-pagecontainer").pagecontainer("change", "#login", {
        transition: "turn"
    });

 
});

$(document).on("pageshow", "#accsettings" , function() {
    
   $("#mrep_user").val(localStorage.getItem('mrepuser1'));
   $("#mrep_obekt").val(localStorage.getItem('mrepobekt1'));
   $("#mrep_parola").val(localStorage.getItem('mrepparola1'));
 
});

$(document).on("pagehide", "#home", function() {
    $("#todaysales").hide();//СКРИВА РЕЗУЛТАТЪТ ОТ СПРАВКАТА ЗА ДА НЕ СЕ НАВИРА ПОД ФУТЪРА 
    $('#the-select').prop('selectedIndex',0).selectmenu( "refresh" );//Инициализира селекта със бързите справки в начална позици
});
$(document).on("pagehide", "#spravki", function() { //Скрива менюто със справки при навигиране извън страницата
    $("#menu_spravki").hide();  
});
$(document).on("pageshow", "#spravki", function() { //Показва менюто със справки при показване на страницата без да има мигане на футъра при фонгап
    $("#menu_spravki").slideDown();  
});


$(document).on("pageshow", "#chart", function() {
     var line1=[['2008-09-30 4:00PM',4], ['2008-10-30 4:00PM',6.5], ['2008-11-30 4:00PM',5.7], ['2008-12-30 4:00PM',9], ['2009-01-30 4:00PM',8.2]];
    $.jqplot.config.enablePlugins = true;
    toast('Сега ще изкочи много яка графика :)');
    $.jqplot('chartdiv', [
        
            line1
        
    ], {title:'Default Date Axis',
    axes:{xaxis:{renderer:$.jqplot.DateAxisRenderer}},
    series:[{lineWidth:4, markerOptions:{style:'square'}}]});
});


//Ивенти на екрана с монитори
//
//
$(document).on("pageinit", "#monitors", function(e) { 

    $('#startmonitoring').tap(function() {
        var counter = 1;
        $('#sales').load(urldata + 'scripts/test3.php');
        $('#monitorstatus').html("<span style='color:#007734'>Стартиран</span>");
        timerId = setInterval(function() {
            $('#sales').load(urldata + 'scripts/test3.php');
            counter++;
            $('#monitorstatus').html("<span style='color:green'>" + counter + " цикъла</span>");
        }, 20000); //През колко секунди да се рефрешват масите
    });
    
    
    
$('#stopmonitoring').tap(function() {
        clearInterval(timerId);
        $('#monitorstatus').html("<span style='color:white'>Спрян</span>");
        $('#sales').empty();
    });
    //ПРАВИ МАСАТА ПО ГОЛЯМА
    $(document).on("tap", ".maca", function(е) {
        if ($(this).hasClass('activemaca')) {
            $(this).removeClass('activemaca');
            $(this).find('.monitorartcontent').slideUp();
            $(this).css('height', 'auto').css('width', 'auto');
            //$('#startmonitoring').tap();
        } else {
            $(this).find('.monitorartcontent').slideDown();
            clearInterval(timerId);
            $('#monitorstatus').html("<span style='color:white'>Спрян</span>");
            $(this).css('height', 'auto').css('width', 'auto');
            $(this).addClass('activemaca');
        }
        return false;
    });    
    
    
    
}
        );
//ФУНКЦИИ ЗАКАЧАНИ СЛЕД document ready
$(document).on("pageinit", "#login", function(e) { //Активира ивентите при инициализиране на първата страница
     //Ако има сетнати данни за филтрите в локал сторидж ги зарежда.в противен случай зарежда дефолт стойности
    ////Maha kesha na IOS
	//location.reload();
	if(localStorage.getItem('mrepuser') && localStorage.getItem('mrepparola'))
    $(".guestbutton").hide();
    if(!localStorage.getItem('mrepuser1') && !localStorage.getItem('mrepparola1') && !localStorage.getItem('mrepuser2') && !localStorage.getItem('mrepparola2') && !localStorage.getItem('mrepuser3') && !localStorage.getItem('mrepparola3') )
    $(".accountchanger").hide();
    else
    {
        $("#loginbutton").hide();
        $(".guestbutton").hide();
        if(localStorage.getItem('mrepuser1'))
        $('#mrepuser1').show().text(localStorage.getItem('mrepobekt1') || 'Вход');
        if(localStorage.getItem('mrepuser2'))
        $('#mrepuser2').show().text(localStorage.getItem('mrepobekt2') || 'Вход 2');
        if(localStorage.getItem('mrepuser3'))
        $('#mrepuser3').show().text(localStorage.getItem('mrepobekt3') || 'Вход 3');
    }
      
    
  
  
  //Зарежда реклама, или системно съобщение
   $("#reklama").load(urldata + 'advertisement/important.txt', 
   function( response, status, xhr ) {
   if (status == "succes")
   $("#reklama").show();
});
   
    
    
   
    
    $('#objects-checker').tap(function() {
        get_obekti();
    });
   
    
    $(".buttonforpress").on('touchstart', function(e) {
       $(this).toggleClass("pressedbutton");    
    });
    $(".buttonforpress").on('touchend', function(e) {
       $(this).toggleClass("pressedbutton");    
    });//Оцветяване на бутони с клас pressedbutton при натискане
     
      
   
    
    
    //МАХА КЛАСА ОЦВЕТЯВАЩ БУТОНИТЕ ЛИНК СЛЕД КАТО Е НАТИСНАТ СЛЕДВАЩИЯ
    $("a").on('tap', function(e) {
        //toast(presseda);
        $(presseda).removeClass("ui-btn-active");
        presseda = this;
    });
    //ПУСКА МОНИТОр НА СМЕТКИТЕ
    
    
    
   
    $('#load-fields').tap(function() {
        get_columnnames();
    });
    $('.scrollup').tap(function() {
        window.scroll(5000000, 0);
        return false;
    });
    $('.scrolldown').tap(function() {
        window.scroll(0, 5000000);
        return false;
    });
    $('.printbutton').tap(function() {
        window.print();
    });
    

    //ИЗВИКВА ФУНКЦИЯ ЗА ЛОГВАНЕ
    $(document).on("tap", '#loginbutton', function() {
        password = $('#parola').val();
        account = $('#accountname').val();
        databasemanip = 0;
        login(password, account);
        return false;
    });
    
    $(document).on("tap", '#mrepuser1', function() { 
        $('#accountname').val(localStorage.getItem('mrepuser1'));
        $('#parola').val(localStorage.getItem('mrepparola1'));
        databasemanip = 0;
        password = $('#parola').val();
        account = $('#accountname').val();
        login(password, account);
        return false;
    });
    $(document).on("tap", '#mrepuser2', function() {
        $('#accountname').val(localStorage.getItem('mrepuser2'));
        $('#parola').val(localStorage.getItem('mrepparola2'));
        password = $('#parola').val();
        account = $('#accountname').val();
        login(password, account);
        return false;
    });
    $(document).on("tap", '#mrepuser3', function() {
        $('#accountname').val(localStorage.getItem('mrepuser3'));
        $('#parola').val(localStorage.getItem('mrepparola3'));
        password = $('#parola').val();
        account = $('#accountname').val();
        login(password, account);
        return false;
    });
    //ТЕСТОВ ЛОГ МАГАЗИН
    $(document).on("tap", '#guestloginbutton', function() {
        password = "8";
        account = "TEST";
        login(password, account); 
        return false;
    });
    
    //ТЕСТОВ ЛОГ РЕСТОРАНТ
     $(document).on("tap", '#guestrestloginbutton', function() {
        password = "8";
        account = "TESTREST";
        login(password, account); 
        return false;
    });
    
    //БУТОН ЗА ЗАПИС НА АКАУНТ И ПАРОЛА В ЛОКАЛ СТОРИДЖ НА ПЪРВАТА СТРАНИЦА
    $(document).on("tap", '#savefilters_perm', function() {
        e.stopPropagation();
        e.stopImmediatePropagation();   
       
        localStorage.setItem('mrep_rows', $('#rows').val());
        localStorage.setItem('mrep_hours', $('#hours').val());
        localStorage.setItem('mrep_tekday', $('#tekday').val()); 
        toast("Запис успешен");
    
        
    });
    
     $(document).on("tap", '#savelogininfo', function() {
        e.stopPropagation();
        e.stopImmediatePropagation(); 
        var user = $('#accountname').val();
        var parola = $('#parola').val();
        localStorage.setItem('mrepuser', user);
        localStorage.setItem('mrepparola', parola);
        toast("Запис успешен!");
        return false;
    });
    
  
    $(document).on("tap", '#clear_all', function() { 
        e.stopPropagation();
        e.stopImmediatePropagation(); 
        $('#accountname').val("");
        $('#parola').val("");
        localStorage.removeItem('mrepuser');
        localStorage.removeItem('mrepparola');
        localStorage.removeItem('mrepobekt');
        localStorage.removeItem('mrepuser1');
        localStorage.removeItem('mrepparola1');
        localStorage.removeItem('mrepobekt1');
        localStorage.removeItem('mrepuser2');
        localStorage.removeItem('mrepparola2');
        localStorage.removeItem('mrepobekt2');
        localStorage.removeItem('mrepuser3');
        localStorage.removeItem('mrepparola3');
        localStorage.removeItem('mrepobekt3');
        toast("Запазените акаунти са изтрити. Моля рестартирайте приложението за да влязат промените в сила!", {"colour" : mrep_colours.alert, "fade_time":"2500"});       
    });
    
        
    
    
    
    
    
   
    //ЗАРЕЖДА ДАТИ И ПОЛЕТО С НОМЕРА НА СПРАВКАТА ЗА ДА МОЖЕ ДА СЕ ПРАВТИ ПРАВИЛНО КЪМ СЪРВЪРА КОЙ НОМЕР СПРАВКА
    //ДА СЕ ВАДИ.
    $(document).on("tap", '#spravki a', function() {
        e.stopPropagation();
        e.stopImmediatePropagation();
        var id = $(this).data('name');
        $('#spravkaname').html($(this).text()); //Изписва името на справката над календара
        $('#filterform').val(id); //Слага текущия номер на активаната справка
        $('#filtercolumns').empty(); //Занулява колоните за филтър в справката
        var fullDate = new Date();
        var twoDigitMonth;
        if (fullDate.getMonth() > 8) {
            twoDigitMonth = fullDate.getMonth() + 1;
        } else twoDigitMonth = "0" + (fullDate.getMonth() + 1);
        var twoDigitday;
        if(fullDate.getDate() > 10)
          twoDigitday = fullDate.getDate();
        else twoDigitday = "0" + fullDate.getDate();
       
            
        var currentDate = twoDigitday + "." + twoDigitMonth + "." + fullDate.getFullYear();
        //alert(currentDate);
        $('#otdata').val(currentDate);
        $('#dodata').val(currentDate);
        $(".result").empty();
        //alert(id);
        if(id == 11 || id == 12 || id == 101)//ТОВА СА НОМЕРА НА СПРАВКИ ЗА КОИТО НЕ ТРЯБВА КАЛЕНДАР И СТРАНИЦАТА СЕ ПРОПУСКА
        {
        $('#lastrow').val('1'); //Да показва от ред 1 !!!
        get_spravka();
        $(":mobile-pagecontainer").pagecontainer("change", "#data", {
            transition: "none"
        });
        return false;
    }
    });
    //ВИКА СЕ СПРАВКА И СЕ НУЛИРА ПОЛЕТО В КОЕТО СЕ ПАЗИ ДО КОЙ РЕД ДА СЕ ИСКАТ ОТ SQL
    $(document).on("tap", '#zaredispravka', function() {
        $('#lastrow').val('1'); 
        get_spravka();
        $(":mobile-pagecontainer").pagecontainer("change", "#data", {
            transition: "none"
        });
        return false;
    });
    //ИЗПЪЛНЯВА ВСЕКИ СКРИПТ ЗАПИСАН В КОНФИГ ФАЙЛА НА АКАУНТА
    $(document).on("tap", '#startsql', function() {
        if (confirm("Ако сте създали клиентска процедура тя ще бъде изпълнена.Ако не сте задали такава ще бъде изпълнена празна процедура.Желаете ли да продължите?") === false) return false;
        if (confirm("Сигурни ли сте?Операцията е невъзвратима.") === false) return false;
        var spravkano = 66; //66 е номера на къстъм скрипта.
        $('#startsql').addClass('ui-disabled');
        $.mobile.loading('show', {
            text: 'Изтриват се данни...',
            textVisible: true,
            theme: temaloading,
            html: ""
        });
        $.ajax({
            type: "POST",
            url: urldata + "scripts/test.php",
            data: {
                "ot": 1,
                "do": 1,
                "fromrow": 1,
                "torow": 1,
                "spravka": spravkano,
                "objects": 1,
                "hours": 0,
                "klientid": 1,
                "day": 0
            },
            success: function(data) {
                $.mobile.loading('hide');
                $("#databasesettingspanel").panel("close");
                toast(data);
                $('#startsql').removeClass('ui-disabled');
            }
        });
    });
    //ТРИТЕ ИВЕНТА СА СВЪРЗАНИ С КАЛЕНДАРА
    //ИНИЦИАЛИЗАЦИЯ НА ДВАТА КАЛЕНДАРА В ЕКРАНА С ФИЛТРИ
  
    //СЕТВА КАЛЕНДАРИТЕ СПОРЕД НАТИСКАНЕТО НА БУТОНА ДЕН< МЕСЕЦ< СЕДМИЦА < ГОДИНА
    $('[name=datefilter]').click(function() {
        //toast($(this).val());
        var fullDate1 = new Date();
        var fullDate2 = new Date();
        var a = fullDate2.setDate(fullDate2.getDate() - $(this).val() - $('#tekday').val() + 1);
        var b = fullDate1.setDate(fullDate1.getDate() - $('#tekday').val() + 1);
        var oldDate = new Date(a);
        var fullDate = new Date(b);
        var twoDigitMonth = "";
        if (oldDate.getMonth() > 8) {
            twoDigitMonth = oldDate.getMonth() + 1;
        } else twoDigitMonth = "0" + (oldDate.getMonth() + 1);
        var twoDigitMonthnow = "";
        if (fullDate.getMonth() > 8) {
            twoDigitMonthnow = fullDate.getMonth() + 1;
        } else twoDigitMonthnow = "0" + (fullDate.getMonth() + 1);
        var twodigday;
        if(oldDate.getDate() > 9)
        twodigday = oldDate.getDate();
        else twodigday = "0" + oldDate.getDate();
        var twodigday2;
        if(fullDate.getDate() > 9)
        twodigday2 = fullDate.getDate();
        else twodigday2 = "0" + fullDate.getDate();
        //toast(oldDate.getMonth()+ "." + fullDate.getMonth());
        var currentDate = twodigday + "." + twoDigitMonth + "." + oldDate.getFullYear();
        var currentDatenow = twodigday2 + "." + twoDigitMonthnow + "." + fullDate.getFullYear();
        //toast(fullDate.getMonth() + 1);
        $('#otdata').val(currentDate);
        $('#dodata').val(currentDatenow);
    });
    //СМЯНА НА ЦВЕТА ПРИ КЛИК В ЕКРАНА С ПЪРИЯ РЕЗУЛТАТ ОТ ВСИЧКИ СПРАВКИ.
    $(document).on("tap", ".spravka-ul li", function() {
        //toast($(this).hasClass('clickedli'));
        if ($(this).hasClass('clickedli') === true) {
            show_record_sdr($(this).data("num"), $(this).data("obektnum"), $(this).data("spravka2"));
        } else {
            $('.clickedli').toggleClass('clickedli');
            $(this).toggleClass('clickedli');
            
        }
    });
    //СМЯНА НА ЦВЕТА ПРИ КЛИК В ЕКРАНА С РЕЗУЛТАТЪТ ОТ ТЪРСЕНЕ НА АРТИКУЛИ В СКЛАДА.
    $(document).on("click", "#artikuls li", function() {
        if ($(this).hasClass('clickedli') === true) {
            $('#artname').html($(this).find('.articulname').html());
            $('#artaction').popup('option', 'transition', 'none');
            $('#artaction').popup('open');
        } else {
            $('#artikuls').find('.clickedli').toggleClass('clickedli');
            $(this).toggleClass('clickedli');
        }
    });
    $(document).on("click", "#partners li", function() {
        if ($(this).hasClass('clickedli') === true) {
            $('#partname').html($(this).find('.partname').html());
            $('#partaction').popup('option', 'transition', 'none');
            $('#partaction').popup('open');
        } else {
            $('#partners').find('.clickedli').toggleClass('clickedli');
            $(this).toggleClass('clickedli');
        }
    });
    //НАВИГАЦИЯТА
    $(document).on("tap", ".footerul li a", function(е) {
        $(".result").empty();//Чисти ако има нещо в резулт дива.
        е.preventDefault();
        e.stopPropagation();
        var href = $(this).attr('href');
        if (href === '#login') {//Изход от приложението
            //if(confirm("Желаете ли да напуснете приложението!")) ДА СЕ НАПРАВИ С КЪСТЪМ ПРОЗОРЕЦ - ДИАЛОГ
            $(":mobile-pagecontainer").pagecontainer("change", href, {
            transition: "none"
        });
        }
        else
        $(":mobile-pagecontainer").pagecontainer("change", href, {
            transition: "none"
        });
    return false;
    });
    $(document).on("tap", "#askregistration", function(е) {
        if ($("#reg_user").val()) ask_registration();
        else toast("Въведете име!", {"colour":"#d8cd5c", "fade_time":"1500"});
        return false;
    });
    //НАВИГАЦИЯТА
    $(document).on("tap", ".li-zapisi li a", function(е) {
        var ratio = $(this).data('nextratio');
        var rows = $('#rows').val();
        $('#rows').val(rows * ratio);
        get_spravka();
        $('#rows').val(rows);
        return false;
    });
    $(document).on("tap", "#artaction li a", function(evt) {
        
        var obektid = $('.clickedli').find('#artikul-obektid').val();
        var ckladid = $('.clickedli').find('#artikul-ckladid').val();
        var number = $('.clickedli').find('#artikul-artnomer').val();
        var artname = $('.clickedli').find('#artikul-name').val();
        var spravka2 = $(this).data('name');
        $.mobile.loading('show', {
            text: 'Зареждане на данни...',
            textVisible: false,
            theme: temaloading,
            html: ""
        });
        $.ajax({
            type: "POST",
            url: urldata + "scripts/test2.php",
            data: {
                "number": number,
                "spravka2": spravka2,
                "obektid": obektid,
                "ckladid": ckladid
            },
            success: function(data) {
                $('#spravkaresult').find('#dialog-header').html(artname);
                $.mobile.loading('hide');
                $('#myMessage').html(data);
                $("#lnkDialog").click(); //ОТВАРЯ ДИАЛОГА С ИЗКУСТВЕНО НАСТИКСАНЕ НА ЛИНК
                $('#myMessage').trigger('create'); //СЛАГА СТИЛ НА ДИАЛОГА В КОЙТО СА НАЛЯТИ ДАННИ С AJAX
            }
        });
        return false;
    });
    
    $(document).on("tap", "#partaction li a", function(evt) {
        
        $('#klientnameid').val($('.clickedli').data("nomer"));
        $('#filterform').val($(this).data('name'));
        $(":mobile-pagecontainer").pagecontainer("change", "#filter", {
        transition: "none"
    });
        return false;
    });
    //ПРОБА ЗА КЛИК ИВЕНТ НА РЕД В ТАБЛИЦАТА >
    $(document).on("tap", "#table-custom-2 tr", function() {
        toast($(this).html());
    });
    $(document).on("tap", "#database_check", function() {
        $.mobile.loading('show', {
            text: 'Зареждане на данни...',
            textVisible: false,
            theme: temaloading,
            html: ""
        });
        var db = $('#databasepath').val();
        var user = $('#databaseuser').val();
        var pass = $('#databasepassword').val();
        $.ajax({
            type: "POST",
            url: urldata + "scripts/check_database.php",
            timeout: 20000,
            data: {
                "databasepath": db,
                "databaseuser": user,
                "databasepassword": pass
            },
            //
            success: function(data) {
                $.mobile.loading('hide');
                $('#databasetype').html(data);
                $('#databasetype').trigger("create");
            },
           
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $.mobile.loading('hide');
                $('#databasetype').html(errorThrown + "Изглежда че има проблем с връзката или е надвишен 20 секундния период на конекция.Моля опитайте отново!" + textStatus + XMLHttpRequest);
            }
        });
        return false;
    });
    //ПРОБА ЗА КЛИК ИВЕНТ НА РЕД В ТАБЛИЦАТА >
    //ФУНКЦИИ КОИТО СЕ ВИКАТ ОТ ИВЕНТ ФУНКЦИИТЕ ПО НАГОРЕ
    //ФУНКЦИИ КОИТО СЕ ВИКАТ ОТ ИВЕНТ ФУНКЦИИТЕ ПО НАГОРЕ
    //ФУНКЦИИ КОИТО СЕ ВИКАТ ОТ ИВЕНТ ФУНКЦИИТЕ ПО НАГОРЕ
    //ФУНКЦИИ КОИТО СЕ ВИКАТ ОТ ИВЕНТ ФУНКЦИИТЕ ПО НАГОРЕ
    //ОСНОВНА ФУНКЦИЯ КОЯТО ИЗВИКВА СПРАВКАТА
    //ПРОБА ЗА КЛИК ИВЕНТ НА РЕД В ТАБЛИЦАТА >
    $(document).on("change", "#the-select", function() {
        show_quick($(this).val());
    });
    
    $(document).on("change", "#mrep_number", function() {
     $('#mrep_user').val(localStorage.getItem('mrepuser'+ $('#mrep_number').val()));
     $('#mrep_obekt').val(localStorage.getItem('mrepobekt'+ $('#mrep_number').val()));
     $('#mrep_parola').val(localStorage.getItem('mrepparola'+ $('#mrep_number').val()));
    });
    
    //ЗАПАЗВА ДАННИТЕ ЗА ДОСТЪП ДО БАЗАТА - ПЪТ < ПОТРЕБИТЕЛ В ЛОКАЛ СТОРИДЖ
    $(document).on("tap", '#save_account', function() {
        var user = $('#mrep_user').val();
        var password = $('#mrep_parola').val();
        var obekt = $('#mrep_obekt').val();
        var number = $('#mrep_number').val();   
        localStorage.setItem('mrepuser'+ number, user);
        localStorage.setItem('mrepparola' + number, password);
        localStorage.setItem('mrepobekt' + number, obekt);
        toast("Успешен запис!")
        return false;
    });
});

