<?php
function serverRealPath($path){
	// Returns this path relative to the DOCUMENT_ROOT
	return '/'.implode(array_slice(explode('/', realpath($path)), 3));
}
?>