<?php include $_SERVER["DOCUMENT_ROOT"].'/php/includes.php';?>
<!DOCTYPE html>
<html>
	<head>
		<?php include $_TEMPLATES."/header.php" ?>
	</head>
	<body>
		<?php include $_TEMPLATES."/nav.php" ?>
		<div class="content result">
			<div class="container">
				<?php 
					if(file_exists($_ROOT."/process/".$_GET["query"]."/info.json")){
						$galaxyDetails = json_decode(file_get_contents($_ROOT."/process/".$_GET["query"]."/info.json"));
				?>
				<header>
					Results of processing:
				</header>
				<a id="download" href="<?php echo $galaxyDetails->zip ?>">
					download results
				</a>
				<div id="resultImages">
					<?php foreach($galaxyDetails->images as $name => $image){
						echo '<figure class="resultImage">
							<img src="'.$image.'"/>
							<figcaption>'.$name.'</figcaption>
						</figure>';
					}?>
				</div>
				<?php } else if ($_GET["query"] != "") echo "No such result ID exists. Sorry."; 
						else echo "Sorry, but a result ID is needed to view a result."?>
			</div>
		</div>
	</body>
</html>