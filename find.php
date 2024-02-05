<?php

use rdx\imdb\Person;
use rdx\imdb\Title;

require __DIR__ . '/inc.bootstrap.php';

$html = '';
if ( isset($_GET['q']) ) {
	$results = $client->searchGraphql($_GET['q']);
// dump($results);

	$html .= "<ul class='list'>\n";
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
				onclick="this.src = this.dataset.src; this.onclick = null"
			/>';
		}
		if ($object instanceof Title) {
			$html .= '<a href="title.php?id=' . $object->id . '">' . html($object->name) . '</a>';
			$html .= ' (' . ($object->getYearLabel() ?? '?') . ')';
			if ($object->rating) {
				$html .= ' <span class="rating ' . (($object->userRating->rating ?? 0) ? 'rated' : '') . '">';
				$html .= '&#9734; ' . ($object->userRating->rating ?? '?') . ' / ' . $object->rating;
				$html .= '</span>';
			}
			$html .= '<br>[' . ($object->getTypeLabel() ?? '?') . '] ' . html($object->getSearchInfo());
		}
		elseif ($object instanceof Person) {
			$html .= '<a href="person.php?id=' . $object->id . '">' . html($object->name) . '</a>';
			$html .= '<br>[PERSON] ' . html($object->getSearchInfo());
		}
		else {
			$html .= '<a href="' . $object->getUrl() . '">' . html($object->getSearchResult()) . '</a>';
		}
		$html .= "</li>\n";
	}
	$html .= "</ul>\n";
}

include 'tpl.header.php';

?>
<style>
input {
	padding: 10px;
}
button {
	padding: 10px 25px;
	font-weight: bold;
	width: 100%;
}
</style>

<p>
	<a href="intersect.php">Intersect</a>
	|
	<a href="ratings.php">Ratings</a>
</p>

<?= $html ?>

<form action>
	<p>Query: <input name="q" value="<?= @$_GET['q'] ?>" autocomplete="off" /></p>
	<p><button>Search</button></p>
</form>
