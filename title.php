<?php

require __DIR__ . '/inc.bootstrap.php';

$title = $client->getGraphqlTitle($_GET['id'] ?? '');
if (!$title) exit("ID not found");
// dump($title);

$validVotedBefore = password_verify(VOTING_PASSWORD, $_COOKIE['imdb_voting_password'] ?? 'x');

if (isset($_POST['watchlist'])) {
	if ($validVotedBefore || password_verify($_POST['password'] ?? 'x', VOTING_PASSWORD)) {
		setcookie('imdb_voting_password', password_hash(VOTING_PASSWORD, PASSWORD_DEFAULT), 0);
		$logged = file_put_contents(
			VOTING_LOG_FILE,
			date('Y-m-d H:i:s') . ' - ' . ($_SERVER['REMOTE_ADDR'] ?? '?') . ' - ' . $title->id . ' - watchlist -> ' . intval($_POST['watchlist']) . "\n",
			FILE_APPEND
		);
		if ($logged) {
			$_POST['watchlist'] ? $client->addTitleToWatchlist($title->id) : $client->removeTitleFromWatchlist($title->id);
		}
	}
	exit('OK');
}

if (isset($_POST['rating'])) {
	if ($validVotedBefore || password_verify($_POST['password'] ?? 'x', VOTING_PASSWORD)) {
		setcookie('imdb_voting_password', password_hash(VOTING_PASSWORD, PASSWORD_DEFAULT), 0);
		$logged = file_put_contents(
			VOTING_LOG_FILE,
			date('Y-m-d H:i:s') . ' - ' . ($_SERVER['REMOTE_ADDR'] ?? '?') . ' - ' . $title->id . ' - ' . ($title->userRating->rating ?? '_') . ' -> ' . $_POST['rating'] . "\n",
			FILE_APPEND
		);
		if ($logged) {
			$client->rateTitle($title->id, $_POST['rating']);
		}
	}
	exit('OK');
}

$inWatchlist = $client->titleInWatchlist($title->id);

$_title = $title->name;
include 'tpl.header.php';

?>
<style>
[data-watchlist] {
	font-weight: bold;
	color: red;
}
[data-watchlist="1"] {
	color: green;
}
</style>

<h1>
	<a href="find.php">&lt;</a>
	<?= html($title->name) ?>
	(<?= $title->getYearLabel() ?? 'year?' ?>)
</h1>
<p>
	<?= html($title->getTypeLabel()) ?> |
	<? if ($title->originalName): ?>
		&quot;<?= html($title->originalName) ?>&quot; |
	<? endif ?>
	<? if ($title->duration): ?>
		<?= $title->getDurationLabel() ?> |
	<? endif ?>
	<a href="<?= html($title->getUrl()) ?>">Open in IMDB</a> |
	<button data-watchlist="<?= (int) $inWatchlist ?>">WL</button> |
	<button id="rate"><?= $title->userRating->rating ?? '?' ?></button> / <?= $title->rating ?? 'rating?' ?> (<?= $title->ratings !== null ? number_format($title->ratings, 0, '.', '_') : '?' ?>)
</p>
<p style="display: flex">
	<? if ($title->image): ?>
		<img
			width="50"
			height="<?= $title->image->getHeightForWidth(50) ?? 50 ?>"
			data-src="<?= html($title->image->url) ?>"
			style="border: solid 1px black; margin-right: .5em"
			onclick="this.src = this.dataset.src; this.onclick = null"
		/>
	<? endif ?>
	<span>
		<? if (count($title->genres)): ?>
			<?= html(implode(', ', $title->genres)) ?> |
		<? endif ?>
		<?= html($title->plot ?? 'plot?') ?>
	</span>
</p>
<ul>
	<? foreach (array_slice($title->actors, 0, 20) as $actor): ?>
		<li>
			<a href="person.php?id=<?= html($actor->person->id) ?>"><?= html($actor->person->name) ?></a>
			-
			<?= html($actor->character->name ?? '') ?>
			<?= get_age($actor->person, $title) ?>
		</li>
	<? endforeach ?>
</ul>

<script>
document.querySelector('[data-watchlist]').addEventListener('click', function(e) {
	e.preventDefault();

	const add = Number(!parseInt(this.dataset.watchlist));

	const data = new FormData;
	data.set('watchlist', add);

	<? if (!$validVotedBefore): ?>
		const pwd = prompt("What's the password?", '');
		if (pwd == null || pwd == '') return;
		data.set('password', pwd);
	<? endif ?>

	fetch(new Request(location.href), {
		method: 'post',
		body: data,
	}).then(rsp => location.reload());
});
document.querySelector('#rate').addEventListener('click', function(e) {
	e.preventDefault();

	const rating = prompt("What's the new rating?", '');
	if (rating == null || !rating.match(/^(1|2|3|4|5|6|7|8|9|10)$/)) return;

	const data = new FormData;
	data.set('rating', rating);

	<? if (!$validVotedBefore): ?>
		const pwd = prompt("What's the password?", '');
		if (pwd == null || pwd == '') return;
		data.set('password', pwd);
	<? endif ?>

	fetch(new Request(location.href), {
		method: 'post',
		body: data,
	}).then(rsp => location.reload());
});
</script>
