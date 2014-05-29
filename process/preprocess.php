<?php 

//include $_SERVER["DOCUMENT_ROOT"].'/php/includes.php';

function returnProcessingState($success, $status, $data=array()){
	$result = json_encode(array("success" => $success,"status" => $status,"data" => $data));
	echo $result;
	die();
}

/* preprocessing
 * 
 * This script will:
 * 
 * 1 check for the existence of the image file
 * 2 create a directory and subdirectories for the file
 * 3 move the file into that directory
 * 
 */

// redirect non-POST requests

if ($_SERVER['REQUEST_METHOD'] != 'POST'){
	header('Location: /');
	die();
}

// step 1: check for image file existence

if (!isset($_FILES["galaxyImage"]))
	returnProcessingState(false, 'There was an issue with uploading the image file.');

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

// step 2: create directory for the file

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

// step 3: move the image into inDir

if (!move_uploaded_file($_FILES['galaxyImage']['tmp_name'], $imageData["file"]))
	returnProcessingState(false, "File could not be moved.");

// fin: 

$status = fopen($imageData["location"].'/info.json', 'w');
fwrite($status, json_encode(array(		
	"status" => 'preprocessed',
	"data" => $imageData,
	"options" => $_POST
)));
fclose($status);

returnProcessingState(true, "Can move on to actual processing", array(
	"url" => '/process?id='.$imageData["id"]
));

?>