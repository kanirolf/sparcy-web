<?php include $_SERVER["DOCUMENT_ROOT"].'/php/includes.php';
ignore_user_abort(true);

function returnProcessingState($success, $status, $data=array()){
	$result = json_encode(array("success" => $success,"status" => $status,"data" => $data));
	echo $result;
	setcookie(session_name(), '', time() - 3600);
	die();
}

/* processing
 * 
 * This script will:
 * 
 * 1 check for the imageData session variable
 * 
 * 	return a failure for a POST
 * 	redirect for anything else
 * 
 * 2 create a query string using aggregated options
 * 3 run the query string through the SpArcFiRe binary using the shell
 * 4 create a ZIP containing all files
 * 5 create a JSON file containing info on the query
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

// step 1: import the imageData from the SESSION

if (!isset($_SESSION["imageData"]))
	if($_SERVER["REQUEST_METHOD"] == "POST")
		returnProcessingState(false, "There is no associated image data for this session.");
	else {
		header("Location: /");
		die();
	}

$imageData = $_SESSION["imageData"];

// step 2:
//
// generate a string that PHP will run. This version will use
// the SpArcFiRe program located at /home/wayne/bin/

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

// step 3: run the script

$exitCode = 0;
$passArray = array();

exec($optionString." &>".$imageData['id']."/error.log", $passArray, $exitCode);

// success conditions go past here. success is indicated by exit_code
// if exit_code is equal to 0, this should indicate proper operation of
// the SpArcFiRe script.

// if running SpArcFire was successful, the exit_code should be 0

if ($exitCode)
	returnProcessingState(false, "Image could not be processed.", 
			array(
					"url" => "/results?query=".$imageData["id"],
					"query" => $optionString
			));
else {
	
	// step 4:
	//
	// create a zip for the files. the zip will be created in /outDir
	// as galaxy_[imageId]_data.zip with two folders inside: images/
	// for the images and tables/ for the comma/tab separated value
	// tables.
	
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
	
	// generate associative array of image paths for displaying results
	// associating display name to filename
	
	$images = array();
	
	foreach(preg_grep("/[^.]+\.png/", scandir($imageData["outDir"].'/'.$imageData['name'])) as $image){
		$suffix = array();
		preg_match('/[^-]+-[A-Z]?_+([^.]+)\.png/', $image, $suffix);
		$images[$suffix[1]] = '/process/'.$imageData["id"].'/outDir/'.$imageData["name"].'/'.$image;
	}
	
	// step 5: create a json file containing information on the galaxy results
	
	$output_info = fopen($imageData["location"]."/info.json", "w");
	
	fwrite($output_info, json_encode(array(
	"zip" => '/process/'.$imageData["id"].'/outDir/'.$zipName,
	"images" => $images
	)));
	
	fclose($output_info);
	
	returnProcessingState(true, "Image successfully processed.");

}
?>