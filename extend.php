<?php

namespace fakethinkpad85\LikeSearch;

use Flarum\Extend;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Discussion\Search\Gambit\FulltextGambit;
use Flarum\Search\SearchState;
use Illuminate\Database\Query\Builder;
use Flarum\Post\Post;
use Flarum\Discussion\Discussion;
use Flarum\Query\QueryCriteria;
use Flarum\Query\QueryResults;
use Exception;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use Flarum\Foundation\Paths;
use Illuminate\Contracts\Container\Container;
use Flarum\Discussion\Search\DiscussionSearcher as BaseSearcher;
use Illuminate\Support\ServiceProvider;
use Flarum\Search\GambitManager;
use Flarum\Discussion\Search\Gambit\FulltextGambit as BaseFulltextGambit;

class LikeSearchGambit extends BaseFulltextGambit
{
    public function apply(SearchState $search, $searchValue)
    {
        if (empty($searchValue)) {
            return;
        }

        $search->getQuery()
            ->where(function ($query) use ($searchValue) {
                $pattern = '%' . str_replace(' ', '%', $searchValue) . '%';

                $query->where('discussions.title', 'LIKE', $pattern)
                    ->orWhereExists(function ($query) use ($pattern) {
                        $query->select('posts.id')
                            ->from('posts')
                            ->whereColumn('posts.discussion_id', 'discussions.id')
                            ->where('posts.type', 'comment')
                            ->where('posts.content', 'LIKE', $pattern);
                    });
            });

        $search->setDefaultSort(['created_at' => 'desc']);
    }
}

class SearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register our custom gambit
        $this->app->bind(FulltextGambit::class, LikeSearchGambit::class);
    }
}

return [
    (new Extend\ServiceProvider())
        ->register(SearchServiceProvider::class)
];
