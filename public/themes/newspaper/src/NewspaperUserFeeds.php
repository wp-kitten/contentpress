<?php

namespace App\Newspaper;

use App\Models\Feed;
use App\Newspaper\User as NpUser;
use Illuminate\Support\Collection;

class NewspaperUserFeeds
{
    /**
     * @param int $userID
     * @return mixed
     */
    public static function getUserFeeds( int $userID = 0 )
    {
        if ( empty( $userID ) ) {
            $userID = cp_get_current_user_id();
        }
        return NpUser::find( $userID )->feeds()->get();
    }

    public static function getUserCategories( int $userID = 0, Collection $feeds = null )
    {
        $categories = [];

        if ( empty( $userID ) ) {
            $userID = cp_get_current_user_id();
        }
        if ( is_null( $feeds ) ) {
            $feeds = self::getUserFeeds( $userID );
        }

        if ( !empty( $feeds ) ) {
            foreach ( $feeds as $feed ) {
                $category = $feed->category()->first();
                if ( !isset( $categories[ $category->id ] ) ) {
                    $categories[ $category->id ] = [
                        'category' => $category,
                        'count' => 0,
                    ];
                }
                $categories[ $category->id ][ 'count' ]++;
            }
        }
        return $categories;
    }

    public static function getFeedsFromCategory( int $categoryID, $ignoreFeedIds = [] )
    {
        $query = Feed::where( 'category_id', $categoryID );
        if ( !empty( $ignoreFeedIds ) ) {
            $query = $query->whereNotIn( 'id', $ignoreFeedIds );
        }
        return $query->get();
    }
}
