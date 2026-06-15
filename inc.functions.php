<?php

use rdx\imdb\Actor;
use rdx\imdb\Person;
use rdx\imdb\Title;

function html( $text ) {
	return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8') ?: htmlspecialchars((string)$text, ENT_QUOTES, 'ISO-8859-1');
}

function html_asset( $src ) {
	return $src . '?v=' . filemtime($src);
}

function do_redirect( $url ) {
	header('Location: ' . $url);
}

function get_age( Actor $actor, ?Title $title = null, ?Person $person = null ) : string {
	$person ??= $actor->person;

	$title ??= $actor->title;

	if ($actor->fromYear && $actor->fromYear != $title->year) {
		if ($person->birthYear) {
			return sprintf('(%d in %d)', ($actor->fromYear - $person->birthYear), $actor->fromYear);
		}
		return sprintf('(first in %d)', $actor->fromYear);
	}

	if ($title->year && $person->birthYear) {
		return sprintf('(%d)', ($title->year - $person->birthYear));
	}

	return '';
}
