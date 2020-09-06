<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class TranslationManager
{
    /**
     * The name of the file storing the admin translation strings
     * @var string
     */
    const LANG_FILE_ADMIN = 'a.php';
    /**
     * The name of the file storing the frontend translation strings
     * @var string
     */
    const LANG_FILE_FRONTEND = 'a.php';

    /**
     * Holds the path to the views directory
     * @var string
     */
    private $viewsDirPath;
    /**
     * Holds the path to the languages directory
     * @var string
     */
    private $languagesDirPath;
    /**
     * Holds the list of files found in the $viewsDirPath
     * @var array
     */
    private $filesFound;
    /**
     * Holds the list of all enabled languages
     * @var array
     */
    private $enabledLanguages;
    /**
     * Holds the code of the default language
     * @var string
     */
    private $defaultLanguage;
    /**
     * Holds the list of the directories to scan for translation strings
     * @var array
     */
    private $viewFilePaths;

    public function __construct( $defaultLanguage, array $enabledLanguages, $viewFilePaths = [] )
    {
        $this->viewsDirPath = base_path( 'views' );
        $this->languagesDirPath = resource_path( 'lang' );
        $this->filesFound = File::allFiles( $this->viewsDirPath );
        $this->enabledLanguages = $enabledLanguages;
        $this->defaultLanguage = $defaultLanguage;
        $this->viewFilePaths = $viewFilePaths;
    }

    /**
     * Scan the appropriate directory and extract all translation strings found
     * @param string $fileName
     * @return array
     */
    public function getLocalizedStrings( $fileName = self::LANG_FILE_FRONTEND )
    {
        $strings = [];
        if ( $fileName == self::LANG_FILE_ADMIN ) {
            return $this->__getStringsFromAdmin();
        }
        elseif ( $fileName == self::LANG_FILE_FRONTEND ) {
            return $this->__getStringsFromFrontend();
        }
        return $strings;
    }

    //Sync translations file: scan for strings, update file (!! keep valid entries + translations)
    public function sync( $languageCode, $fileName = self::LANG_FILE_FRONTEND )
    {
        //.. todo
    }

    /**
     * Get translations from the specified $fileName
     * @param string $languageCode
     * @param string $fileName
     * @return array|mixed
     */
    public function getTranslations( $languageCode, $fileName = self::LANG_FILE_FRONTEND )
    {
        $translations = [];
        if ( !$this->isValidFn( $fileName ) ) {
            return $translations;
        }
        $filePath = path_combine( $this->languagesDirPath, $languageCode, $fileName );
        if ( !File::isFile( $filePath ) ) {
            return $translations;
        }
        $translations = include( $filePath );
        return $translations;
    }

    /**
     * Make sure the $fileName is a valid & recognized language file name. The $fileName is automatically sanitized.
     * @param string $fileName
     * @return bool
     */
    public function isValidFn( $fileName = self::LANG_FILE_FRONTEND )
    {
        return in_array( sanitize_file_name( $fileName ), [ self::LANG_FILE_FRONTEND, self::LANG_FILE_ADMIN ] );
    }

    //============================================================================

    protected function extractLocalizedStrings( array $files )
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
        if ( !empty( $files ) ) {
            foreach ( $files as $fileInfo ) {
                /**@var \SplFileInfo $fileInfo */
                if ( !$fileInfo->isFile() ) {
                    continue;
                }

                try {
                    $fileContent = File::get( $fileInfo->getPathname() );
                }
                catch ( \Exception $e ) {
                    continue;
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
        }

        return $allMatches;
    }

    private function __getStringsFromAdmin()
    {
        $appStrings = [];
        $viewsStrings = [];

        $dirPath = base_path( 'app' );
        if ( File::isDirectory( $dirPath ) ) {
            $appStrings = $this->extractLocalizedStrings( File::allFiles( $dirPath ) );
        }
        $dirPath = base_path( 'views/admin' );
        if ( File::isDirectory( $dirPath ) ) {
            $viewsStrings = $this->extractLocalizedStrings( File::allFiles( $dirPath ) );
        }

        if ( empty( $appStrings ) && empty( $viewsStrings ) ) {
            return [];
        }

        return array_unique( array_merge( $appStrings, $viewsStrings ) );
    }

    //#! TODO: Later on, include the path to plugins
    private function __getStringsFromFrontend()
    {
        $dirPath = base_path( 'views/' . cp_get_current_theme_name() );
        if ( File::isDirectory( $dirPath ) ) {
            return $this->extractLocalizedStrings( File::allFiles( $dirPath ) );
        }
        return [];
    }

}
