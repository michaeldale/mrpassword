
function menuWidth() {
    var userButtonWidth = 0;
    var userMenuWidth = 0;
    userButtonWidth = $(".user-menu").outerWidth() +3;
    userMenuWidth = $(".user-menu").find("ul.dropdown-menu").outerWidth() +3;
/*
    alert("User button width:" + userButtonWidth + "User menu width:" + userMenuWidth );
*/

    if(userButtonWidth > userMenuWidth){
        $(".user-menu").find("ul.dropdown-menu").width(userButtonWidth);
    }
    if(userMenuWidth > userButtonWidth){
        $(".user-menu").stop().width(userMenuWidth);
    }

}

$(document).ready(menuWidth);