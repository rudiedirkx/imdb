<?php

require __DIR__ . '/inc.bootstrap.php';

// $auth = password_verify(VOTING_PASSWORD, $_COOKIE['imdb_voting_password'] ?? 'x');

$titles = $client->getTitleRatings();
// dump($client);

include 'tpl.header.php';

?>
<p>
	<a href="find.php">Quicksearch</a>
	|
	<a href="intersect.php">Intersect</a>
</p>

<ul class="list">
	<? foreach ($titles as $title): ?>
		<li>
			<img
				width="30"
				data-src="<?= html($title->image->url) ?>"
				onclick="this.src = this.dataset.src; this.onclick = null"
			/>
			<div class="text">
				<a href="title.php?id=<?= $title->id ?>"><?= html($title->name) ?></a>
				(<?= ($title->getYearLabel() ?? '?') ?>)
				<span class="rating rated">&#9734; <?= $title->userRating->rating ?></span>
				<br>
				(on <?= date('Y-m-d', $title->userRating->ratedOn) ?>)
			</div>
		</li>
	<? endforeach ?>
</ul>
