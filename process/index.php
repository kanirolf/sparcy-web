<?php
if ($_SERVER['HTTP_REFERER'] == "")
	header("Location: /");
else
	header("Location: ".$_SERVER['HTTP_REFERER']);
die();
?>