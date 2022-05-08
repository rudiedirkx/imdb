<?php

require __DIR__ . '/inc.bootstrap.php';

$person = $client->getGraphqlPerson($_GET['id'] ?? '');
if (!$person) exit("ID not found");
// dump($person);

?>
<title><?= html($person->name) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="theme-color" content="#333" />

<h1>
	<?= html($person->name) ?>
	(<?= $person->birthYear ?? '?' ?>)
</h1>
<p>
	<a href="<?= html($person->getUrl()) ?>">Open in IMDB</a>
</p>

<ul>
	<? foreach (array_slice($person->credits, 0, 5) as $actor): ?>
		<li>
			[<?= html($actor->title->getTypeLabel()) ?>]
			<a href="title.php?id=<?= html($actor->title->id) ?>"><?= html($actor->title->name) ?></a>
			(<?= $actor->title->getYearLabel() ?? '?' ?>)
			-
			<?= html($actor->character->name ?? '') ?>
		</li>
	<? endforeach ?>
</ul>
