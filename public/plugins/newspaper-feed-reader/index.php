<?php
/**
 * Stores the name of the plugin's directory
 */

define( 'NPFR_PLUGIN_DIR_NAME', basename( dirname( __FILE__ ) ) );

/**
 * Stores the system path to the plugin's directory
 */
define( 'NPFR_PLUGIN_DIR_PATH', dirname( __FILE__ ) );

/**
 * The name of the option storing whether the process is already in progress
 * @var string
 */
define( 'NPFR_PROCESS_OPT_NAME', 'cp_feed_reader_running' );

/**
 * Stores the name of the special category: public
 * @var string
 */
define( 'NPFR_CATEGORY_PUBLIC', 'public' );
/**
 * Stores the name of the special category: private
 * @var string
 */
define( 'NPFR_CATEGORY_PRIVATE', 'private' );

require_once( NPFR_PLUGIN_DIR_PATH . '/src/Models/Feed.php' );
require_once( NPFR_PLUGIN_DIR_PATH . '/src/Syndication/ISyndication.php' );
require_once( NPFR_PLUGIN_DIR_PATH . '/src/Syndication/AtomReader.php' );
require_once( NPFR_PLUGIN_DIR_PATH . '/src/Syndication/RssReader.php' );
require_once( NPFR_PLUGIN_DIR_PATH . '/src/Syndication/FeedReader.php' );
require_once( NPFR_PLUGIN_DIR_PATH . '/src/FeedImporter.php' );
require_once( NPFR_PLUGIN_DIR_PATH . '/functions.php' );

require_once( NPFR_PLUGIN_DIR_PATH . '/plugin-hooks.php' );
require_once( NPFR_PLUGIN_DIR_PATH . '/routes/web.php' );
require_once( NPFR_PLUGIN_DIR_PATH . '/routes/console.php' );
