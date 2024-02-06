<?php

require __DIR__ . '/inc.bootstrap.php';

if (isset($_GET['id'], $_GET['watchlist'])) {
	$inWatchlist = $client->titleInWatchlist($_GET['id']);
	header('Content-type: application/json; charset=utf-8');
	exit(json_encode([
		'watchlist' => $inWatchlist,
	]));
}

$title = $client->getGraphqlTitle($_GET['id'] ?? '');
if (!$title) exit("ID not found");
// dump($title);

$validVotedBefore = password_verify(VOTING_PASSWORD, $_COOKIE['imdb_voting_password'] ?? 'x');

if (isset($_POST['watchlist'])) {
	if ($validVotedBefore || password_verify($_POST['password'] ?? 'x', VOTING_PASSWORD)) {
		setcookie('imdb_voting_password', password_hash(VOTING_PASSWORD, PASSWORD_DEFAULT), strtotime('+6 months'));
		$logged = file_put_contents(
			VOTING_LOG_FILE,
			date('Y-m-d H:i:s') . ' - ' . ($_SERVER['REMOTE_ADDR'] ?? '?') . ' - ' . $title->id . ' - watchlist -> ' . intval($_POST['watchlist']) . "\n",
			FILE_APPEND
		);
		if ($logged) {
			$_POST['watchlist'] ? $client->addTitleToWatchlist($title->id) : $client->removeTitleFromWatchlist($title->id);
			header('Content-type: application/json; charset=utf-8');
			exit(json_encode([
				'watchlist' => (bool) $_POST['watchlist'],
			]));
		}
	}
	exit('NOK');
}

if (isset($_POST['rating'])) {
	if ($validVotedBefore || password_verify($_POST['password'] ?? 'x', VOTING_PASSWORD)) {
		setcookie('imdb_voting_password', password_hash(VOTING_PASSWORD, PASSWORD_DEFAULT), strtotime('+6 months'));
		$logged = file_put_contents(
			VOTING_LOG_FILE,
			date('Y-m-d H:i:s') . ' - ' . ($_SERVER['REMOTE_ADDR'] ?? '?') . ' - ' . $title->id . ' - ' . ($title->userRating->rating ?? '_') . ' -> ' . $_POST['rating'] . "\n",
			FILE_APPEND
		);
		if ($logged) {
			$client->rateTitle($title->id, $_POST['rating']);
			header('Content-type: application/json; charset=utf-8');
			exit(json_encode([
				'rating' => (int) $_POST['rating'],
			]));
		}
	}
	exit('NOK');
}

$_title = $title->name;
include 'tpl.header.php';

?>
<style>
[data-watchlist="0"] {
	font-weight: bold;
	color: red;
}
[data-watchlist="1"] {
	font-weight: bold;
	color: green;
}
.working {
	animation: sideway-wiggle linear 500ms infinite;
}
@keyframes sideway-wiggle {
	0%, 100% {
		translate: 0px 0;
	}
	25% {
		translate: -3px 0;
	}
	75% {
		translate: 3px 0;
	}
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
	<button data-watchlist>WL</button> |
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
			<?= get_age($actor->person, $title) ?>
			-
			<?= html($actor->character->name ?? '') ?>
		</li>
	<? endforeach ?>
</ul>

<?php include 'tpl.search.php'; ?>

<script>
(function() {
let needPassword = <?= json_encode(!$validVotedBefore) ?>;

function maybeAskForPassword(data) {
	if (needPassword) {
		const pwd = prompt("What's the password?", '');
		if (pwd == null || pwd == '') return false;
		data.set('password', pwd);
	}
	return true;
}

const watchlistBtn = document.querySelector('[data-watchlist]');
fetch(location.href + '&watchlist=').then(async rsp => {
	const data = await rsp.json();
	if (data.watchlist != null) {
		watchlistBtn.dataset.watchlist = Number(data.watchlist);
	}
});
watchlistBtn.addEventListener('click', function(e) {
	e.preventDefault();

	if (this.dataset.watchlist === '') return;
	const add = Number(!parseInt(this.dataset.watchlist));

	const data = new FormData;
	data.set('watchlist', add);

	if (!maybeAskForPassword(data)) return;

	this.classList.add('working');
	fetch(new Request(location.href), {
		method: 'post',
		body: data,
	}).then(x => x.json()).then(data => {
		this.classList.remove('working');
		this.dataset.watchlist = Number(data.watchlist);
		needPassword = false;
	});
});

const rateButton = document.querySelector('#rate');
rateButton.addEventListener('click', function(e) {
	e.preventDefault();

	const rating = prompt("What's the new rating?", '');
	if (rating == null || !rating.match(/^(1|2|3|4|5|6|7|8|9|10)$/)) return;

	const data = new FormData;
	data.set('rating', rating);

	if (!maybeAskForPassword(data)) return;

	this.classList.add('working');
	fetch(new Request(location.href), {
		method: 'post',
		body: data,
	}).then(x => x.json()).then(data => {
		this.classList.remove('working');
		this.textContent = data.rating;
		needPassword = false;
	});
});
})();
</script>
