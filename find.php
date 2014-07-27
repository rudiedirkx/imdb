<?php

$results = '';

if ( isset($_GET['q']) ) {
	$q = strtolower(trim($_GET['q']));
	$q = str_replace(array("'", '-'), '', $q);
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
	body, input, button {
		font-size: 20px;
		line-height: 1.8;
	}
	input {
		padding: 10px;
	}
	button {
		padding: 10px 25px;
		font-weight: bold;
	}
	</style>
</head>

<body>
	<p><a href="index.html">Intersect here</a></p>

	<?= $results ?>

	<form action>
		<p>Query: <input name=q value="<?= @$_GET['q'] ?>" /></p>
		<p><button>Search</button></p>
	</form>

</body>

</html>
