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
</p>

<p><span id="showing-num"><?= count($titles) ?></span> / <?= $client->ratedlist->count ?>:</p>

<ul class="list">
	<?php include 'tpl.ratings.php'; ?>
</ul>

<style>
li.last-before-load {
	background-color: yellow;
}
</style>

<script>
window.onload = function() {
	const ul = document.querySelector('ul');
	const showing = document.querySelector('#showing-num');

	let cursor = '<?= $pager->cursor ?>';
	let loading = false;
	window.onscroll = async function(e) {
		const atBottom = document.documentElement.scrollTop + window.innerHeight > document.documentElement.offsetHeight - 20;
		if (!atBottom || loading) return;

		ul.lastElementChild.classList.add('last-before-load');

		loading = true;
		const rsp = await fetch('?cursor=' + cursor);
		cursor = rsp.headers.get('imdb-cursor');
		const html = await rsp.text();
		ul.innerHTML += html;
		showing.textContent = ul.childElementCount;

		loading = false;
	};
};
</script>
