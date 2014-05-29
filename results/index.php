<?php include $_SERVER["DOCUMENT_ROOT"].'/php/includes.php';
$isProcessed = file_exists($_ROOT."/process/".$_GET["id"]."/info.json");
$hasError = file_exists($_ROOT."/process/".$_GET["id"]."/error.log");
if ($isProcessed)
	$galaxyDetails = json_decode(file_get_contents($_ROOT."/process/".$_GET["id"]."/info.json"));
$status = $galaxyDetails->status;
if ($status == "processing" || $status == "preprocessed"){
	header("Location: /process?id=".$_GET["id"]);
	die();
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include $_TEMPLATES."/header.php" ?>
	</head>
	<body>
		<?php include $_TEMPLATES."/nav.php" ?>
		<?php if ($_GET["id"] != "") {
			if ($isProcessed){?>
			<div id="meta-container">
				<div id="meta-inner">
					<span id="id-label">Query ID</span> 
					<span id="id"><?php echo $_GET["id"]?></span>
					<?php if ($status == "processed") {?>
					<a id="download" href="<?php echo $galaxyDetails->zip ?>">
						download as .zip
					</a>
					<?php }?>
				</div>
			</div>
		<?php }}?>
		<div id="content-container">
			<?php if ($_GET["id"] != "") {?>
			<div class="content result active">
			<?php if ($status == "processed") {?>	
				<span id="result-info">These results can be accessed using the above ID and going to <a href="/results">the results page</a> or directly accessed using <a href="<?php echo "http://sparcfire.ics.uci.edu/results?query=".$_GET["id"] ?>">this link</a>.</span>
				<div id="resultImages">
					<?php foreach($galaxyDetails->images as $name => $image){
						echo '<figure class="resultImage">
							<img src="'.$image.'"/>
							<figcaption>'.$name.'</figcaption>
						</figure>';
					}?>
				</div>
			<?php } else if ($status == "failed"){ ?>
				<span id="error-before">The processing of the image failed. The log of execution follows:</span>
				<pre id="error-log">
					<?php echo file_get_contents($_ROOT.$galaxyDetails->log) ?>
				</pre>
			<?php } else { ?>
				No such result ID exists. Sorry.
			<?php } 
			} else { ?>
			<div class="content result-search active">
				<label id="result-search-label">Type in the ID of your query:</label>
				<form action="/results">
					<input type="text" id="result-search" name="id" value="ID goes here"></input>
					<input type="submit" id="result-search-submit" value="search"></input>
				</form>
			<?php } ?>
			</div>
		</div>
		<?php include $_TEMPLATES."/footer.php" ?>
	</body>
</html>