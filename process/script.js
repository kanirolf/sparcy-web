$(document).ready(function(){
	$.ajax({
		url: "/process/process.php",
		type: "POST",
		data: {id: queryID} ,
		async: true,
		cache: false
	}).done(function(data, status, something){
		data = JSON.parse(data);
		if (data["status"] == "Image successfully processed.")
			window.location = data["data"]['url'];
	}).fail(function(x, y, z){
	});
});
