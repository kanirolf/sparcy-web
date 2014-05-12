<?php

include $_SERVER["DOCUMENT_ROOT"].'/php/includes.php';

/* todo:
 * - create something to store metadata about a processed image
 * - fix FITS
 */

/* redirect if there is no $_POST data (probably means that someone tried
 * to access directly, although this can be easily bypassed if a POST
 * request were spoofed.)
 */

if (!count($_POST)){
	if ($_SERVER['HTTP_REFERER'] == "")
		header("Location: /");
	else 
		header("Location: ".$_SERVER['HTTP_REFERER']);
	die();
}

/* define a function to return all relevant variables on process
 * failure or success
 */
function returnProcessingState($success, $status, $data=array(), $echoOut=True){
	
	$result = json_encode(array(
		"success" => $success,
		"status" => $status,
		"data" => $data
	));
	if ($echoOut){ echo $result; } else { return $result; }
	die();
}

/* store image

  the image will be stored in the /tmp directory under a directory
  name generated as the md5 hash of the galaxy name and the current
  timestamp

  */

// define variables detailing image locations and name

$imageName = pathinfo($_FILES["galaxyImage"]["name"]);
$imageId = md5(time().$imageName);

$imageData = array(
        "name" => $imageName['filename'],
        "ext" => $imageName['extension'],
        "id" => $imageId,
        "location" => '../process/'.$imageId.'/',
        "file" => '../process/'.$imageId.'/inDir/'.$imageName['filename'].'.'.$imageName['extension']
);

// create directory for the file
if(!mkdir($imageData["location"]))
	returnProcessingState(false, "Processing directories could not be created.");

// create directories for subprocessing
foreach(array("inDir", "pngDir", "outDir") as $subdir){
	mkdir($imageData["location"]."/".$subdir);
	$imageData[$subdir] = $imageData["location"]."/".$subdir;
}

$matDir = $imageData["outDir"]."/matout";
$altDir = $imageData["outDir"]."/".$imageData["name"];

mkdir($matDir);
mkdir($altDir);

// move the image into inDir
if (!move_uploaded_file($_FILES['galaxyImage']['tmp_name'], $imageData["file"]))
	returnProcessingState(false, "File could not be moved.");

/* generate a string that PHP will run. This version will use
 the SpArcFiRe program located at /home/wayne/bin/ */

// start with binary location
$optionString .= '/home/wayne/bin/SpArcFiRe ';

// if the image is a FITS, append FITS options
if ($_POST["isFits"]["convert-FITS"] == "true")
	$optionString .= " -convert-FITS ".($_POST["isFits"]["ignore-starmask"] ? "-ignore-starmask" : "")." ";

// add -web and directories

$optionString .= '-web '.$imageData["inDir"].' '.$imageData["pngDir"].' '.$imageData['outDir'];

// append SpArcFire execution options
foreach($_POST['mainOptions'] as $option => $value)
	$optionString .= ' -'.$option.' '.$value;

// run the script

$exitCode = 0;
$passArray = array();

exec($optionString." &>".$imageData['id'].".log", $passArray, $exitCode);

/* success conditions go past here. success is indicated by exit_code
 * if exit_code is equal to 0, this should indicate proper operation of
 * the SpArcFiRe script. */

// if running SpArcFire was successful, the exit_code should be 0
if ($exitCode)
	returnProcessingState(false, "Image could not be processed.", 
			array(
					"command" => $optionString
			));
else {
	

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
	
	/* generate associative array of image paths for displaying results
	   associating display name to filename */
	
	$images = array();
	
	foreach(preg_grep("/[^.]+\.png/", scandir($imageData["outDir"].'/'.$imageData['name'])) as $image){
		$suffix = array();
		preg_match('/[^-]+-[A-Z]?_+([^.]+)\.png/', $image, $suffix);
		$images[$suffix[1]] = '/process/'.$imageData["id"].'/outDir/'.$imageData["name"].'/'.$image;
	}
	
	$output_info = fopen($imageData["location"], "w");
	
	fwrite($output_info, json_encode(array(	
		"zip" => '/process/'.$imageData["id"].'/outDir/'.$zipName,
		"images" => $images
	)));
	
	returnProcessingState(true, "Image successfully processed.",
		array(
			"url" => "/results?query=".$imageData["id"]
		)
	);
	
}	
	/*// generate an array of <img /> using each image in the output directory
	
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
}*/
?>