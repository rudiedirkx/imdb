<?php

use rdx\imdb\Person;

class Intersection {

	public function __construct(
		public Person $person,
		public array $characters,
	) {}

}
