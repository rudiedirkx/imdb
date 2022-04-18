<?php

require __DIR__ . '/inc.bootstrap.php';

$title = IMDB_AT_MAIN ? $client->getGraphqlTitle($_GET['id'] ?? '') : $client->getTitle($_GET['id'] ?? '');
// dump($title);

?>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="theme-color" content="#333" />

<h1>
	<?= html($title->name) ?>
	(<?= $title->year ?>)
</h1>
<p>
	<a href="<?= html($title->getUrl()) ?>">Open in IMDB</a> |
	<?= $title->userRating->rating ?? '?' ?> / <?= $title->rating ?>
</p>
<p><?= html($title->plot) ?></p>
<ul>
	<? foreach (array_slice($title->actors, 0, 5) as $actor): ?>
		<li><?= html($actor->person->name) ?> - <?= html($actor->character->name ?? '') ?></li>
	<? endforeach ?>
</ul>
