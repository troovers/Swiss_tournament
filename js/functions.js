$(document).ready(function() {
	$("input[type='submit']:not([name='delete'], [name='clear'])").hoverIntent(function() {
		$(this).animate({"border": "1px solid #007600", backgroundColor: "#007600", color: "#FFFFFF"}, 300);
	}, function() {
		$(this).animate({"border": "1px solid #007600", backgroundColor: "#FFFFFF", color: "#007600"}, 300);
	});
	
	$("form[name='wedstrijden'] input[type='submit']").click(function (e) {
		$("input:not([type='submit'])").css({"border": "1px solid #CCCCCC", "background-color": "#FFFFFF"});
		
		var counter = 0;
		
		$("input[type='text']").each(function(){
			if($.trim($(this).val()) == ""){
				counter += 1;
				
				$(this).css({"border": "1px solid #CCCCCC"});
			}
		});
		
		if(counter == 0) {
			return true;
		} else {
			$("#response").empty().html("<div id='error'>U heeft niet alle velden ingevuld</div>");
			
			return false;
		}
	});
});