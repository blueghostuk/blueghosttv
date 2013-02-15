<?php
//$debug = true;
if ($debug){
	echo '<br /><br /><h3>Debugging Info:</h3>';
	echo "\n";
	echo "<h4>COOKIE_cartID = ".$_COOKIE['cartID']."</h4>\n";
	echo "<h4>COOKIE_cartHMAC = ".$_COOKIE['cartHMAC']."</h4>\n";
	echo "<h4>COOKIE_cartClear = ".$_COOKIE['cartClear']."</h4>\n";
	echo "<h4>COOKIE_cartURL = ".$_COOKIE['cartURL']."</h4>\n";
	echo "<h4>URL = ".$url."</h4>\n";
	echo "<h4>DEBUG TEXT = ".$debug_text."</h4>";
	echo '<div class="debug">'.$xml->asXML().'</div>';
	echo "\n";
}
?>
</div>
<div id="footer" align="center">
| Data supplied from Amazon UK | All data is subject to change and cannot be guaranteed |</div>
<?php
	include('../footer.php');
?>
</body>
</html>