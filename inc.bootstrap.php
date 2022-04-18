<?php

use rdx\imdb\AuthSession;
use rdx\imdb\Client;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/env.php';

$client = new Client(new AuthSession(IMDB_AT_MAIN, IMDB_UBID_MAIN));
