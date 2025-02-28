<?php

namespace fakethinkpad85\LikeSearch\Search\Gambit;

use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Search\GambitInterface;
use Flarum\Search\SearchState;
use Illuminate\Database\Query\Expression;

class FulltextGambit implements GambitInterface
{
    /**                                                                                                                                                                       
     * {@inheritdoc}                                                                                                                                                          
     */                                                                                                                                                                       
    public function apply(SearchState $search, $bit)                                                                                                                          
    {                                                                                                                                                                         

        // Replace all non-word characters with spaces.                                                                                                                      
        $bit = preg_replace('/[^\p{L}\p{N}\p{M}_]+/u', ' ', $bit);                                                                                                           
                                                                                                                                                                             
        $query = $search->getQuery();                                                                                                                                        
        $grammar = $query->getGrammar();                                                                                                                                     
                                                                                                                                                                             
        // Modify this query to use LIKE instead of MATCH
        $discussionSubquery = Discussion::select('id')                                                                                                                       
            ->selectRaw('NULL as score')                                                                                                                                     
            ->selectRaw('first_post_id as most_relevant_post_id')                                                                                                            
            ->where('discussions.title', 'LIKE', '%' . $bit . '%'); // Changed from MATCH to LIKE                                                                         

        // Modify this query to use LIKE instead of MATCH for posts
        $subquery = Post::whereVisibleTo($search->getActor())
            ->select('posts.discussion_id')
            ->selectRaw('SUM(CASE WHEN posts.content LIKE ? THEN 1 ELSE 0 END) as score', ['%' . $bit . '%']) // Changed from MATCH to LIKE
            ->selectRaw('SUBSTRING_INDEX(GROUP_CONCAT('.$grammar->wrap('posts.id').' ORDER BY CASE WHEN posts.content LIKE ? THEN 1 ELSE 0 END DESC, '.$grammar->wrap('posts.number').'), \',\', 1) as most_relevant_post_id', ['%' . $bit . '%']) // Changed from MATCH to LIKE
            ->where('posts.type', 'comment')
            ->groupBy('posts.discussion_id')
            ->union($discussionSubquery);

        // Join the subquery into the main search query and scope results to                                                                                                 
        // discussions that have a relevant title or that contain relevant posts.                                                                                             
        $query
            ->addSelect('posts_ft.most_relevant_post_id')
            ->join(
                new Expression('('.$subquery->toSql().') '.$grammar->wrapTable('posts_ft')),
                'posts_ft.discussion_id',
                '=',
                'discussions.id'
            )
            ->groupBy('discussions.id')
            ->addBinding($subquery->getBindings(), 'join');

        // Modify the sort order to use LIKE as well
        $search->setDefaultSort(function ($query) use ($grammar, $bit) {
            $query->orderByRaw('CASE WHEN discussions.title LIKE ? THEN 1 ELSE 0 END desc', ['%' . $bit . '%']); // Changed from MATCH to LIKE
            $query->orderBy('posts_ft.score', 'desc');
        });

        return true;
    }
}