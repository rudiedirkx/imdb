<?php

if ( isset($_GET['in']) ) {
echo '<pre>';
print_r($_GET);
	$in = (array)$_GET['in'];
	$notIn = isset($_GET['not-in']) ? (array)$_GET['not-in'] : array();

	$_GET['titles'] = implode(',', $in);
	include '../xml/imdb.php';

exit;
}


