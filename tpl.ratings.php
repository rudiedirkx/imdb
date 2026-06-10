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
			<span class="rating rated">
				&#9734; <?= $title->userRating->rating ?? '?' ?> / <?= number_format($title->rating ?? 0, 1) ?>
			</span>
			<?if ($title->userRating): ?>
				<br>
				(rated on <?= date('Y-m-d', $title->userRating->ratedOn) ?>)
			<? endif ?>
		</div>
	</li>
<? endforeach ?>
