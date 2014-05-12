<?php 

include $_SERVER["DOCUMENT_ROOT"].'/php/includes.php' 

	/* create a zip for the files. the zip will be created in /outDir
	 * as galaxy_[imageId]_data.zip with two folders inside: images/
	 * for the images and tables/ for the comma/tab separated value
	 * tables.
	 */
	
	$zipName = "galaxy_".$imageData["name"]."_data.zip";
	$files = new ZipArchive;
	
	$files->open($imageData["outDir"]."/".$zip_name, ZipArchive::CREATE);
	
	$files->addEmptyDir("images");
	$files->addEmptyDir("tables");
	
	$files->addPattern("/[^.]+\.[ct]sv/", $imageData["outDir"], array(
			"remove_all_path" => TRUE, "add_path" => "tables/"));
	
	$files->addPattern("/[^.]+\.png/", $imageData["outDir"]."/".$imageData["name"], array(
			"remove_all_path" => TRUE, "add_path" => "images/"));
	
	$files->close();
	
	// generate an array of <img /> using each image in the output directory
	
	$images = array();
	
	foreach(preg_grep("/[^.]+\.png/", scandir($imageData["outDir"].'/'.$imageData['name'])) as $image){
		$suffix = array();
		preg_match('/[^-]+-[A-Z]?_+([^.]+)\.png/', $image, $suffix);
		$images[$suffix[1]] = '<img src=\'/process/'.$imageData["id"].'/outDir/'.$imageData["name"].'/'.$image.'\' />';
	}
	
	returnProcessingState(true, "Image successfully processed.", 
		array(
			"image" => $imageData["name"].".".$imageData["ext"],
			"images" => $images,
			"zip_file" => '/process/'.$imageData["id"].'/outDir/'.$zipName,
			"command" => $optionString
	));
}

?>


<!DOCTYPE html>
<html>
	<head>
		<?php include $_TEMPLATES."/header.php" ?>
	</head>
	<body>
		<?php include $_TEMPLATES."/nav.php" ?>
		<div id="content">
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
		</div>
	</body>
</html>