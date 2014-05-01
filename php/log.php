<?php

/* log accesses to SpArcFire, including data about IP, access time
 * and access path, to a given file. */

/* this path is relative to DOCUMENT_ROOT, please only edit this, 
 * even if the below is three lines: */

$logfileLocation = "/access.log";

// as MC Hammer says...
$accessLog = file_get_contents($_ROOT.$logfileLocation);
$accessLog .= $_SERVER["REMOTE_ADDR"].' '.date("d-M-Y H:i:s", $_SERVER["REQUEST_TIME"]).' '.$_SERVER["REQUEST_URI"]."\n";
file_put_contents($_ROOT.$logfileLocation , $accessLog);
?>
