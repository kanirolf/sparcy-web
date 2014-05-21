$(document).ready(function(){
	$(".results").addClass('active');
	
	$("form").submit(function(){
		alert("Nothing has been put in the search bar.");
		return false;
	});
	
	$("#result-search").focus(function(){
		this.value = "";
		$(this).addClass("active").unbind("focus");
		$("form").unbind("submit");
	});
	
});