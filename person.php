<?php

require __DIR__ . '/inc.bootstrap.php';

$person = $client->getGraphqlPerson($_GET['id'] ?? '');
if (!$person) exit("ID not found");
// dump($person);

$_title = $person->name;
include 'tpl.header.php';

?>
<h1>
	<a href="find.php">&lt;</a>
	<?= html($person->name) ?>
	(<?= $person->birthYear ?? '?' ?>)
</h1>
<p style="display: flex">
	<? if ($person->image): ?>
		<img
			width="80"
			height="<?= $person->image->getHeightForWidth(80) ?? 80 ?>"
			data-src="<?= html($person->image->url) ?>"
			style="border: solid 1px black; margin-right: .5em"
			onclick="this.src = this.dataset.src; this.onclick = null"
		/>
	<? endif ?>
	<span><a href="<?= html($person->getUrl()) ?>">Open in IMDB</a></span>
</p>

<ul>
	<? foreach ($person->credits as $actor): ?>
		<li>
			[<?= html($actor->title->getTypeLabel()) ?>]
			<a href="title.php?id=<?= html($actor->title->id) ?>"><?= html($actor->title->name) ?></a>
			(<?= $actor->title->getYearLabel() ?? '?' ?>)
			<?= get_age($person, $actor->title) ?>
			<? if ($actor->title->rating): ?>
				<span class="rating <?= ($actor->title->userRating->rating ?? 0) ? 'rated' : '' ?>">
					&#9734; <?= $actor->title->userRating->rating ?? '?' ?> / <?= $actor->title->rating ?>
				</span>
			<? endif ?>
			-
			<?= html($actor->character->name ?? '') ?>
		</li>
	<? endforeach ?>
</ul>

<?php include 'tpl.search.php'; ?>
