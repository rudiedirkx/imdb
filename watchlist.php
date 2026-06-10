<?php

use rdx\imdb\Pager;

require __DIR__ . '/inc.bootstrap.php';

$cursor = $_GET['cursor'] ?? null;
$items = $client->getWatchlistTitles($pager = new Pager(limit: 200, cursor: $cursor));
// dd($items[0]);

if ($cursor) {
	header('imdb-cursor: ' . $pager->cursor);
	include 'tpl.list-items.php';
	exit;
}

$_title = 'IMDB watchlist';
include 'tpl.header.php';

?>
<p>
	<a href="find.php">Quicksearch</a>
	|
	<a href="intersect.php">Intersect</a>
	|
	<a href="ratings.php">Ratings</a>
</p>

<p><span id="showing-num"><?= count($items) ?></span> / <?= $client->watchlist->count ?>:</p>

<ul class="list">
	<?php include 'tpl.list-items.php'; ?>
</ul>

<?php include 'tpl.list-load.php'; ?>
