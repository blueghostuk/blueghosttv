<?php

include('includes.php');
include('header.php');

if ($_GET['page']){
	echo 'GOT PAGE '.$_GET['page'];
	$page = $_GET['page'];
	echo 'CURENT PAGE = '.$page.' , search = '.$_SESSION['search'];
	echo '<br /><a href="stemp.php?page='.($page+1).'">NEXT</a><br />';
	echo '<a href="stemp.php?page='.($page-1).'">PREVIOUS</a><br />';
}else{
	echo 'GOT NO PAGE, FIRST RUN<br />';
	$_SESSION['search'] = 'SEARCHVARS';
	$page = 0;
	echo '<a href="stemp.php?page='.($page+1).'">NEXT</a><br />';
	//echo '<a href="stemp.php?page='.($page-1).'">PREVIOUS</a>
}	

include('footer.php');
?>