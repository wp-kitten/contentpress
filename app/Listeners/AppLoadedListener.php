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

        if ( cp_is_admin() ) {
            if ( !did_action( 'contentpress/admin/init' ) ) {
                do_action( 'contentpress/admin/init' );
            }
        }

        if ( !did_action( 'contentpress/app/loaded' ) ) {
            do_action( 'contentpress/app/loaded' );
        }
    }
}
