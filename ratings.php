<?php

use rdx\imdb\Pager;

require __DIR__ . '/inc.bootstrap.php';

// $auth = password_verify(VOTING_PASSWORD, $_COOKIE['imdb_voting_password'] ?? 'x');

$cursor = $_GET['cursor'] ?? null;
$titles = $client->getRatedTitles($pager = new Pager(limit: 100, cursor: $cursor));
// dump($client);
// $titles = [...$titles, ...$client->getTitleRatings(page: 2)];

if ($cursor) {
	header('imdb-cursor: ' . $pager->cursor);
	include 'tpl.ratings.php';
	exit;
}

$_title = 'IMDB ratings';
include 'tpl.header.php';

?>
<p>
	<a href="find.php">Quicksearch</a>
	|
	<a href="intersect.php">Intersect</a>
	|
	<a href="watchlist.php">Watchlist</a>
</p>

<p><span id="showing-num"><?= count($titles) ?></span> / <?= $client->ratedlist->count ?>:</p>

<ul class="list">
	<?php include 'tpl.ratings.php'; ?>
</ul>

<?php include 'tpl.list-load.php'; ?>
