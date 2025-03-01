<?php

namespace fakethinkpad85\LikeSearch;

use Flarum\Extend;
use Flarum\Discussion\Search\Gambit\FulltextGambit;
use fakethinkpad85\Search\Gambit\

return [
    (new Extend\AddGambit(FulltextGambit::class))
];
