<?php

header('Content-type: text/html; charset=utf8');

?>
<!doctype html>
<html>

<head>
	<meta charset="utf-8" />
	<title>Intersect results</title>
	<style>
	table {
		border-collapse: collapse;
	}
	td, th {
		padding: 4px 7px;
		border: solid 1px #999;
	}
	.title, .actor {
		font-weight: bold;
	}
	</style>
</head>

<body>
<?php

if ( isset($_GET['in']) ) {

	$in = array_unique((array)$_GET['in']);
	$notIn = isset($_GET['not_in']) ? (array)$_GET['not_in'] : array();

	$intersect = intersect($in, $notIn);
// var_dump($intersect);

	?>

	<? if ($intersect->intersects): ?>
		<table border=1 class="results intersect-results">
			<tr>
				<td></td>
				<? foreach ($intersect->titles as $code => $title): ?>
					<td class="title"><?= html($title) ?></td>
				<? endforeach ?>
			</tr>
			<? foreach ($intersect->intersects as $actorCode => $actor): ?>
				<tr>
					<td class="actor"><?= html($actor) ?></td>
					<? foreach ($intersect->titles as $titleCode => $title): ?>
						<td class="character"><?= html($intersect->casts[$titleCode]->characters[$actorCode]) ?></td>
					<? endforeach ?>
				</tr>
			<? endforeach ?>
		</table>
	<? else: ?>
		<p>No results...</p>
	<? endif ?>

	<?php

}

?>
</body>

</html>
<?php

function html( $html ) {
	return htmlspecialchars($html, ENT_COMPAT, 'UTF-8');
}

function intersect( $in, $notIn = array() ) {
	$errors = array();
	$titles = array();
	$casts = array();
	$intersects = false;

	foreach ( $in AS $tt ) {
		$title = '';
		$cast = getCast($tt, $title);
// print_r($cast);
		$titles[$tt] = $title;
		$casts[$tt] = $cast;

		if ( $cast ) {
			// first title
			if ( false === $intersects ) {
				$intersects = $cast->actors;
			}
			// 2nd..n title
			else {
				// do intersect
				$intersects = array_intersect_key($intersects, $cast->actors);
				// any candidates left?
				if ( !$intersects ) {
					// nope -- don't check the rest
					break;
				}
			}
		}
		else {
			$errors[$tt] = $title;
		}
	}

	return (object)compact('errors', 'titles', 'intersects', 'casts');
}

function getCast( $tt, &$title = '' ) {
	$html = getHTML($tt);

	if ( preg_match('#<h3 itemprop="name">[\s\S]+?</h3>#i', $html, $match) ) {
		$title = _clean($match[0]);
// var_dump($title);
	}

	if ( preg_match('#<table class="cast_list">([\s\S]+?)</table>#i', $html, $match) ) {
		$table = $match[1];

		// preg_match_all('#itemprop="actor"[^<]+([\s\S]+?)</td>#', $table, $matches);
		// $actors = array_map(

		preg_match_all('#itemprop="actor"[\s\S]+?href="/name/(nm\d+)[^>]+>([\s\S]+?)</a>#i', $table, $matches);
		$ids = array_map('_clean', $matches[1]);
		$actors = array_map('_clean', $matches[2]);
// print_r($ids);

		$actors = array_combine($ids, $actors);
// print_r($actors);

		preg_match_all('#<td class="character">([\s\S]+?)(?:\(|/ \.|</td)#i', $table, $matches);
		$characters = array_map('_clean', $matches[1]);
		$characters = array_combine($ids, $characters);
// print_r($characters);

		return (object)compact('actors', 'characters');
	}
}

function _clean( $text ) {
	return trim(preg_replace('#\s+#', ' ', str_replace('&nbsp;', ' ', strip_tags($text))));
}

function getHTML( $tt ) {
	$file = __DIR__ . '/cache/' . $tt . '.html';
	$fromCache = file_exists($file) && filemtime($file) > strtotime('-1 day');
	if ( !$fromCache ) {
		$url = 'http://www.imdb.com/title/' . $tt . '/fullcredits';
		file_put_contents($file, file_get_contents($url));
	}

	return file_get_contents($file);
}
