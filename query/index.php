<?php

include $_SERVER["DOCUMENT_ROOT"].'/php/includes.php';

function input($name, $type, $value, $nameWrapper, $label='', $tooltip=''){
	echo '<div class="option" title="'.($tooltip != '' ? $tooltip : '').'" id="'.$name.'">
		<label>'.($label != '' ? $label : $name).'</label>
		<input name="';
		$wrapped = $name;
		foreach ($nameWrapper as $wrapper){
			echo $wrapper.'[';
			$wrapped .= "]";
		}
		echo $wrapped;
		echo '"type="'.$type.'"
		value="'.(gettype($value) == 'boolean' ? ($value == True ? 1 : 0) : $value).'" />';
		/*if ($tooltip != '')
			echo '<div class="tooltip">
				'.($tooltip != '' ? $tooltip : '').'
			</div>';*/
		echo '</div>';
}

function createFieldGroup($group, $groupWrapper){
	$arrLen = count($group);
	for($i = 0; $i < 2; $i++){
		echo '<div class="align-'.($i == 1 ? 'left' : 'right').'">';
		foreach (array_slice($group, $i * $arrLen / 2, ($i + 1) * $arrLen / 2) as $member){
			input($member[0], $member[1], $member[2], $groupWrapper, isset($member[3]) ? $member[3] : '',
				isset($member[4]) ? $member[4] : '');
		}
		echo '</div>';
	}
}					

$config = json_decode(file_get_contents("config.json"));

?>
<!DOCTYPE html>
<html>
	<head>
		<?php include $_TEMPLATES."/header.php" ?>
	</head>
	<body>
		<?php include $_TEMPLATES.'/nav.php' ?>
		<div class="content query active">
			<form method="POST" enctype="multipart/form-data" action="/process/index.php">
				<div id="galaxyImage">
					<label id="galaxyImage">Select your file</label>
					<input type="file" name="galaxyImage" />
				</div>
				<section id="isFits" class="twoCol disabled">
					<header>
						.fits to .png processing options
					</header>
					<?php createFieldGroup($config->isFits, array("isFits")) ?>
				</section>
				<section id="mainOptions" class="disabled">
					<header>
						SpArcFiRe image processing options
					</header>
					<?php $levels = array("easy", "advanced", "expert");
					foreach($levels as $level){
						echo '<section id="'.$level.'" class="twoCol level">';
						if ($level != "easy")
							echo '<div class="level-header">
								<span class="expander open">+</span><span class="expander close">-</span>
								<header>'.$level.'</header>
						</div>';
						createFieldGroup($config->mainOptions->$level, array("mainOptions"));
						echo '</section>';
					} ?>
				</section>
				<section id="submit" class="disabled">
					<button>Submit</button>
				</section>
			</form>
		</div>
		<div class="content process">
			<span id="procMsg">processing image...</span>
			<span id="procCrt">(this could take half a minute to two minutes depending upon the image; please be patient)</span>
		</div>
	</body>
</html>