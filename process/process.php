<?php //include $_SERVER["DOCUMENT_ROOT"].'/php/includes.php';

ignore_user_abort(true);

function returnProcessingState($success, $status, $data=array()){
	$result = json_encode(array("success" => $success,"status" => $status,"data" => $data));
	echo $result;
	die();
}

/* processing
 * 
 * This script will:
 * 
 * 1 check for the ID variable
 * 
 * 	return a failure for a POST
 * 	redirect for anything else
 * 
 * 1 create a query string using aggregated options
 * 4 run the query string through the SpArcFiRe binary using the shell
 * 5 create a ZIP containing all files
 * 6 create a JSON file containing info on the query
 * The script will respond with a json object in all cases of failure 
 * and success, structured as:
 * 
 * {
 * 		"success" : bool,
 * 		"status" : str,
 * 		"data" : {
 * 			("key" : "value")*
 * 		}
 * }
 *
 */

// redirect non-POST requests

if ($_SERVER["REQUEST_METHOD"] != "POST"){
	header("Location: /");
	die();
}

// step 1: 
// 
// check that there is an id supplied, 
// 	
//	if not, call returnProcessingState with a false success
// 	if so, but no such query exists, redirect to equivalent result page

if (!isset($_POST["id"])){
	returnProcessingState(false, "No ID supplied.");
} else if(!is_dir($_POST["id"])){
	returnProcessingStatus(true, "Query does not exist.", array(
		"url" => "/results?id=".$_POST["id"]
	));
}

// step 2: 
//
// check that the image is not already processed. if so, redirect to
// equivalent result page. if the image is being processed, return a
// JSON response that is true without a URL; this shouldn't happen
// since the process/index.php page also checks that the file isn't 
// being processed or is already processed, but just in case...

$info = json_decode(file_get_contents($_POST["id"]."/info.json"));

if ($info->status == "processed" || $info->status == "failed"){
	returnProcessingStatus(true, "Query already processed.", array(
		"url" => "/results?id=".$_POST["id"]
	));
} else if ($info->status == "processing"){
		returnProcessingState(true, "This file is currently processing");
}


// step 2: import the imageData and options from the info.json file

$options = $info->options;
$imageData = $info->data;

$output_info = fopen($imageData->location."/info.json", "w");
fwrite($output_info, json_encode(array("status" => "processing")));
fclose($output_info);

// step 2:
//
// generate a string that PHP will run. This version will use
// the SpArcFiRe program located at /home/wayne/bin/


// start with binary location
$optionString .= 'export MCR_CACHE_ROOT=/tmp && /home/wayne/bin/SpArcFiRe ';

// if the image is a FITS, append FITS options
if ($imageData->ext == "fits" || $imageData->ext == "fit"){
	$optionString .= " -convert-FITS -p ";
	foreach(array("brightnessQuartileForASinhAlpha", "brightnessQuartileForASinhBeta", "asinhApplications")
		as $fitsOpt)
		$optionString .= $options->isFits->$fitsOpt." "; 
	foreach(array("compute-starmask", "ignore-starmask") as $fitsOpt)
		$optionString .= $options->isFits->$fitsOpt == "true" ? "-".$fitsOpt." " : " ";
}


// add -web and directories

$optionString .= '-web '.$imageData->inDir.' '.$imageData->pngDir.' '.$imageData->outDir;

// append SpArcFire execution options
foreach($options->mainOptions as $option => $value)
	$optionString .= ' -'.$option.' '.$value;

// step 3: run the script

$exitCode = 0;
$passArray = array();

exec($optionString." &>".$imageData->id."/error.log", $passArray, $exitCode);

// success conditions go past here. success is indicated by exit_code
// if exit_code is equal to 0, this should indicate proper operation of
// the SpArcFiRe script.

// if running SpArcFire was successful, the exit_code should be 0

if ($exitCode){
	$output_info = fopen($imageData->location."/info.json", "w");
	fwrite($output_info, json_encode(array(
		"status" => "failed",
		"log" => '/process/'.$imageData->id.'/error.log'
	)));
	fclose($output_info);

	returnProcessingState(false, "Image could not be processed.", 
			array(
					"url" => "/results?query=".$imageData->id
			));
} else {
	
	// step 4:
	//
	// create a zip for the files. the zip will be created in /outDir
	// as galaxy_[imageId]_data.zip with two folders inside: images/
	// for the images and tables/ for the comma/tab separated value
	// tables.
	
	$zipName = "galaxy_".$imageData->name."_data.zip";
	$files = new ZipArchive;
	
	$files->open($imageData->outDir."/".$zipName, ZipArchive::CREATE);
	
	foreach(array_diff(scandir($imageData->outDir."/".$imageData->name), array('.', '..')) as $file)
		$files->addFile($imageData->outDir."/".$imageData->name."/".$file, $file);
	
	$files->close();
	
	// generate associative array of image paths for displaying results
	// associating display name to filename
	
	$images = array();
	
	foreach(preg_grep("/[^.]+\.png/", scandir($imageData->outDir.'/'.$imageData->name)) as $image){
		$suffix = array();
		preg_match('/[^-]+-[A-Z]?_+([^.]+)\.png/', $image, $suffix);
		$images[$suffix[1]] = '/process/'.$imageData->id.'/outDir/'.$imageData->name.'/'.$image;
	}
	
	// step 5: create a json file containing information on the galaxy results
	
	$output_info = fopen($imageData->location."/info.json", "w");
	
	fwrite($output_info, json_encode(array(
	"status" => "processed",
	"zip" => '/process/'.$imageData->id.'/outDir/'.$zipName,
	"images" => $images
	)));
	
	fclose($output_info);
	
	returnProcessingState(true, "Image successfully processed.", array(
		"url" => "/results?id=".$imageData->id
	));

}
?>