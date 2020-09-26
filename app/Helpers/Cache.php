<?php

namespace App\Helpers;

use App\Models\Settings;
use Illuminate\Foundation\Application;
use App\Models\Cache as CacheModel;
use Illuminate\Support\Facades\Schema;

/**
 * Class Cache
 * @package App\Helpers
 *
 * Helper class that provides an abstraction layer for the Redis provider
 */
class Cache
{
    /**
     * Whether or not the cache provider is enabled
     * @var bool
     */
    private $_enabled = false;

    /**
     * The number of minutes a cache is valid. Defaults to 1h. It can be configured in config/app.php using the key: cp_cache_timeout
     * @var int
     */
    private $_cacheTimeout = 60;

    /**
     * @var CacheModel|null
     */
    private $_model = null;

    /**
     * Cache constructor.
     * @param Application $application
     */
    public function __construct( Application $application )
    {
        if ( Schema::hasTable( 'settings' ) ) {
            $settings = new Settings();
            if ( $settings->getSetting( 'use_internal_cache', false ) ) {
                $this->_enabled = true;
                $this->_model = new CacheModel();
            }
            $cfg = $application->get( 'config' );
            if ( $cacheTimeout = $cfg->get( 'app.cp_cache_timeout' ) ) {
                $this->_cacheTimeout = intval( $cacheTimeout );
            }
        }
    }

    /**
     * Check to see whether or not the Internal Cache system is enabled
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_enabled;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get( string $key, $default = '' )
    {
        if ( $this->_enabled ) {
            return $this->_model->get( $key, $default, $this->_cacheTimeout );
        }
        return $default;
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set( string $key, $value = '' )
    {
        if ( $this->_enabled ) {
            $this->_model->add( $key, $value );
        }
        return $this;
    }

    /**
     * Deletes all entries from the cache table
     * @return $this
     */
    public function clear()
    {
        if ( $this->_enabled ) {
            $this->_model->clear();
        }
        return $this;
    }
}
