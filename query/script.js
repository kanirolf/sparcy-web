function setAsActive(elementClass){
	$("li.step.active, div.content.active").removeClass("active");
	$("."+elementClass).addClass("active visited");
	$("div#image-stats-container").removeClass("active");
	$("div#option-stats-container").removeClass("active");
	switch(elementClass){
		case "confirm":
			$("div#option-stats-container").addClass("active");
		case "options":
			$("div#image-stats-container").addClass("active");
	}
}

$(document).ready(function(){

// initialization
	
	// make the active link in the nav the "query" link
	
	$(".query").addClass('active');
	
	
	$("ul#steps").on("click", "li.step.visited", function(e){
		if (e.which != 1) return False;
		setAsActive(this.className.split(/\s+/)[1]);
	});
	
	$("div.pageFlipper > div").click(function(e){
		if (e.which != 1) return False;
		var current = $("div.content").index($("div.content.active"));
		var next = current + (this.className == "nextPage" ? 1 : -1);
		current = ["select-image", "options", "confirm"][current];
		next = ["select-image", "options", "confirm"][next];
		switch(current){
			case "select-image":
				if ($("[name=galaxyImage]")[0].files.length == 0)
					return alert("An image file has not been chosen.");
				break;
			case "confirm":
				if (this.className == "nextPage") {
					$("li.step.visited").css("cursor", "default");
					$("ul#steps").off("click", "li.step.visited");
					setAsActive("process");
					$(window).bind("beforeunload", function(){return "The image has not finished processing.";});
					var queryData = new FormData($("form")[0]);
					$.ajax({
						url: "/process/preprocess.php",
						type: "POST",
						data: queryData,
						async: true,
						cache: false,
				        contentType: false,
				        processData: false
					}).done(function(data, status, something){
						$(window).unbind("beforeunload");
						data = JSON.parse(data);
						console.log(data);
						if (data['success'])
							window.location = data["data"]['url'];
						else {
							alert(data['status']);
							$("li.step.visited").css("cursor", "pointer");
							setAsActive("confirm");
							$(".process").removeClass("visited");
							$("ul#steps").on("click", "li.step.visited", function(){
								setAsActive(this.className.split(/\s+/)[1]);
							});
						}
					}).fail(function(x, y, z){
						alert(data['status']);
						$("li.step.visited").css("cursor", "pointer");
						setAsActive("confirm");
						$(".process").removeClass("visited");
						$("ul#steps").on("click", "li.step.visited", function(){
							setAsActive(this.className.split(/\s+/)[1]);
						});
					});
					return false;
				}
				break;
		}
		
		setAsActive(next);
	});
	
	// convert 0, 1 in checkbox inputs to true and false values
	
	$("input[type=checkbox]").each(function(){
		this.checked = $(this).attr("value") == 1 ? true : false;
	});

// select-image
	
	// If the selected image is a .png, .jpg, .fit or .fits, change to the
	// "options" menu. Otherwise, stay on the select-image menu.
	
	
	$("[name=galaxyImage]").change(function(){
		if (!/.((pn|jp)g|fi?ts?)$/.test(this.value)){
			alert("The given image is not a .fits, .png or .jpg image");
		} else {
			if (/.fi?ts?$/.test(this.value)){
				$("#isFits").removeClass("disabled");
				$("#isFits input").attr("disabled", false);
			} else {
				$("#isFits").addClass("disabled");
				$("#isFits input").attr("disabled", true);
			}
			$("div#image-stats-container span#image-stats").text(this.files[0].name);
		}
	});
	
// options
	
	// Create dropdown menus for the categorized main options.
	
	$("div.content.options section#mainOptions section.level:not(easy) div.level-header").click(function(e){
		if (e.which != 1) return False;
		var parent = $(this).closest("section.level");
		if ($(parent).hasClass("active"))
			$(parent).removeClass("active");
		else
			$(parent).addClass("active");
	});
	

	// Switch to page with tooltip texts
	
	$("div#options-help-button").mousedown(function(e){
		if (e.which != 1) return False;
		$("div.content.options-help").addClass("active");
		$("div.content.options").removeClass("active");
	});
	
	$("div#options-back-button").mousedown(function(e){
		if (e.which != 1) return False;
		$("div.content.options-help").removeClass("active");
		$("div.content.options").addClass("active");
	});
	
	$("div.content.options input").change(function(){
		var linkedField = $("span.option-value-container."+$(this).siblings("label").text());
		$(this).addClass("changed");
		if ($(this).attr("type") == "checkbox"){
			if ((this.checked ? 1 : 0) != this.value){
				linkedField.children("span.option-value").text(this.checked ? "Yes" : "No");
				return linkedField.addClass("changed");
			}
		} else if ($(this).attr("value") != this.value){
			linkedField.children("span.option-value").text(this.value);
			return linkedField.addClass("changed");
		} 
		$(this).removeClass("changed");
		linkedField.removeClass("changed");
	});

});