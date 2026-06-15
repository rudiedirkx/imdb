<? foreach ($items as $item): ?>
	<li>
		<img
			width="30"
			data-src="<?= html($item->title->image->url ?? '') ?>"
			onclick="this.src = this.dataset.src; this.onclick = null"
		/>
		<div class="text">
			<?= $item->position ?>. <a href="title.php?id=<?= $item->title->id ?>"><?= html($item->title->name) ?></a>
			(<?= ($item->title->getYearLabel() ?? '?') ?>)
			<span class="rating <?= $item->title->userRating->rating ? 'rated' : '' ?>">
				&#9734; <?= $item->title->userRating->rating ?? '?' ?> / <?= number_format($item->title->rating ?? 0, 1) ?>
			</span><br>
			<? if ($item->title->userRating): ?>
				(added on <?= date('Y-m-d', $item->created) ?>)
			<? endif ?>
			<? if ($item->title->genres): ?>
				(<?= html(implode(', ', $item->title->genres)) ?>)
			<? endif ?>
		</div>
	</li>
<? endforeach ?>
