<?php

require __DIR__ . '/inc.bootstrap.php';

$id = (string) ($_GET['id'] ?? '');

if ($id && isset($_GET['watchlist'])) {
	$watchlistItem = imdb()->getWatchlistItem($id);
	header('Content-type: application/json; charset=utf-8');
	exit(json_encode([
		'watchlist' => (bool) $watchlistItem,
		'position' => $watchlistItem?->position,
		'notes' => $watchlistItem?->notes,
	]));
}

$validVotedBefore = password_verify(VOTING_PASSWORD, $_COOKIE['imdb_voting_password'] ?? 'x');

if (isset($_POST['watchlist'])) {
	if ($validVotedBefore || password_verify($_POST['password'] ?? 'x', VOTING_PASSWORD)) {
		setcookie('imdb_voting_password', password_hash(VOTING_PASSWORD, PASSWORD_DEFAULT), strtotime('+6 months'));
		$logged = file_put_contents(
			VOTING_LOG_FILE,
			date('Y-m-d H:i:s') . ' - ' . ($_SERVER['REMOTE_ADDR'] ?? '?') . ' - ' . $id . ' - watchlist -> ' . intval($_POST['watchlist']) . "\n",
			FILE_APPEND
		);
		if ($logged) {
			$_POST['watchlist'] ? imdb()->addTitleToWatchlist($id) : imdb()->removeTitleFromWatchlist($id);
			header('Content-type: application/json; charset=utf-8');
			exit(json_encode([
				'watchlist' => (bool) $_POST['watchlist'],
			]));
		}
	}
	exit('NOK');
}

$title = imdb()->getGraphqlTitle($id);
if (!$title) exit("ID not found");
// dump($title);

if (isset($_POST['rating'])) {
	if ($validVotedBefore || password_verify($_POST['password'] ?? 'x', VOTING_PASSWORD)) {
		setcookie('imdb_voting_password', password_hash(VOTING_PASSWORD, PASSWORD_DEFAULT), strtotime('+6 months'));
		$logged = file_put_contents(
			VOTING_LOG_FILE,
			date('Y-m-d H:i:s') . ' - ' . ($_SERVER['REMOTE_ADDR'] ?? '?') . ' - ' . $id . ' - ' . ($title->userRating->rating ?? '_') . ' -> ' . $_POST['rating'] . "\n",
			FILE_APPEND
		);
		if ($logged) {
			imdb()->rateTitle($id, $_POST['rating']);
			header('Content-type: application/json; charset=utf-8');
			exit(json_encode([
				'rating' => (int) $_POST['rating'],
			]));
		}
	}
	exit('NOK');
}

if ($id && isset($_GET['moreactors'])) {
	$actorsTitle = imdb()->getGraphqlTitleActors($id, $_GET['moreactors']);
	$title->actors = $actorsTitle->actors;
	header('imdb-cursor: ' . $actorsTitle->moreActorsCursor);
	include 'tpl.title-actors.php';
	exit;
}

$_title = $title->name;
include 'tpl.header.php';

?>
<style>
.genres-interests.show-interests > .genres,
.genres-interests:not(.show-interests) > .interests {
	display: none;
}
[data-watchlist="0"] {
	font-weight: bold;
	color: red;
}
[data-watchlist="1"] {
	font-weight: bold;
	color: green;
}
[data-watchlist="1"][data-position]:not([data-position=""])::after {
	content: " #" attr(data-position);
}
[data-watchlist="1"][data-notes]:not([data-notes=""]) {
	background-color: green;
	color: white;
}
.working {
	animation: sideway-wiggle linear 500ms infinite;
}
li:has(button[data-cursor=""]) {
	display: none;
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
	<?= html($title->getTypeLabel()) ?>
	<? if ($title->episode): ?>
		<?= $title->episode->season ?>.<?= $title->episode->episode ?>
		(<a href="title.php?id=<?= html($title->episode->series->id) ?>"><?= html($title->episode->series->name) ?></a>)
	<? endif ?>
	|
	<? if ($title->originalName): ?>
		&quot;<?= html($title->originalName) ?>&quot; |
	<? endif ?>
	<? if ($title->duration): ?>
		<?= $title->getDurationLabel() ?> |
	<? endif ?>
	<a href="<?= html($title->getUrl()) ?>">Open in IMDB</a> |
	<button data-watchlist>WL</button> |
	<button id="rate"><?= $title->userRating->rating ?? '?' ?></button> /
	<?= $title->rating ? number_format($title->rating, 1) : 'rating?' ?>
	(<?= $title->ratings !== null ? number_format($title->ratings, 0, '.', '_') : '?' ?>)
	<? if ($title->metacriticRating): ?>
		[<?= $title->metacriticRating ?>]
	<? endif ?>
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
		<? if (count($title->genres) || count($title->interests)): ?>
			<span class="genres-interests">
				<span class="genres"><?= html(implode(', ', $title->genres)) ?></span>
				<span class="interests"><?= html(implode(', ', $title->interests)) ?></span>
			</span> |
		<? endif ?>
		<?= get_countries_and_languages($title) ?>
		<?= html($title->plot ?? 'plot?') ?>
	</span>
</p>
<ul>
	<? foreach (['Director' => $title->directors, 'Writer' => $title->writers] as $role => $people): ?>
		<? foreach ($people as $person): ?>
			<li>
				<?= $role ?>:
				<a href="person.php?id=<?= html($person->id) ?>"><?= html($person->name) ?></a>
			</li>
		<? endforeach ?>
	<? endforeach ?>
	<?php include 'tpl.title-actors.php'; ?>
	<? if ($title->moreActorsCursor): ?>
		<li><button id="more-actors" data-cursor="<?= html($title->moreActorsCursor) ?>">Load more actors</button></li>
	<? endif ?>
</ul>

<? if (count($title->episodes)): ?>
	<hr>
	<h2>Some episodes (<?= $title->totalSeasons ?> seasons, <?= $title->totalEpisodes ?> episodes)</h2>
	<ul>
		<? foreach ($title->episodes as $episode): ?>
			<li>
				<?= $episode->episode->season ?>.<?= $episode->episode->episode ?>
				<a href="title.php?id=<?= $episode->id ?>"><?= html($episode->name) ?></a>
			</li>
		<? endforeach ?>
	</ul>
<? endif ?>

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

const genresToggle = document.querySelector('.genres-interests');
genresToggle.addEventListener('click', function(e) {
	this.classList.toggle('show-interests');
});

const watchlistBtn = document.querySelector('[data-watchlist]');
fetch(location.href + '&watchlist=').then(async rsp => {
	const data = await rsp.json();
	if (data.watchlist != null) {
		watchlistBtn.dataset.watchlist = Number(data.watchlist);
		watchlistBtn.dataset.position = data.position || '';
		watchlistBtn.dataset.notes = data.notes || '';
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
	fetch(new Request(location.href, {
		method: 'post',
		body: data,
	})).then(x => x.json()).then(data => {
		this.classList.remove('working');
		this.dataset.watchlist = Number(data.watchlist);
		this.dataset.position = '';
		this.dataset.notes = '';
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
	fetch(new Request(location.href, {
		method: 'post',
		body: data,
	})).then(x => x.json()).then(data => {
		this.classList.remove('working');
		this.textContent = data.rating;
		needPassword = false;
	});
});

const moreActorsButton = document.querySelector('#more-actors');
moreActorsButton.addEventListener('click', async function(e) {
	e.preventDefault();

	const cursor = this.dataset.cursor;

	this.classList.add('working');
	const rsp = await fetch(location.href + '&moreactors=' + encodeURIComponent(cursor));
	this.classList.remove('working');
	const html = await rsp.text();

	const newCursor = rsp.headers.get('imdb-cursor');
	this.dataset.cursor = newCursor;

	const last = this.closest('li');
	const ul = this.closest('ul');
	const more = document.createElement('div');
	more.innerHTML = html;
	ul.append(more);
	ul.append(this.closest('li'));
});
})();
</script>
