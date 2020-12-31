<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class TranslationManager
{
    /**
     * Retrieve all files from a specified language directory
     * @param string $langCode
     * @param string $type
     * @param string $dirName Only used for themes & plugins, stores the directory name of the theme or plugin
     * @return false|\Symfony\Component\Finder\SplFileInfo[]
     */
    public function getFiles( string $langCode, string $type = VALPRESS_TYPE_CORE, string $dirName = '' )
    {
        if ( '' == ( $dirPath = $this->getLanguagesDirPath( $langCode, $type, $dirName ) ) ) {
            return false;
        }

        if ( !File::isDirectory( $dirPath ) ) {
            return false;
        }

        return File::files( $dirPath );
    }

    /**
     * Load the entries from the specified language file
     * @param string $dirPath
     * @param string $fileName
     * @return array|mixed
     */
    public function loadFile( string $dirPath, string $fileName ): array
    {
        $filePath = path_combine( $dirPath, $fileName );
        if ( !File::isFile( $filePath ) ) {
            return [];
        }
        return include( $filePath );
    }

    /**
     * Retrieve the system path to the specified language directory
     * @param string $langCode
     * @param string $type
     * @param string|null $dirName
     * @return string
     */
    public function getLanguagesDirPath( string $langCode, string $type = VALPRESS_TYPE_CORE, string $dirName = null ): string
    {
        if ( $type == VALPRESS_TYPE_CORE ) {
            return resource_path( "lang/{$langCode}" );
        }
        elseif ( $type == VALPRESS_TYPE_PLUGIN ) {
            return public_path( "plugins/{$dirName}/lang/{$langCode}" );
        }
        elseif ( $type == VALPRESS_TYPE_THEME ) {
            return public_path( "themes/{$dirName}/lang/{$langCode}" );
        }
        return '';
    }

    /**
     * Retrieve all translations from a file
     * @param string $filePath
     * @return array
     */
    protected function extractLocalizedStrings( string $filePath ): array
    {
        $functions = [ '__' ];

        /*
         * This pattern is derived from Barryvdh\TranslationManager by Barry vd. Heuvel <barryvdh@gmail.com>
         *
         * https://github.com/barryvdh/laravel-translation-manager/blob/master/src/Manager.php
         */
        $pattern =
            // See https://regex101.com/r/jS5fX0/5
            '[^\w]' . // Must not start with any alphanum or _
            '(?<!->)' . // Must not start with ->
            '(' . implode( '|', $functions ) . ')' .// Must start with one of the functions
            "\(" .// Match opening parentheses
            "[\'\"]" .// Match " or '
            // Start a new group to match:
            '(.+)' .
            "[\'\"]" .// Closing quote
            "[\),]"  // Close parentheses or new parameter
        ;

        $allMatches = [];
        if ( !empty( $filePath ) ) {
            try {
                $fileContent = File::get( $filePath );
            }
            catch ( \Exception $e ) {
                return $allMatches;
            }

            if ( preg_match_all( "/$pattern/siU", $fileContent, $matches ) ) {
                if ( !empty( $matches[ 2 ] ) ) {
                    foreach ( $matches[ 2 ] as $str ) {
                        $str = stripslashes( $str );
                        $str = preg_replace( '/^[m|a]./', '', $str );
                        if ( !in_array( $str, $allMatches ) ) {
                            array_push( $allMatches, $str );
                        }
                    }
                }
            }
        }
        return $allMatches;
    }
}
