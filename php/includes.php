<?php
$_ROOT = $_SERVER["DOCUMENT_ROOT"];
$_TEMPLATES = $_ROOT.'/templates';

include 'functions.php';

// run sessions before logging, in case you wanted to have log based on session vars.
include 'sessions.php';

include 'log.php';

?>