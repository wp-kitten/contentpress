<?php

namespace App\Helpers\Syndication;

use DOMDocument;

/**
 * class RssReader
 *
 * The base class for retrieving the content of an RSS 2.0 feed.
 *
 * @author        Costin Trifan <costintrifan@yahoo.com>
 * @copyright    2009 Costin Trifan
 * @licence        MIT License:    http://en.wikipedia.org/wiki/MIT_License
 * @last update    Dec. 2009
 * @version        1.0
 */
class RssReader implements ISyndication
{
    /**
     * The instance of the DOMDocument class
     * @var DOMDocument
     * @see __construct
     */
    protected $_doc = null;

    public function __construct( DOMDocument $docXML )
    {
        $this->_doc = $docXML;
    }

    /**
     * Retrieve the feed's base tags
     *
     * @return array
     */
    public function getBaseTags()
    {
        $result = [];

        $ch = $this->_doc->getElementsByTagName( 'channel' )->item( 0 );
        if ( !is_null( $ch ) ) {
            if ( $ch->hasChildNodes() ) {
                foreach ( $ch->getElementsByTagName( '*' ) as $tag ) {
                    // do not select item tags
                    if ( $tag->hasChildNodes() && ( strtolower( $tag->tagName ) != 'item' ) ) {
                        $result[ $tag->tagName ] = html_entity_decode( $tag->nodeValue, ENT_QUOTES, 'UTF-8' );
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Retrieve the feed's titles
     *
     * @param int $maxLimit The maximum number of titles to retrieve.
     * If $maxLimit = 0 all records will be retrieved.
     *
     * @return array
     */
    public function getTitles( $maxLimit = 0 )
    {
        $result = [];

        $ch = $this->_doc->getElementsByTagName( 'channel' )->item( 0 );
        if ( ( !is_null( $ch ) ) and $ch->hasChildNodes() ) {
            $i = 0;
            foreach ( $ch->getElementsByTagName( 'item' ) as $tag ) {
                if ( !is_null( $tag->getElementsByTagName( 'title' )->item( 0 ) ) ) {
                    $title = $tag->getElementsByTagName( 'title' )->item( 0 )->nodeValue;
                    $link = $tag->getElementsByTagName( 'link' )->item( 0 )->nodeValue;

                    $title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
                    $link = html_entity_decode( $link, ENT_QUOTES, 'UTF-8' );

                    $result[ $i ][ 'title' ] = $title;
                    $result[ $i ][ 'link' ] = $link;
                }
                $i++;
                if ( !empty( $maxLimit ) && $maxLimit == $i ) {
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Retrieve the feed's entries
     *
     * @param int $maxLimit The maximum number of entries to retrieve.
     * If $maxLimit = 0 all records will be retrieved.
     *
     * @return array
     */
    public function getEntries( $maxLimit = 0 )
    {
        $result = [];

        $ch = $this->_doc->getElementsByTagName( 'channel' )->item( 0 );
        if ( !is_null( $ch ) ) {
            if ( $ch->hasChildNodes() ) {
                $i = 0;
                foreach ( $ch->getElementsByTagName( 'item' ) as $tag ) {
                    $result[ 'item_' . $i ] = [];

                    foreach ( $tag->getElementsByTagName( '*' ) as $item ) {
                        if ( 'media:thumbnail' == $item->tagName && $item->hasAttribute( 'url' ) ) {
                            $url = $item->getAttribute( 'url' );
                            $result[ 'item_' . $i ][ $item->tagName ] = $url;
                        }
                        elseif ( 'enclosure' == $item->tagName && $item->hasAttribute( 'url' ) ) {
                            //#! If this is an image (ex: http://www.sportingnews.com/us/rss)
                            $type = strtolower( $item->getAttribute( 'type' ) );
                            if ( false !== stripos( $type, 'image' ) ) {
                                $url = $item->getAttribute( 'url' );
                                $result[ 'item_' . $i ][ 'media:thumbnail' ] = $url;
                            }
                            else {
                                $url = $item->getAttribute( 'url' );
                                $result[ 'item_' . $i ][ $item->tagName ] = $url;
                            }
                        }
                        else {
                            $result[ 'item_' . $i ][ $item->tagName ] = html_entity_decode( $item->nodeValue, ENT_QUOTES, 'UTF-8' );
                        }
                    }

                    $i++;
                    if ( !empty( $maxLimit ) && $maxLimit == $i ) {
                        break;
                    }
                }
            }
        }
        return $result;
    }

}
