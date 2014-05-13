$(document).ready(function(){

	$(".query").addClass('active');
	
	$("input[type=checkbox]").each(function(){
		this.checked = $(this).attr("value") == 1 ? true : false;
		$(this).click(function(){
			$(this).attr('value', !this.value ? 1 : 0);
		});
	});	
	
	$("input[name!=galaxyImage]").attr("disabled", true);

	$("[name=galaxyImage]").change(function(){
		if (!/.((pn|jp)g|fi?ts?)$/.test(this.value)){
			alert("The given image is not a .fits, .png or .jpg image");
		} else {
			if (/.fi?ts?$/.test(this.value)){
				$("#isFits").removeClass("disabled");
				$("#isFits input").attr("disabled", false);
				$("#isFits input#isFits").attr("value", "true");
			} else {
				$("#isFits").addClass("disabled");
				$("#isFits input").attr("disabled", true);
			}
			$("*:not(#isFits)").removeClass("disabled");
			$("*:not(#isFits)").attr("disabled", false);
		}
	});

	$("div.content.query form").submit(function(event){
		$(".process").addClass("active");
		$(".query").removeClass("active");
		history.pushState({}, "", "/process");
		$(window).bind("beforeunload", function(){
			return "The image has not finished processing.";
		});
		event.preventDefault();
		var queryData = new FormData($(this)[0]);
		$.ajax({
			url: "/process/index.php",
			type: "POST",
			data: queryData,
			async: true,
			cache: false,
	        contentType: false,
	        processData: false
		}).done(function(data, status, something){
			$(".process").removeClass("active");
			$(window).unbind("beforeunload");
			data = JSON.parse(data);
			console.log(data);
			if (data['success'])
				window.location = data["data"]['url'];
			else {
				alert(data['status']);
				$("div.content#query").addClass("active");	
			}
		}).fail(function(x, y, z){
			alert(data['status']);
			$("div.content#process").removeClass("active");
			$("div.content#query").addClass("active");
		});
		return false;
	});
});