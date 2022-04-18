<?php

use rdx\jsdom\Node;

require __DIR__ . '/inc.bootstrap.php';
require 'src/Intersector.php';
require 'src/Intersection.php';

$intersector = new Intersector($client, $titles);
$intersector->loadActors();
$intersections = $intersector->intersect();

?>

<? if (count($intersections)): ?>
	<table border=1 class="results intersect-results">
		<tr>
			<td></td>
			<? foreach ($intersector->titles as $title): ?>
				<td class="title">
					<a href="<?= $title->getUrl() ?>"><?= html($title->name) ?></a>
				</td>
			<? endforeach ?>
		</tr>
		<? foreach ($intersections as $intersection): ?>
			<tr>
				<td class="actor">
					<a href="<?= $intersection->person->getUrl() ?>"><?= html($intersection->person->name) ?></a>
				</td>
				<? foreach ($intersector->titles as $title): ?>
					<td class="character"><?= html($intersection->characters[$title->id]->name) ?></td>
				<? endforeach ?>
			</tr>
		<? endforeach ?>
	</table>
<? else: ?>
	<p>No results...</p>
<? endif ?>
