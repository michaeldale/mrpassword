$(document).ready(function () {

    var line_item_count = 0; // Global unique counter

	//Add new line item
    $("#add_item").click(function (e) {
		e.preventDefault();

		line_item_count++; // Increment counter

		var clonedRow = $('.custom_field').clone();
		$(clonedRow).addClass('new_custom_field').removeClass('custom_field');
		$(clonedRow).find('input').val('').end().find('p').append('<a href="#custom" id="delete_item" class="btn btn-danger">Delete</a>').end().appendTo('.extra_custom_field');

	});
	
	//Delete existing line item
	$('body').on('click', '#delete_item', function(e){	
		e.preventDefault();

		$(this).parent('p').remove(); 
    });

});