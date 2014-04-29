<?php 
$id = $_GET["id"];

$results = "/process/".$id."/outDir";

$images = array();

foreach(preg_grep("/[^.]+\.png/", scandir($imageData[$results].'/'.$imageData['name'])) as $image){
	$suffix = array();
	preg_match('/[^-]+-[A-Z]?_+([^.]+)\.png/', $image, $suffix);
	$images[$suffix[1]] = '<img src=\'/process/'.$imageData["id"].'/outDir/'.$imageData["name"].'/'.$image.'\' />';
}

returnProcessingState(true, "Image successfully processed.",
array(
"image" => $imageData["name"].$imageData["ext"],
"images" => $images,
"zip_file" => '/process/'.$imageData["id"].'/outDir/'.$zip_name,
"command" => $optionString
));


<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>SpArcFiRe Web Interface</title>
		<link href='http://fonts.googleapis.com/css?family=Dosis|Concert+One' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="style.css" type="text/css">
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="script.js"></script>
	</head>
	<body>
		<header>
			<p>SpArcFire <b>WebUI</b></p>
		</header>
		<nav id="steps">
			<span class="query active">
				create new query
			</span>
			&gt;
			<span class="processing">
				process
			</span>
			&gt;
			<span class="results">
				results
			</span>
		</nav>
		<section class="results step active">
			<div class="container">
				<header>
					Results of processing:
				</header>
				<a id="download" href="">
					download results
				</a>
				<div id="resultImages">
				</div>
			</div>
		</section>
	</body>
</html>