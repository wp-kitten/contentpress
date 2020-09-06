<?php

namespace App\Helpers;

use App\Options;

abstract class ContentPressUpdaterAbstract
{
    /**
     * @var string
     */
    protected $themesDir = '';
    /**
     * @var string
     */
    protected $pluginsDir;

    protected $dbInfo = [];
    protected $themesUpdateInfo = [];
    protected $pluginsUpdateInfo;

    protected $options = null;

    /**
     * ContentPressUpdaterAbstract constructor.
     */
    public function __construct()
    {
        $this->themesDir = untrailingslashit( wp_normalize_path( public_path( 'themes' ) ) );
        $this->pluginsDir = untrailingslashit( wp_normalize_path( public_path( 'plugins' ) ) );

        $this->options = new Options();
        $this->dbInfo = $this->options->getOption( 'contentpress_updates', [ 'plugins' => [], 'themes' => [] ] );
        $this->themesUpdateInfo = $this->dbInfo[ 'themes' ];
        $this->pluginsUpdateInfo = $this->dbInfo[ 'plugins' ];
    }

    /**
     * @param string $resourceFileName The name of the resource's (theme or plugin) directory
     * @return mixed
     */
    abstract function update( $resourceFileName );
    abstract function exists( $resourceFileName );
    abstract function getInfo( $resourceFileName );
}
