<?php

namespace App\Listeners;

use App\Events\AppLoadedEvent;
use App\Helpers\PluginsManager;
use App\Helpers\ThemesManager;
use Illuminate\Support\Facades\File;

class AppLoadedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param AppLoadedEvent $event
     * @return void
     */
    public function handle( AppLoadedEvent $event )
    {
        //#! Create the required app directories if they don't exist
        $uploadsDirPath = public_path( 'uploads' );
        $appDirs = [
            $uploadsDirPath,
            path_combine( $uploadsDirPath, 'files' ),
            path_combine( $uploadsDirPath, 'tmp' ),
            path_combine( $uploadsDirPath, 'plugins' ),
            path_combine( $uploadsDirPath, 'themes' ),
        ];
        try {
            foreach ( $appDirs as $dirPath ) {
                if ( ! File::isDirectory( $dirPath ) ) {
                    File::makeDirectory( $dirPath, 775, true, true );
                }
            }
        }
        catch ( \Exception $e ) {
            logger( 'Error creating directory: ' . $e->getMessage() );
        }

        PluginsManager::getInstance();
        ThemesManager::getInstance();

        if ( vp_is_admin() ) {
            if ( !did_action( 'valpress/admin/init' ) ) {
                do_action( 'valpress/admin/init' );
            }
        }

        if ( !did_action( 'valpress/app/loaded' ) ) {
            do_action( 'valpress/app/loaded' );
        }
    }
}
