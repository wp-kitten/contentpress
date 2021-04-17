<?php

namespace App\Helpers;

/**
 * Class DirAutoloader
 * @package App\Helpers
 *
 * Utility class which can be used by themes & plugins to autoload classes
 *
 * @usage Autoloader::registerPath( THEME_DIR_PATH . '/App' );
 */
class DirAutoloader
{
    /**
     * Register the path to directory to autoload classes from.
     * @param string $rootDir
     */
    public static function registerPath( string $rootDir )
    {
        $recursiveIterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $rootDir ) );
        $files = [];
        foreach ( $recursiveIterator as $file ) {
            if ( $file->isDir() ) {
                continue;
            }
            $files[] = $file->getPathname();
        }
        if ( !empty( $files ) ) {
            foreach ( $files as $path ) {
                $className = basename( $path, '.php' );
                if ( !class_exists( $className ) ) {
                    require_once( $path );
                }
            }
        }
    }
}
