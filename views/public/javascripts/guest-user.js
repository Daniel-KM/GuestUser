jQuery('document').ready(function() {
    var adminBarHeight = jQuery('#admin-bar');
    jQuery('#guest-user-register-info').css('top', adminBarHeight + 'px');


    jQuery('a#menu-admin-bar-welcome').closest('li').hover(
            function() {jQuery('#menu-guest-user-me').closest('ul').show();},
            function() {jQuery('#menu-guest-user-me').closest('ul').hide();}
        );

    jQuery('#menu-guest-user-register').closest('li').hover(
            function() {jQuery('#guest-user-register-info').show();},
            function() {jQuery('#guest-user-register-info').hide();}
        );
});
