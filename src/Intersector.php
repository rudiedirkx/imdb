<?php

use rdx\imdb\Client;
use rdx\imdb\Title;

class Intersector {

	public array $actors = [];

	public function __construct(
		protected Client $client,
		public array $titles,
	) {
		$this->titles = array_map(fn($name, $id) => new Title($id, $name), $titles, array_keys($titles));
	}

	public function loadActors() {
		foreach ($this->titles as $title) {
			$this->actors[$title->id] = $this->keyActors($this->client->getTitleActors($title->id));
		}
	}

	public function intersect() : array {
		$common = array_intersect_key(...array_values($this->actors));
// dump($common);
		$people = $this->actors[key($this->actors)];

		$actors = array_map(function($x, $id) use ($people) {
			return new Intersection(
				$people[$id]->person,
				array_map(function($actors) use ($id) {
					return $actors[$id]->character;
				}, $this->actors),
			);
		}, $common, array_keys($common));
		return $actors;
	}

	protected function keyActors( array $actors ) : array {
		$keyed = [];
		foreach ($actors as $actor) {
			$keyed[$actor->person->id] = $actor;
		}

		return $keyed;
	}

}
