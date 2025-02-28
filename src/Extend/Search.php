<?php

namespace fakethinkpad85\LikeSearch\Extend;

use Flarum\Extend;
use Flarum\Discussion\Search\Gambit\FulltextGambit;

return [
    (new Extend\AddGambit(FulltextGambit::class))
];