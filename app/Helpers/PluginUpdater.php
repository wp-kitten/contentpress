<?php

namespace App\Helpers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class PluginUpdater extends UpdaterAbstract
{

    /**
     * @param string $pluginFileName The name of the plugin's directory
     * @return mixed
     */
    function update( $pluginFileName )
    {
        if ( empty( $pluginFileName ) ) {
            return false;
        }

        if ( empty( $this->pluginsUpdateInfo ) || !isset( $this->pluginsUpdateInfo[ $pluginFileName ] ) ) {
            return false;
        }

        $downloadFileUrl = $this->pluginsUpdateInfo[ $pluginFileName ][ 'url' ];
        if ( empty( $downloadFileUrl ) ) {
            return false;
        }

        $updateInfo = $this->pluginsUpdateInfo[ $pluginFileName ];
        if ( !isset( $updateInfo[ 'url' ] ) || empty( $updateInfo[ 'url' ] ) ) {
            return false;
        }
        $response = Http::get( $updateInfo[ 'url' ] );
        if ( !$response->successful() ) {
            return false;
        }
        $archiveData = $response->body();
        $archiveName = basename( $downloadFileUrl );
        $savePath = path_combine( public_path( 'uploads/tmp' ), $archiveName );
        File::put( $savePath, $archiveData );

        if ( !File::isReadable( $savePath ) ) {
            File::chmod( $savePath, 0775 );
        }

        $zip = new \ZipArchive();
        if ( $zip->open( $savePath ) ) {

            $zip->extractTo( $this->pluginsDir );
            $zip->close();

            File::delete( $savePath );

            //#! Remove entry from db cache
            unset( $this->dbInfo[ 'plugins' ][ $pluginFileName ] );
            $this->options->addOption( 'valpress_updates', $this->dbInfo );
            return true;
        }
        return false;
    }

    function exists( $pluginFileName )
    {
        $pluginPath = path_combine( $this->pluginsDir, $pluginFileName );
        return File::isDirectory( $pluginPath );
    }

    function getInfo( $pluginFileName )
    {
        return PluginsManager::getInstance()->getPluginInfo( $pluginFileName );
    }
}
