<?php

include $_SERVER["DOCUMENT_ROOT"].'/php/includes.php';

function input($name, $type, $value, $nameWrapper, $label='', $tooltip=''){
	echo '<div class="option '.$name.'" title="'.($tooltip != '' ? $tooltip : '').'">
		<label>'.($label != '' ? $label : $name).'</label>
		<input name="';
		$wrapped = $name;
		foreach ($nameWrapper as $wrapper){
			echo $wrapper.'[';
			$wrapped .= "]";
		}
		echo $wrapped;
		echo '" type="'.$type.'"
		value="'.(gettype($value) == 'boolean' ? ($value == True ? 1 : 0) : $value).'" />';
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

function createHelpTextGroup($group){
	foreach($group as $option){
		echo '<div class="option-help-listing">
				<header class="option-help-name">
					'.$option[0].'
				</header>
				<div class="option-help-text">';
		if (isset($option[4]))
			echo $option[4];
		else 
			echo "No help text is available for this.";
		echo '</div></div>';
	}
}

function createOptionValueGroup($group){
	foreach($group as $option){
		$name = isset($option[3]) && $option[3] != '' ? $option[3] : $option[0];
		echo '<span class="option-value-container '.$name.'">
				<span class="option-name">
					 '.$name.'
				</span>
				<span class="option-value">
					 '.($option[1] == "checkbox" ? ($option[2] == 1 ? 'Yes' : 'No') : $option[2]).'
				</span>
			</span>';
	}
}

$config = json_decode(file_get_contents("config.json"));
$levels = array("easy", "advanced", "expert");

?>
<!DOCTYPE html>
<html>
	<head>
		<?php include $_TEMPLATES."/header.php" ?>
	</head>
	<body>
		<?php include $_TEMPLATES."/nav.php"; ?>
		<div id="steps-container">
			<ul id="steps">
				<li class="step select-image active visited">
					select image
					<div class="arrow"></div>
				</li>
				<li class="step options">
					options
					<div class="arrow"></div>
				</li>
				<li class="step confirm">
					confirm
					<div class="arrow"></div>
				</li>
				<li class="step process">
					process
					<div class="arrow"></div>
				</li>
			</ul>
		</div>
		<div id="image-stats-container">
			<span id="image-stats-inner">
				<span id="image-stats-pre">
					image name
				</span>
				<span id="image-stats">
				</span>
			</span>
		</div>
		<div id="option-stats-container">
			<div id="option-stats-inner">
				<span id="option-stats-pre">
					processing options
				</span>
				<span id="option-stats-pre-nontitle">
					faded out values are default, unchanged values
				</span>
				<div id="option-stats">
					<section>
						<header>
							fits options
						</header>
						<?php createOptionValueGroup($config->isFits) ?>
					</section>
					<section>
						<header>
							SpArcFiRe image processing options
						</header>
						<section>
							<?php createOptionValueGroup($config->mainOptions->easy) ?>
						</section>
						<section>
							<header>
								advanced
							</header>
							<?php createOptionValueGroup($config->mainOptions->advanced)?>
						</section>
						<section>
							<header>
								expert
							</header>
							<?php createOptionValueGroup($config->mainOptions->expert)?>
						</section>
					</section>
				</div>
			</div>
		</div>
		<div id="content-container">
			<form method="POST" enctype="multipart/form-data" action="/process/index.php">
				<div class="content select-image active visited">
					<section id="galaxyImage">
						<span id="galaxy-image-text">
							Select an image of a galaxy for processing. Allowed extensions are (.png, .jpg, .fit and .fits).
						</span>
						<div class="warning">
							WARNING: Processing time scales roughly as O(N<sup>4</sup>), where N is the linear scale of your image, in pixels. A 400x400 image will take a few minutes; a 2000x2000 image could take hours. Please use discretion, and pre-shrink your image before submitting, if necessary.</span>
						</div>
						<div id="galaxyImage-container">
							<input type="file" name="galaxyImage" accept=".png,.jpeg,.fits"/>
						</div>
					</section>
					<div class="pageFlipper">
						<div class="nextPage">
							next &gt;&gt;
						</div>
					</div>
				</div>
				<div class="content options">
						<div id="options-help-button">
							options help &gt;&gt;
						</div>
						<section id="isFits" class="twoCol disabled">
							<header>
								.fits to .png processing options
							</header>
							<?php createFieldGroup($config->isFits, array("isFits")) ?>
						</section>
						<section id="mainOptions" class="">
							<header>
								SpArcFiRe image processing options
							</header>
							<?php foreach($levels as $level){
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
						<div class="pageFlipper">
							<div class="prevPage">
								&lt;&lt; back
							</div>
							<div class="nextPage">
								next &gt;&gt;
							</div>
						</div>
				</div>
			</form>
			<div class="content confirm">
				<span>The image will be processed with the above options. Note that processing options cannot be changed during processing. Proceed?</span>
				<div class="pageFlipper">
					<div class="prevPage">
						&lt;&lt; back
					</div>
					<div class="nextPage">
						submit &gt;&gt;
					</div>
				</div>
			</div>
			<div class="content process">
				<div id="proc-animation">
					<img src="proc.png" id="galaxy" alt="" />
				</div>
				<div id="text-container">
					<span id="procMsg">processing image...</span>
					<span id="procCrt">(this could take half a minute to two minutes depending upon the image; please be patient)</span>
				</div>
			</div>
			<div class="content options-help">
				<div id="options-back-button">
					&lt;&lt; back to options
				</div>
				<section>
					<header>
						.fits to .png options
					</header>
					<?php createHelpTextGroup($config->isFits) ?>
				</section>
				<section>
					<header>
						SpArcFiRe image processing options
					</header>
					<?php  foreach($levels as $level){
						echo '<section>'.($level != "easy" ? '<header>'.$level.'</header>' : '');
						createHelpTextGroup($config->mainOptions->$level);
						echo '</section>';
					} ?>
				</section>
			</div>
		</div>
		<?php include $_TEMPLATES."/footer.php" ?>
	</body>
</html>