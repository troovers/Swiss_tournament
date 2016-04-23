$(document).ready(function() {
	$("input[type='submit']:not([name='delete'], [name='clear'])").hoverIntent(function() {
		$(this).animate({"border": "1px solid #007600", backgroundColor: "#007600", color: "#FFFFFF"}, 300);
	}, function() {
		$(this).animate({"border": "1px solid #007600", backgroundColor: "#FFFFFF", color: "#007600"}, 300);
	});
	
	$("form[name='add_participant'] input[name='name']").focus();
	
	$("form[name='wedstrijden'] input[type='submit']").click(function (e) {
		$("input:not([type='submit'])").css({"border": "1px solid #CCCCCC", "background-color": "#FFFFFF"});
		
		var counter = 0;
		
		$("input[type='text']").each(function(){
			if($.trim($(this).val()) == ""){
				counter += 1;
				
				$(this).css({"border": "1px solid red"});
			}
		});
		
		if(counter == 0) {
			return true;
		} else {
			$("#response").empty().html("<div id='error'>U heeft niet alle velden ingevuld</div>");
			
			return false;
		}
	});
	
	$("form[name='add_participant'] input[type='submit']").click(function (e) {
		$("input:not([type='submit'])").css({"border": "1px solid #CCCCCC", "background-color": "#FFFFFF"});
		
		var counter = 0
			name = $.trim($("input[name='name']").val())
			filename = $.trim($("input[name='filename']").val());
		
		if($.trim($("input[name='name']").val()) == "") {
			counter += 1;
			
			$("input[name='name']").css({"border": "1px solid red"});
		}
		
		if(counter == 0) {
			$.ajax({
				type: "POST",
				url: "../handlers/participant_handling.php",
				data: "name="+name+"&filename="+filename,
				dataType: "json",
				success: function(data) {
					if(data.status == "error") {
						$("#response").empty().html("<div id='error'>"+data.message+"</div>");
					} else if(data.status == "succes") {
						window.location.href = "tournament_participants.php?edition="+filename+"&msg=sf_add";
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(textStatus+" - "+errorThrown);
				}
			});
		} else {
			$("#response").empty().html("<div id='error'>U heeft niet alle velden ingevuld</div>");
		}
		
		return false;
	});
	
	$("form[name='add_tournament'] input[type='submit']").click(function (e) {
		$("input:not([type='submit'])").css({"border": "1px solid #CCCCCC", "background-color": "#FFFFFF"});
		
		var counter = 0
			name = $.trim($("input[name='name']").val())
			year = $.trim($("select[name='year']").val());
		
		if($.trim($("input[name='name']").val()) == "") {
			counter += 1;
			
			$("input[name='name']").css({"border": "1px solid red"});
		}
		
		if(counter == 0) {
			$.ajax({
				type: "POST",
				url: "handlers/tournament_handling.php",
				data: "name="+name+"&year="+year,
				dataType: "json",
				success: function(data) {
					if(data.status == "error") {
						$("#response").empty().html("<div id='error'>"+data.message+"</div>");
					} else if(data.status == "succes") {
						window.location.href = "?msg=sf_add";
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(textStatus+" - "+errorThrown);
				}
			});
		} else {
			$("#response").empty().html("<div id='error'>U heeft niet alle velden ingevuld</div>");
		}
		
		return false;
	});
});