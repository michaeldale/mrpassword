function autocollapse() {
    var navbar = $('#autocollapse');
    navbar.removeClass('collapsed'); // set standard view
    if($(".navbar-collapse").innerHeight() > 70) // check if we've got 2 lines
        $("#autocollapse").addClass('collapsed');

    var a = $('.navbar-nav li a');
    a.removeClass('a-small-text');
    $(a).each(function(){
        if ($(this).innerHeight() > 51){
            $(this).addClass('a-small-text');
        }
    });
}

$(document).on('ready', autocollapse);
$(window).on('resize', autocollapse);