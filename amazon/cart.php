<?php
include('includes.php');

switch($_GET['op']){
	case 'add':
		if (!$_COOKIE['cartID']){
			$url = 'http://webservices.amazon.co.uk/onca/xml?Service=AWSECommerceService&SubscriptionId='
				. $subID
				. '&AssociateTag='
				. $assID
				. '&Operation=CartCreate&Item.1.ASIN='
				. $_GET['id']
				. '&Item.1.Quantity=1'
				. '&MergeCart=True'
				. '&ResponseGroup=Cart';
			$xml = simplexml_load_file($url);
			setcookie('cartID', (string)$xml->Cart->CartId, time()+3600*24*90, '/'); 
			setcookie('cartHMAC', (string)$xml->Cart->HMAC, time()+3600*24*90, '/');
			setcookie('cartURL', (string)$xml->Cart->PurchaseURL, time()+3600*24*90, '/');
		}else{/*ADD TO CART*/
			$url = 'http://webservices.amazon.co.uk/onca/xml?Service=AWSECommerceService&SubscriptionId='
				. $subID
				. '&AssociateTag='
				. $assID
				. '&Operation=CartAdd&CartId='
				. $_COOKIE['cartID']
				. '&HMAC='
				. $_COOKIE['cartHMAC']
				. '&Item.1.ASIN='
				. $_GET['id']
				. '&Item.1.Quantity=1'
				. '&ResponseGroup=Cart';
				$xml = simplexml_load_file($url);
		}
		setcookie('cartURL', (string)$xml->Cart->PurchaseURL, time()+3600*24*90, '/');
		include('header.php');
		//VIEW CART
		$subtotal = $xml->Cart->SubTotal->FormattedPrice;
		$i= 0;
		$item = $xml->Cart->CartItems->CartItem[$i];
		echo '<h3>Cart Total: '.$subtotal.'</h3>';
		echo "\n";
		while($item){
			echo 'ITEM '.($i+1).':'.$item->Title.' - '.$item->Price->FormattedPrice.'<br />';
			echo "\n";
			$i++;
			$item = $xml->Cart->CartItems->CartItem[$i];
		}
		break;
	case 'view':
			if (!$_COOKIE['cartID']){
				include('header.php');
				echo '<h3>Your Cart is Empty</h3>';
			}else{
				$url = 'http://webservices.amazon.co.uk/onca/xml?Service=AWSECommerceService&SubscriptionId='
					. $subID
					. '&AssociateTag='
					. $assID
					. '&Operation=CartGet&CartId='
					. $_COOKIE['cartID']
					. '&HMAC='
					. $_COOKIE['cartHMAC']
					. '&ResponseGroup=Cart';
					$xml = simplexml_load_file($url);
					setcookie('cartURL', (string)$xml->Cart->PurchaseURL, time()+3600*24*90, '/');
					$subtotal = $xml->Cart->SubTotal->FormattedPrice;
					$i= 0;
					$item = $xml->Cart->CartItems->CartItem[$i];
					include('header.php');
					echo '<h3>Cart Total: '.$subtotal.'</h3>';
					echo "\n";
					while($item){
						echo '<a href="/amazon/item/'.$item->ASIN.'">ITEM '.($i+1).':'.$item->Title.' - '.$item->Price->FormattedPrice.'</a><br />';
						$i++;
						$item = $xml->Cart->CartItems->CartItem[$i];
					}
				}
		break;
	case 'create':
		break;
	case 'delete':
			$url = 'http://webservices.amazon.co.uk/onca/xml?Service=AWSECommerceService&SubscriptionId='
					. $subID
					. '&AssociateTag='
					. $assID
					. '&Operation=CartClear&CartId='
					. $_COOKIE['cartID']
					. '&HMAC='
					. $_COOKIE['cartHMAC'];
			$xml = simplexml_load_file($url);
			setcookie('cartID', (string)$xml->Cart->CartId, time()-3600*24*90, '/'); 
			setcookie('cartHMAC', (string)$xml->Cart->HMAC, time()-3600*24*90, '/');
			setcookie('cartURL', (string)$xml->Cart->PurchaseURL, time()-3600*24*90, '/');
			include('header.php');
			echo '<h3>Cart Empty</h3>';
		break;
	case 'complete':
			//setcookie('cartID', (string)$xml->Cart->CartId, time()-3600*24*90); 
			//setcookie('cartHMAC', (string)$xml->Cart->HMAC, time()-3600*24*90);
			setcookie('cartClear', 'no', time()+3600*24*90, '/');
			include('header.php');
			echo'<h4><a href="'.$_COOKIE['cartURL'].'">Click Here To Complete Transaction with Amazon UK</a></h4>';
		break;
	default:
		break;
}
include('cart_footer.php');
include('footer.php');
?>