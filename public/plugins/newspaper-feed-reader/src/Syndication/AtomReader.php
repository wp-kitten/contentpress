<?php

namespace App\Helpers\Syndication;

use DOMDocument;

/**
 * class AtomReader
 *
 * The base class for retrieving the content of an ATOM 2.0 feed.
 *
 * @author        Costin Trifan <costintrifan@yahoo.com>
 * @copyright    2009 Costin Trifan
 * @licence        MIT License:    http://en.wikipedia.org/wiki/MIT_License
 * @last update    Dec. 2009
 * @version        1.0
 */
class AtomReader implements ISyndication
{
    /**
     * The instance of the DOMDocument class
     *
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

        $title = $this->_doc->getElementsByTagName( 'title' )->item( 0 );
        if ( !is_null( $title ) ) {
            $result[ $title->tagName ] = trim( $title->nodeValue );
        }

        $subtitle = $this->_doc->getElementsByTagName( 'subtitle' )->item( 0 );
        if ( !is_null( $subtitle ) ) {
            $result[ $subtitle->tagName ] = trim( $subtitle->nodeValue );
        }

        $link = $this->_doc->getElementsByTagName( 'link' )->item( 0 );
        if ( !is_null( $link ) ) {
            $result[ $link->tagName ] = trim( $link->nodeValue );

            $result[ $link->tagName ] = [];
            $result[ $link->tagName ][ 'href' ] = ( $link->getAttribute( 'href' ) );
            $result[ $link->tagName ][ 'rel' ] = ( $link->getAttribute( 'rel' ) );
        }

        $updated = $this->_doc->getElementsByTagName( 'updated' )->item( 0 );
        if ( !is_null( $updated ) ) {
            $result[ $updated->tagName ] = ( $updated->nodeValue );
        }

        $author = $this->_doc->getElementsByTagName( 'author' )->item( 0 );
        if ( !is_null( $author ) ) {
            $result[ $author->tagName ] = [];

            if ( !is_null( $author->getElementsByTagName( 'name' )->item( 0 ) ) ) {
                $result[ $author->tagName ][ 'name' ] = trim( $author->getElementsByTagName( 'name' )->item( 0 )->nodeValue );
            }

            if ( !is_null( $author->getElementsByTagName( 'email' )->item( 0 ) ) ) {
                $result[ $author->tagName ][ 'email' ] = trim( $author->getElementsByTagName( 'email' )->item( 0 )->nodeValue );
            }
        }

        $id = $this->_doc->getElementsByTagName( 'id' )->item( 0 );
        if ( !is_null( $id ) ) {
            $result[ $id->tagName ] = trim( $id->nodeValue );
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

        $i = 0;
        foreach ( $this->_doc->getElementsByTagName( 'entry' ) as $entry ) {
            if ( !is_null( $entry->getElementsByTagName( 'title' )->item( 0 ) ) ) {
                $title = $entry->getElementsByTagName( 'title' )->item( 0 )->nodeValue;
                $title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );

                $link = $entry->getElementsByTagName( 'link' )->item( 0 )->getAttribute( 'href' );
                $link = html_entity_decode( $link, ENT_QUOTES, 'UTF-8' );

                $result[ $i ][ 'title' ] = $title;
                $result[ $i ][ 'link' ] = $link;
            }
            $i++;
            if ( !empty( $maxLimit ) && $maxLimit == $i ) {
                break;
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

        $i = 0;
        foreach ( $this->_doc->getElementsByTagName( 'entry' ) as $entry ) {
            $result[ 'entry_' . $i ] = [];
            foreach ( $entry->getElementsByTagName( '*' ) as $tag ) {
                $result[ 'entry_' . $i ][ $tag->tagName ] = html_entity_decode( $tag->nodeValue, ENT_QUOTES, 'UTF-8' );
            }

            if ( !is_null( $entry->getElementsByTagName( 'link' )->item( 0 ) ) ) {
                $result[ 'entry_' . $i ][ 'link' ] = $entry->getElementsByTagName( 'link' )->item( 0 )->getAttribute( 'href' );
            }

            $i++;
            if ( !empty( $maxLimit ) && $maxLimit == $i ) {
                break;
            }
        }
        return $result;
    }

}
