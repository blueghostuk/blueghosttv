<?php
		if ( ( $_COOKIE['cartID'] && !$_COOKIE['cartClear'] ) || $_GET['op'] == "add"){
			echo'<h4><a href="/amazon/cart/delete/">Delete Cart</a></h4>';
			echo "\n";
			echo'<h4><a href="/amazon/cart/complete/">Complete Cart</a></h4>';
			echo "\n";
		}	
?>