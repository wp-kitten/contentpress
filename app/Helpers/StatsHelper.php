<?php

namespace App\Helpers;

use App\Models\CommentStatuses;
use App\Models\Options;
use App\Models\Post;
use App\Models\PostComments;
use App\Models\User;

/**
 * Class StatsHelper
 * @package App\Helpers
 *
 * Standard Singleton
 * This class provides utility methods to interact with the website's statistics
 */
class StatsHelper
{
    const KEY_POSTS = 'posts';
    const KEY_COMMENTS = 'comments';
    const KEY_COMMENTS_PENDING = 'pending_comments';
    const KEY_SPAM_COMMENTS = 'spam_comments';
    const KEY_USERS = 'users';

    const OPERATOR_PLUS = '+';
    const OPERATOR_MINUS = '-';

    /**
     * Holds the reference to the instance of this class
     * @var StatsHelper|null
     */
    private static $_instance = null;

    /**
     * Internal cache version of all stats
     * @var array
     */
    private static $_stats = [];

    /**
     * StatsHelper constructor.
     */
    private function __construct()
    {
        if ( empty( self::$_stats ) ) {
            $this->refreshStats();
        }
    }

    /**
     * Retrieve the reference to the instance of this class
     * @return StatsHelper|null
     */
    public static function getInstance()
    {
        if ( !self::$_instance ) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Refresh stats
     */
    public function refreshStats()
    {
        $option = Options::where( 'name', 'site_stats' )->first();
        if ( !$option ) {
            $optData = [
                self::KEY_POSTS => [ CURRENT_YEAR => [ CURRENT_MONTH_NUM => 0 ] ],
                self::KEY_COMMENTS => [ CURRENT_YEAR => [ CURRENT_MONTH_NUM => 0 ] ],
                self::KEY_COMMENTS_PENDING => [ CURRENT_YEAR => [ CURRENT_MONTH_NUM => 0 ] ],
                self::KEY_SPAM_COMMENTS => [ CURRENT_YEAR => [ CURRENT_MONTH_NUM => 0 ] ],
                self::KEY_USERS => [ CURRENT_YEAR => [ CURRENT_MONTH_NUM => 0 ] ],
            ];

            Options::create( [
                'name' => 'site_stats',
                'value' => maybe_serialize( $optData ),
            ] );
        }
        else {
            $optData = ( new Options() )->getOption( 'site_stats', [], true );

            $optData = array_merge( [
                self::KEY_POSTS => [ CURRENT_YEAR => [ CURRENT_MONTH_NUM => 0 ] ],
                self::KEY_COMMENTS => [ CURRENT_YEAR => [ CURRENT_MONTH_NUM => 0 ] ],
                self::KEY_COMMENTS_PENDING => [ CURRENT_YEAR => [ CURRENT_MONTH_NUM => 0 ] ],
                self::KEY_SPAM_COMMENTS => [ CURRENT_YEAR => [ CURRENT_MONTH_NUM => 0 ] ],
                self::KEY_USERS => [ CURRENT_YEAR => [ CURRENT_MONTH_NUM => 0 ] ],
            ], $optData );


            $approvedComments = PostComments::where( 'comment_status_id', CommentStatuses::where( 'name', 'approve' )->first()->id )->count();
            $pendingComments = PostComments::where( 'comment_status_id', CommentStatuses::where( 'name', 'pending' )->first()->id )->count();
            $spamComments = PostComments::where( 'comment_status_id', CommentStatuses::where( 'name', 'spam' )->first()->id )->count();

            $optData[ self::KEY_POSTS ][ CURRENT_YEAR ][ CURRENT_MONTH_NUM ] = Post::count();
            $optData[ self::KEY_USERS ][ CURRENT_YEAR ][ CURRENT_MONTH_NUM ] = User::count();
            $optData[ self::KEY_COMMENTS ][ CURRENT_YEAR ][ CURRENT_MONTH_NUM ] = $approvedComments;
            $optData[ self::KEY_COMMENTS_PENDING ][ CURRENT_YEAR ][ CURRENT_MONTH_NUM ] = $pendingComments;
            $optData[ self::KEY_SPAM_COMMENTS ][ CURRENT_YEAR ][ CURRENT_MONTH_NUM ] = $spamComments;

            $option->value = maybe_serialize( $optData );
            $option->update();
        }

        self::$_stats = $optData;
    }

    /**
     * Update the stats option
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function update( $key, $value )
    {
        self::$_stats[ $key ] = $value;
        $option = Options::where( 'name', 'site_stats' )->first();
        if ( !$option ) {
            Options::create( [
                'name' => 'site_stats',
                'value' => maybe_serialize( self::$_stats ),
            ] );
        }
        else {
            $option->value = maybe_serialize( self::$_stats );
            $option->update();
        }
        return $this;
    }

    /**
     * Retrieve the data associated with the specified key
     * @param string $key
     * @return array|mixed
     */
    public function getKey( $key )
    {
        if ( isset( self::$_stats[ $key ] ) ) {
            if ( !isset( self::$_stats[ $key ][ CURRENT_YEAR ] ) ) {
                self::$_stats[ $key ][ CURRENT_YEAR ] = [];
            }
            if ( !isset( self::$_stats[ $key ][ CURRENT_YEAR ][ CURRENT_MONTH_NUM ] ) ) {
                self::$_stats[ $key ][ CURRENT_YEAR ][ CURRENT_MONTH_NUM ] = 0;
            }
            return self::$_stats[ $key ];
        }

        if ( !isset( self::$_stats[ $key ][ CURRENT_YEAR ] ) ) {
            self::$_stats[ $key ][ CURRENT_YEAR ] = [];
        }
        if ( !isset( self::$_stats[ $key ][ CURRENT_YEAR ][ CURRENT_MONTH_NUM ] ) ) {
            self::$_stats[ $key ][ CURRENT_YEAR ][ CURRENT_MONTH_NUM ] = 0;
        }
        return self::$_stats[ $key ];
    }

    /**
     * Retrieve the posts stats
     * @return array|mixed
     */
    public function getPostsInfo()
    {
        return ( isset( self::$_stats[ self::KEY_POSTS ] ) ? self::$_stats[ self::KEY_POSTS ] : [] );
    }

    /**
     * Retrieve the comments stats
     * @return array|mixed
     */
    public function getCommentsInfo()
    {
        return ( isset( self::$_stats[ self::KEY_COMMENTS ] ) ? self::$_stats[ self::KEY_COMMENTS ] : [] );
    }

    /**
     * Retrieve the spam comments stats
     * @return array|mixed
     */
    public function getSpamCommentsInfo()
    {
        return ( isset( self::$_stats[ self::KEY_SPAM_COMMENTS ] ) ? self::$_stats[ self::KEY_SPAM_COMMENTS ] : [] );
    }

    /**
     * Retrieve the number of pending comments
     * @return int
     */
    public function getPendingComments()
    {
        return ( isset( self::$_stats[ self::KEY_COMMENTS_PENDING ] ) ? intval( self::$_stats[ self::KEY_COMMENTS_PENDING ] ) : 0 );
    }

    /**
     * Retrieve the users stats
     * @return array|mixed
     */
    public function getUsersInfo()
    {
        return ( isset( self::$_stats[ self::KEY_USERS ] ) ? self::$_stats[ self::KEY_USERS ] : [] );
    }

    /// >> Utility methods
    /// =======================================
    ///
    public function hasCurrentMonth( $data )
    {
        return ( isset( $data[ CURRENT_MONTH_NUM ] ) && !empty( $data[ CURRENT_MONTH_NUM ] ) );
    }

    public function getCurrentMonthName( $short = false )
    {
        return Util::getMonthName( CURRENT_MONTH_NUM, $short );
    }

    public function getMonthNamesFromList( $array, $short = false )
    {
        if ( empty( $array ) ) {
            return [];
        }
        return array_map( function ( $monthNum ) use ( $short ) {
            return Util::getMonthName( $monthNum, $short );
        }, $array );
    }

    public function buildStringsArrayForJS( $array = [] )
    {
        if ( empty( $array ) ) {
            return '';
        }
        return ( '"' . implode( '","', $array ) . '"' );
    }

    public function getLastMonthInfo( $array )
    {
        $out = [
            'name' => '',
            'short_name' => '',
            'data' => 0,
        ];
        $lastMonthNum = ( CURRENT_MONTH_NUM - 1 );
        if ( empty( $lastMonthNum ) ) {
            return $out;
        }

        $hasLastMonth = isset( $array[ $lastMonthNum ] );
        $lastMonthData = ( $hasLastMonth ? $array[ $lastMonthNum ] : 0 );

        $out[ 'name' ] = Util::getMonthName( $lastMonthNum );
        $out[ 'short_name' ] = Util::getMonthName( $lastMonthNum, true );
        $out[ 'data' ] = $lastMonthData;
        return $out;
    }

    private function __isValidOperator( $operator )
    {
        return in_array( $operator, [ self::OPERATOR_MINUS, self::OPERATOR_PLUS ] );
    }
}
