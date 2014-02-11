<?php

$results = '';

if ( isset($_GET['q']) ) {
	$q = strtolower(trim($_GET['q']));
	$q = str_replace(array("'"), '', $q);
	$q = preg_replace('/\W+/', '_', $q);

	while ( strlen($q) >= 2 ) {
		$url = 'http://sg.media-imdb.com/suggests/' . $q[0] . '/' . urlencode($q) . '.json';
		$jsonp = @file_get_contents($url);
		if ( $jsonp ) {
			break;
		}
		else {
			$q = substr($q, 0, -1);
		}
	}

	$jsonp = trim($jsonp, ' ;');
	$json = substr($jsonp, strlen($q) + 6, -1);
	$response = json_decode($json);

	$results .= '<ul>';
	foreach ( $response->d as $object ) {
		if ( $object->id[0] == 't' ) {
			$uri = 'title/' . $object->id;
			$title = $object->l . ' (' . ( @$object->y ?: '?' ) . ')';
		}
		else {
			$uri = 'name/' . $object->id;
			$title = $object->l;
		}

		$results .= '<li><a href="http://imdb.com/' . $uri . '">' . $title . '</a></li>';
	}
	$results .= '</ul>';
}

?>
<!doctype html>
<html>

<head>
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>IMDb quick find</title>
	<style>
	ul { font-size: 110%; line-height: 1.6; }
	</style>
</head>

<body>
	<p><a href="index.html">Intersect here</a></p>

	<?= $results ?>

	<form action>
		<p>Query: <input name=q value="<?= @$_GET['q'] ?>" autofocus /></p>
		<p><input type=submit></p>
	</form>

</body>

</html>
