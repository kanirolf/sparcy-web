<?php

include $_SERVER["DOCUMENT_ROOT"].'/php/includes.php';

function input($name, $type, $value, $nameWrapper='', $label=''){
	echo '<label id="'.$name.'">
	<p>'.($label == '' ? $name : $label).'</p>
	<input name="'.($nameWrapper == '' ? $name : $nameWrapper.'['.$name.']').'"
	type="'.$type.'", value="'.(gettype($value) == 'boolean' ? ($value == True ? 1 : 0) : $value ).'" id="'.$name.'"/>
	</label>';
}

function createFieldGroup($group, $groupName){
	$arrLen = count($group->$groupName);
	for($i = 0; $i < 2; $i++){
		echo '<div class="align-'.($i == 1 ? 'left' : 'right').'">';
		foreach (array_slice($group->$groupName, $i * $arrLen / 2, ($i + 1) * $arrLen / 2) as $member){
			input($member[0], $member[1], $member[2], $groupName);
		}
		echo '</div>';
	}
}					

$config = json_decode(file_get_contents("config.json"));

?>
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
		<section class="query step active">
			<form method="POST" enctype="multipart/form-data" action="/process/index.php">
				<label id="galaxyImage">
					<p>Select your file</p>
					<input type="file" name="galaxyImage" />
				</label>
				<section id="isFits" class="twoCol disabled">
					<header>
						.fits to .png processing options
					</header>
					<?php createFieldGroup($config, 'isFits') ?>
					<input type="input" name="isFits[convert-FITS]" value="false" id="isFits" />
				</section>
				<section id="mainOptions" class="twoCol disabled">
					<header>
						SpArcFiRe image processing options
					</header>
					<?php createFieldGroup($config, 'mainOptions') ?>
				</section>
				<section id="submit" class="disabled">
					<button>Submit</button>
				</section>
			</form>
		</section>
		<section class="processing step">
			<span id="procMsg">processing image...</span>
			<span id="procCrt">(this could take half a minute to two minutes depending upon the image; please be patient)</span>
		</section>
		<section class="results step">
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