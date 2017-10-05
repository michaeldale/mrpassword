$(document).ready(function () {

	$(".show_password_history").click(function () {
		var password_id = $(this).attr("id");
		var password_exploded = password_id.split('-');
		password_id = password_exploded[1];

		
		$('#id-' + password_id).replaceWith('<a id="id-' + password_id + '">loading...</a>');
		
		$.ajax({
			type: "GET",
			url:  mrp_base_url + "/passwords/gethistory/" + password_id,
			success: function(html){
				$('#id-' + password_id).replaceWith(html);
			}


		 });
		 
	});

	$(".show_password").click(function () {
		var password_id = $(this).attr("id");
		var password_exploded = password_id.split('-');
		password_id = password_exploded[1];

		
		$('#id-' + password_id).replaceWith('<a id="id-' + password_id + '">loading...</a>');
		
		$.ajax({
			type: "GET",
			url:  mrp_base_url + "/passwords/get/" + password_id,
			success: function(html){
				$('#id-' + password_id).replaceWith(html);
			}


		 });
		 
	});
	
	$(".show_share_password").click(function () {
		var password_id = $(this).attr("id");
		var password_exploded = password_id.split('-');
		password_id = password_exploded[1];

		
		$('#id-' + password_id).replaceWith('<a id="id-' + password_id + '">loading...</a>');
		
		$.ajax({
			type: "GET",
			url:  mrp_base_url + "/passwords/getshare/" + password_id,
			success: function(html){
				$('#id-' + password_id).replaceWith(html);
			}


		 });
		 
	});
	
	$(".show_global_password").click(function () {
		var password_id = $(this).attr("id");
		var password_exploded = password_id.split('-');
		password_id = password_exploded[1];

		
		$('#id-' + password_id).replaceWith('<a id="id-' + password_id + '">loading...</a>');
		
		$.ajax({
			type: "GET",
			url:  mrp_base_url + "/passwords/getglobal/" + password_id,
			success: function(html){
				$('#id-' + password_id).replaceWith(html);
			}


		 });
		 
	});

	$(".generate_password").click(function (e) {
		e.preventDefault();
		
		var type = 0;
	
		type = $("input:radio[name=password_type]:checked").val();
		
		$(".generate_field").val(generatePassword(type));	

	
	});
	
	var generatePassword = function (type) {
	
	    var words = ["password", "alpha", "bravo", "charlie", "delta", "echo", "foxtrot", "golf", "hotel", "india", "juliet", "kilo", "lima", "mike", "november", "oscar", "papa", "quebec", "romeo", "sierra", "tango", "uniform", "victor", "whiskey", "x-ray", "yankee", "zulu"];
		var symbols = ["!", "\"", "$", "%", "^", "&", "*", "(", ")", "-", "_", "=", "+", "[", "{", "]", "}", ";", ":", "'", "@", "#", "~", "|", ",", "<", ".", ">", "/", "?"];	
		
		var passwd = '';
	
		if (type == 1) {
			var word = words[Math.floor(Math.random()*words.length)];
			var symbol = symbols[Math.floor(Math.random()*symbols.length)];

			passwd = word;			
			passwd += symbol;
			
			var length = 3;
			var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		}
		else if (type == 2) {
			var length = 32;
			var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		}
		else {
			var length = 32;
			var chars = '~!@#$%^&*()_+=abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		}
	
		for (i=1;i<=length;i++) {
			var c = Math.floor(Math.random()*chars.length + 1);
			passwd += chars.charAt(c);
		}

		return passwd;

	};
	
	$('#add_extra_file').click(function (e) {
		e.preventDefault();

		$('#attach_file_area').append('<div><div class="pull-left"><input name="file[]" type="file" /></div><div class="pull-right"><a href="#" id="remove_this_file"><span class="glyphicon glyphicon-remove"></span></a></div></div>');

	});
	
	//Delete existing file
	$('body').on('click', '.delete_existing_password_file', function(e){
		e.preventDefault();

		if (confirm("Are you sure you wish to delete this file?")){
			
			var ticket_id = $(this).closest('li').attr("id");
			var ticket_exploded = ticket_id.split('-');
			ticket_id = ticket_exploded[1];
						
			var file_id = $(this).attr("id");
			var file_exploded = file_id.split('-');
			file_id = file_exploded[1];
			
			$.ajax({
				type: "POST",
				url:  mrp_base_url + "/passwords/deletefile/" + file_id + "/",
				data: "delete=true&password_id=" + ticket_id,
				success: function(html){
					//alert(html);
				}
			 });
			 
			 $(this).parent('li').remove(); 
		}
		else {
			return false;
		}
		
    });	
	


});