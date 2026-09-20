<?php

/** @var rdx\imdb\Title $title */

?>
<? foreach ($title->actors as $actor): ?>
	<li>
		<a href="person.php?id=<?= html($actor->person->id) ?>"><?= html($actor->person->name) ?></a>
		<?= get_age($actor, title: $title) ?>
		<? if ($actor->episodes): ?>
			(<?= $actor->episodes ?> eps)
		<? endif ?>
		-
		<?= html($actor->character->name ?? '') ?>
	</li>
<? endforeach ?>
