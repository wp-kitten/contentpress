<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Class ThemeUpdater
 * @package App\Helpers
 */
class ThemeUpdater extends ContentPressUpdaterAbstract
{
    public function update( $themeFileName )
    {
        if ( empty( $themeFileName ) ) {
            return false;
        }

        if ( empty( $this->themesUpdateInfo ) || !isset( $this->themesUpdateInfo[ $themeFileName ] ) ) {
            return false;
        }

        $downloadFileUrl = $this->themesUpdateInfo[ $themeFileName ][ 'url' ];
        if ( empty( $downloadFileUrl ) ) {
            return false;
        }

        $updateInfo = $this->themesUpdateInfo[ $themeFileName ];
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

            $zip->extractTo( $this->themesDir );
            $zip->close();

            File::delete( $savePath );

            //#! Remove entry from db cache
            unset( $this->dbInfo[ 'themes' ][ $themeFileName ] );
            $this->options->addOption( 'contentpress_updates', $this->dbInfo );

            return true;
        }
        return false;
    }

    public function exists( $themeFileName )
    {
        $themePath = path_combine( $this->themesDir, $themeFileName );
        return File::isDirectory( $themePath );
    }

    public function getInfo( $themeFileName )
    {
        if ( !$this->exists( $themeFileName ) ) {
            return false;
        }
        $theme = new Theme( $themeFileName );
        if ( !$theme->isValid() ) {
            return false;
        }
        return $theme->getThemeData();
    }
}
