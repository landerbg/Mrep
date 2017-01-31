$(document).bind("mobileinit", function(){
   //Инициализации изпълняват се преди JQUERY MOBILE
    $.support.touchOverflow = true;
    $.mobile.touchOverflowEnabled = true;
    $.mobile.defaultPageTransition   = 'none';
    $.mobile.defaultDialogTransition = 'none';
    $.mobile.buttonMarkup.hoverDelay = 0;//Няк
    
});


