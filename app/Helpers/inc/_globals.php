<?php

use App\Models\Post;

/**
 * The current version of the application
 * @var float
 */
define( 'VALPRESS_VERSION', '1.0' );
/**
 * Holds the url to the ValPress website
 * @var string
 */
define( 'VALPRESS_URL', 'https://valpress.net' );

/**
 * Holds the url to the api server endpoint
 * @var string
 */
define( 'VALPRESS_API_URL', 'https://api.valpress.net/api' );
/**
 * Stores the HTTP path to the marketplace site
 * @var string
 */
define( 'VALPRESS_MARKETPLACE_URL', 'http://api.valpress.net' );

/**
 * 4 digits year
 * @var string
 */
define( 'CURRENT_YEAR', date( 'Y' ) );

/**
 * 1 digit month
 * @var string
 */
define( 'CURRENT_MONTH_NUM', date( 'n' ) );

define( 'CP_HOUR_IN_SECONDS', 60 );
define( 'CP_DAY_IN_SECONDS', 24 * CP_HOUR_IN_SECONDS );

//#! Various response codes to be used (exclusively|optionally) for ajax requests
define( 'TYPE_ERROR', 'X000000' );
define( 'TYPE_SUCCESS', 'X000001' );

/*
 * Internal constants used mainly for translations
 */
define( 'VALPRESS_TYPE_CORE', 'core' );
define( 'VALPRESS_TYPE_PLUGIN', 'plugin' );
define( 'VALPRESS_TYPE_THEME', 'theme' );

/**
 * Global identifier to be used for forms
 * @var int
 * @see vp_search_form()
 */
$GLOBALS[ 'sid' ] = 0;

/**
 * Stores the current post instance. Only populated when viewing single posts.
 * @var Post
 */
$GLOBALS[ 'cp_post' ] = null;
