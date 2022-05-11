<?php

require __DIR__ . '/inc.bootstrap.php';

$title = $client->getGraphqlTitle($_GET['id'] ?? '');
if (!$title) exit("ID not found");
// dump($title);

if (isset($_POST['rating'], $_POST['password'])) {
	if (password_verify($_POST['password'], VOTING_PASSWORD)) {
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

?>
<title><?= html($title->name) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="theme-color" content="#333" />

<h1>
	<?= html($title->name) ?>
	(<?= $title->getYearLabel() ?? 'year?' ?>)
</h1>
<p>
	<?= html($title->getTypeLabel()) ?> |
	<? if ($title->duration): ?>
		<?= $title->getDurationLabel() ?> |
	<? endif ?>
	<a href="<?= html($title->getUrl()) ?>">Open in IMDB</a> |
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
	<span><?= html($title->plot ?? 'plot?') ?></span>
</p>
<ul>
	<? foreach (array_slice($title->actors, 0, 5) as $actor): ?>
		<li>
			<a href="person.php?id=<?= html($actor->person->id) ?>"><?= html($actor->person->name) ?></a>
			-
			<?= html($actor->character->name ?? '') ?>
		</li>
	<? endforeach ?>
</ul>

<script>
document.querySelector('#rate').addEventListener('click', e => {
	e.preventDefault();

	const rating = prompt("What's the new rating?", '');
	if (rating == null || !rating.match(/^(1|2|3|4|5|6|7|8|9|10)$/)) return;

	const pwd = prompt("What's the password?", '');
	if (pwd == null || pwd == '') return;

	const data = new FormData;
	data.set('rating', rating);
	data.set('password', pwd);
	fetch(new Request(location.href), {
		method: 'post',
		body: data,
	}).then(rsp => location.reload());
});
</script>
