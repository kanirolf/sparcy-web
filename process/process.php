<?php include $_SERVER["DOCUMENT_ROOT"].'/php/includes.php';

// import the imageData from the SESSION

// generate a string that PHP will run. This version will use
//  the SpArcFiRe program located at /home/wayne/bin/

// start with binary location
$optionString .= 'export MCR_CACHE_ROOT=/tmp && /home/wayne/bin/SpArcFiRe ';

// if the image is a FITS, append FITS options
if ($imageData["ext"] == "fits" || $imageData["ext"] == "fit"){
	$optionString .= " -convert-FITS -p ";
	foreach(array("brightnessQuartileForASinhAlpha", "brightnessQuartileForASinhBeta", "asinhApplications")
		as $fitsOpt)
		$optionString .= $_POST["isFits"][$fitsOpt]." "; 
	foreach(array("compute-starmask", "ignore-starmask") as $fitsOpt)
		$optionString .= $_POST["isFits"][$fitsOpt] == "true" ? "-".$fitsOpt." " : " ";
}

// add -web and directories

$optionString .= '-web '.$imageData["inDir"].' '.$imageData["pngDir"].' '.$imageData['outDir'];

// append SpArcFire execution options
foreach($_POST['mainOptions'] as $option => $value)
	$optionString .= ' -'.$option.' '.$value;

// run the script

$exitCode = 0;
$passArray = array();

exec($optionString." &>".$imageData['id']."/error.log", $passArray, $exitCode);

/* success conditions go past here. success is indicated by exit_code
 * if exit_code is equal to 0, this should indicate proper operation of
 * the SpArcFiRe script. */

// if running SpArcFire was successful, the exit_code should be 0
if ($exitCode)
	returnProcessingState(true, "Image could not be processed.", 
			array(
					"url" => "/results?query=".$imageData["id"],
					"query" => $optionString
			));
else {
	
	/* create a zip for the files. the zip will be created in /outDir
	 * as galaxy_[imageId]_data.zip with two folders inside: images/
	* for the images and tables/ for the comma/tab separated value
	* tables.
	*/
	
	$zipName = "galaxy_".$imageData["name"]."_data.zip";
	$files = new ZipArchive;
	
	$files->open($imageData["outDir"]."/".$zipName, ZipArchive::CREATE);
	
	$files->addEmptyDir("images");
	$files->addEmptyDir("tables");
	
	$files->addPattern("/[^.]+\.[ct]sv/", $imageData["outDir"], array(
			"remove_all_path" => TRUE, "add_path" => "tables/"));
	
	$files->addPattern("/[^.]+\.png/", $imageData["outDir"]."/".$imageData["name"], array(
			"remove_all_path" => TRUE, "add_path" => "images/"));
	
	$files->close();
	
	/* generate associative array of image paths for displaying results
	   associating display name to filename */
	
	$images = array();
	
	foreach(preg_grep("/[^.]+\.png/", scandir($imageData["outDir"].'/'.$imageData['name'])) as $image){
		$suffix = array();
		preg_match('/[^-]+-[A-Z]?_+([^.]+)\.png/', $image, $suffix);
		$images[$suffix[1]] = '/process/'.$imageData["id"].'/outDir/'.$imageData["name"].'/'.$image;
	}

	// create a json file containing information on the galaxy results
	
	$output_info = fopen($imageData["location"]."/info.json", "w");
	
	fwrite($output_info, json_encode(array(	
		"zip" => '/process/'.$imageData["id"].'/outDir/'.$zipName,
		"images" => $images
	)));
	
	fclose($output_info);
	
	returnProcessingState(true, "Image successfully processed.",
		array(
			"url" => "/results?query=".$imageData["id"]
		)
	);
	
}
?>