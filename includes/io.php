<?php
function write_header($file,$text){
   	if (!$handle = fopen($file, 'w')) {
   		echo "Cannot Open File ($file)";
		exit;
   	}

   	if (!fwrite($handle, $text)) {
    	echo "Cannot write to file ($file)";
      	exit;
   	}

   	//echo "Success, wrote ($text) to file ($file)<br />";

   	fclose($handle);
}
?>