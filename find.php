<?php

use rdx\imdb\Person;
use rdx\imdb\Title;

require __DIR__ . '/inc.bootstrap.php';

$html = '';
if ( isset($_GET['q']) ) {
	$results = $client->search($_GET['q']);
// dump($results);

	$html .= "<ul>\n";
	if ( !count($results) ) {
		$html .= "<li>0 results</li>\n";
	}
	foreach ( $results as $object ) {
		$html .= '<li>';
		if ($object->image) {
			$html .= '<img
				width="' . ($object->image->getWidthForHeight(50) ?? 50) . '"
				height="' . 50 . '"
				data-src="' . html($object->image->url) . '"
				style="float: left; border: solid 1px black; margin-right: .25em"
				onclick="this.src = this.dataset.src; this.onclick = null"
			/>';
		}
		if ($object instanceof Title) {
			$html .= '<a href="title.php?id=' . $object->id . '">' . html($object->name) . '</a>';
			$html .= ' (' . ($object->getYearLabel() ?? '?') . ')';
			$html .= '<br>[' . ($object->getTypeLabel() ?? '?') . '] ' . html($object->searchInfo);
		}
		elseif ($object instanceof Person) {
			$html .= '<a href="person.php?id=' . $object->id . '">' . html($object->name) . '</a>';
			$html .= '<br>[PERSON] ' . html($object->searchInfo);
		}
		else {
			$html .= '<a href="' . $object->getUrl() . '">' . html($object->getSearchResult()) . '</a>';
		}
		$html .= "</li>\n";
	}
	$html .= "</ul>\n";
}

?>
<!doctype html>
<html>

<head>
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="theme-color" content="#333" />
	<title>IMDb quick find</title>
	<style>
	* {
		box-sizing: border-box;
	}
	body, input, button {
		font-size: 20px;
		line-height: 1.3;
	}
	li {
		margin-bottom: 5px;
	}
	input {
		padding: 10px;
	}
	button {
		padding: 10px 25px;
		font-weight: bold;
		width: 100%;
	}
	</style>
</head>

<body>
	<p><a href="intersect.php">Intersect here</a></p>

	<?= $html ?>

	<form action>
		<p>Query: <input name="q" value="<?= @$_GET['q'] ?>" autocomplete="off" /></p>
		<p><button>Search</button></p>
	</form>

</body>

</html>
