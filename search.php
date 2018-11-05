<?php

use rdx\jsdom\Node;

require __DIR__ . '/vendor/autoload.php';

?>
<? $intersect = intersect($in) ?>

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
				<td class="actor"><a href="http://www.imdb.com/name/<?= $actorCode ?>/"><?= html($actor) ?></a></td>
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
	$dom = Node::create($html);

	$title = $dom->query('h3[itemprop="name"]')->textContent;

	$table = $dom->query('table.cast_list');

	$actors = $characters = [];
	foreach ( $table->children() as $tr ) {
		$a = $tr->query('a[href^="/name/"]');
		if ( !$a ) continue;

		$href = $a['href'];
		preg_match('#/name/([^/]+)#', $href, $match);
		$id = $match[1];

		$tds = $tr->children();
		$actors[$id] = $tds[1]->textContent;
		$characters[$id] = $tds[3]->textContent;
	}

	return (object) compact('actors', 'characters');
}

function _clean( $text ) {
	return trim(preg_replace('#\s+#', ' ', str_replace('&nbsp;', ' ', strip_tags($text))));
}

function getHTML( $tt ) {
	if ( file_exists($file = __DIR__ . '/cache/' . $tt . '.html') && filemtime($file) > time() - 600 ) {
		return file_get_contents($file);
	}

	$url = 'http://www.imdb.com/title/' . $tt . '/fullcredits';
	$html = file_get_contents($url);
	@file_put_contents($file, $html);
	return $html;
}
