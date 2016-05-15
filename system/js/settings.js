function populate_sub_themes(theme_name, selected_sub_theme) {

	//quicker to replace the entire html select
	$('#default_sub_theme_area').html('<p>Default Sub Theme<br /><select name="default_sub_theme" id="default_sub_theme"><option value="">loading...</option></select></p>');

		
	$.ajax({
		type: "GET",
		cache: false,
		dataType: "json",
		url:  mrp_base_url + "/data/themes/",
		success: function(data){			
			html = '';

			if (data !== null) {
				$.each(
					data,
					function (index, value) {
						if (theme_name == index) {
							if (value.sub_themes.length !== 0) {
								$.each(
									value.sub_themes,
									function (index2, value2) {
										if (selected_sub_theme == value2) {
											html += '<option value="'+ value2 + '" selected="selected">' + value2  + '</option>';
										}
										else {
											html += '<option value="'+ value2 + '">' + value2  + '</option>';									
										}
									}
								);
							}
							else {
								html += '<option value="">N/A</option>';									
							}
						}
					}
				);
			}
												
			$('#default_sub_theme').html(html);
			if ($.fn.chosen) {
				$('select').chosen();
			}
		}
	});
}


$(document).ready(function () {

	populate_sub_themes($('#default_theme').val(), $('#default_theme').attr("data-sub-theme"));

    $('#default_theme').change(function () {
		populate_sub_themes($(this).val(), $('#default_theme').attr("data-sub-theme"));
	});

		
});