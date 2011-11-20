<!doctype html>
<html>

<head>
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
	$notIn = isset($_GET['not-in']) ? (array)$_GET['not-in'] : array();

	$intersect = intersect($in);

	?>

	<?if( $intersect->intersects ):?>
		<table border=1 class="results intersect-results">
		<tr>
			<td></td>
			<?foreach( $intersect->titles AS $code => $title ):?>
				<td class="title"><?=$title?></td>
			<?endforeach?>
		</tr>
		<?foreach( $intersect->intersects AS $actorCode => $actor ):?>
			<tr>
				<td class="actor"><?=$actor?></td>
				<?foreach( $intersect->titles AS $titleCode => $title ):?>
					<td class="character"><?=$intersect->casts[$titleCode]->characters[$actorCode]?></td>
				<?endforeach?>
			</tr>
		<?endforeach?>
		</table>
	<?else:?>
		<p>No results...</p>
	<?endif?>

	<?php

}

?>
</body>

</html>
<?php


function intersect( $in, $notIn = array() ) {
	$errors = array();
	$titles = array();
	$casts = array();
	$intersects = false;

	foreach ( $in AS $tt ) {
		$title = '';
		$cast = getCast($tt, $title);
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
	$url = 'http://www.imdb.com/title/'.$tt.'/fullcredits';
	$html = file_get_contents($url);

	if ( preg_match('#<h1>.*?<a[^>]*>([^<]+)</a>.*?</h1>#is', $html, $match) ) {
		$title = str_replace('&#x22;', '', $match[1]);
	}

	if ( preg_match('#name="cast".*?<table[^>]*>(.*?)</table>#is', $html, $match) ) {
		$table = $match[1];

		preg_match_all('#href="/name/(nm\d+)/"[^>]*>([^<]+)</a>#i', $table, $matches);
		$actors = array_combine($matches[1], $matches[2]);

		preg_match_all('#<td class="char">(.*?)(?:\(|</td)#is', $table, $matches);
		$characters = array_combine(array_keys($actors), array_map('strip_tags', $matches[1]));

		return (object)compact('actors', 'characters');
	}
}


