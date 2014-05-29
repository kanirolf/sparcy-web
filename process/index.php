<?php include $_SERVER["DOCUMENT_ROOT"]."/php/includes.php";

if ($_SERVER["REQUEST_METHOD"] != "GET"){
	die();
} else if (!isset($_GET["id"])){
	header("Location: /");
	die();
} else if (!is_dir($_GET["id"])){
	header("Location: /results?id=".$_GET["id"]);
	die();
}

$status = json_decode(file_get_contents($_GET["id"]."/info.json"))->status;
if ($status == "processed" || $status == "failed") header("Location: /results?id=".$_GET["id"]);
?>
<!DOCTYPE html>
<html>
	<head>
	<script type="text/javascript">var queryID = '<?php echo $_GET["id"] ?>';</script>
	<?php include $_TEMPLATES."/header.php" ?>
	</head>
	<body>
		<?php include $_TEMPLATES."/nav.php"?>
		<div id="meta-container">
				<div id="meta-inner">
					<span id="id-label">Query ID</span> 
					<span id="id"><?php echo $_GET["id"]?></span>
				</div>
			</div>
		<div id="content-container">
			<div class="content process active">
				<div id="proc-animation">
					<img src="proc.png" id="galaxy" alt="" />
				</div>
				<div id="proc-anim-adj-container">
					<span id="procMsg"><?php if ($status == "processing") echo "still "; ?>processing image...</span>
					<span id="procCrt">(this could take half a minute to two minutes depending upon the image; please be patient)</span>
				</div>
				<div id="general-text-container">
					<?php if ($status == "preprocessed") {?>
						Your image is currently processing. If you wish to close this window, take note of the query ID up top; it can used to fetch the results when processing is finished by going on the <a href="/results">look up previous job</a> page. 
					<?php } else {?>
						Your image is currently processing; however, this page will not automatically redirect once the processing is done. When the job is done, the ID above can be looked up on the <a href="/results">lookup previous job</a> page.
				<?php }?></div>
			</div>
		</div>
		<?php include $_TEMPLATES."/footer.php"?>
	</body>
</html>