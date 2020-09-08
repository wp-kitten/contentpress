<?php

namespace App\Helpers\Syndication;

use DOMDocument;
use Exception;

/**
 * class FeedImporter
 *
 *
 * @author      Costin Trifan <costintrifan@yahoo.com>
 * @copyright   2009 Costin Trifan
 * @licence     MIT License:    http://en.wikipedia.org/wiki/MIT_License
 * @version     1.0
 * @revision $1 Dec. 2009
 * @revision $2 Apr 07, 2010
 */
class FeedReader
{
    /**
     * Will hold the instances of the RSS or Atom class respectively
     * @var ISyndication
     */
    protected $_type = null;

    /**
     * Stores the reference to the instance of the DOMDocument class
     * @var DOMDocument
     */
    protected $_doc = null;

    /**
     * Whether or not the feed was successfully loaded
     * @var bool
     */
    protected $_loaded = false;

    /**
     * Open the feed
     *
     * @param string $feed_url
     * @return $this
     * @throws Exception
     */
    public function open( $feed_url = '' )
    {
        if ( empty( $feed_url ) ) {
            throw new Exception( "Please provide the feed url." );
        }
        $this->_doc = new DOMDocument();

        //#! Fixes dismissal from various servers that expect a user agent field
        $opts = [
            'http' => [
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.135 Safari/537.36',
            ],
        ];
        $context = stream_context_create( $opts );
        libxml_set_streams_context( $context );

        if ( !$this->_doc->load( $feed_url ) ) {
            throw new Exception( "The <strong> {$feed_url} </strong> file could not be opened!" );
        }

        $this->_loaded = true;
        $this->_loadClass();

        return $this;
    }

    /**
     * Check to see whether or not the xml document has been successfully loaded.
     *
     * @return bool
     */
    final public function isLoaded()
    {
        return $this->_loaded;
    }

    /**
     * Retrieve the feed's base tags
     *
     * @return array
     */
    public function getBaseTags()
    {
        return $this->_type->getBaseTags();
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
        return $this->_type->getTitles( $maxLimit );
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
        return $this->_type->getEntries( $maxLimit );
    }

    /**
     * Factory method. Check the feed's type and instantiate the appropriate class.
     *
     * @return $this
     * @throws Exception        If the type of the xml document is not a valid RSS or ATOM feed.
     */
    private function _loadClass()
    {
        $root = $this->_doc->documentElement->tagName;

        if ( strcasecmp( $root, 'RSS' ) == 0 ) {
            $this->_type = new RssReader( $this->_doc );
            return $this;
        }
        elseif ( strcasecmp( $root, 'FEED' ) == 0 ) {
            $this->_type = new AtomReader( $this->_doc );
            return $this;
        }
        throw new Exception( 'Unsupported file format!' );
    }

    /**
     * Retrieve the type of the feed
     * @return string
     */
    public function getType()
    {
        return get_class( $this->_type );
    }
}
