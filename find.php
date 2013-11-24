<?php

if ( isset($_GET['q']) ) {
	$q = $_GET['q'];
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

	foreach ( $response->d as $object ) {
		if ( $object->id[0] == 't' ) {
			$uri = 'title/' . $object->id;
			$title = $object->l . ' (' . ( @$object->y ?: '?' ) . ')';
		}
		else {
			$uri = 'name/' . $object->id;
			$title = $object->l;
		}

		echo '<li>';
		echo '<a href="http://imdb.com/' . $uri . '">';
		echo $title;
		echo '</a>';
		echo '</li>';
	}
}

?>
<p><a href="index.html">Intersect here</a></p>

<form action>
	<p>Query: <input name=q value="<?= @$_GET['q'] ?>" autofocus /></p>
	<p><input type=submit></p>
</form>
