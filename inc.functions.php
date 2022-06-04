<?php

use rdx\imdb\Person;
use rdx\imdb\Title;

function html( $text ) {
	return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8') ?: htmlspecialchars((string)$text, ENT_QUOTES, 'ISO-8859-1');
}

function do_redirect( $url ) {
	header('Location: ' . $url);
}

function get_age( Person $person, Title $title ) {
	if ($person->birthYear && $title->year) {
		return '~' . ($title->year - $person->birthYear);
	}
}
