<?php

namespace App\Helpers;

use Illuminate\Translation\FileLoader;

/**
 * Class TranslationsLoader
 *
 * Helper class allowing external components (such as themes, plugins) to load their own translation files
 *
 * Standard Singleton
 *
 * @package App\Helpers
 */
class TranslationsLoader
{
    /**
     * Stores the list of all registered namespaces
     * @var array
     */
    protected $namespaces = [];

    /**
     * @var FileLoader|null
     */
    protected $loader = null;

    /**
     * Stores the reference to the instance of this class
     * @var null|TranslationsLoader
     */
    private static $_instance = null;

    /**
     * TranslationsLoader constructor.
     */
    private function __construct()
    {
        $this->loader = app( 'translation.loader' );
    }

    /**
     * Retrieve the reference to the instance of this class
     * @return TranslationsLoader|null
     */
    public static function getInstance()
    {
        if ( is_null( self::$_instance ) || !( self::$_instance instanceof self ) ) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Register the path to the language file that will be loaded based on the current locale.
     * Themes and plugins should use this function in the "valpress/app/loaded" action
     *
     * @param string $namespace The namespace to use for grouping the translations
     * @param string $langsDirPath The path to the languages directory, usually named "lang"
     * @param string $fileName The name of the translation file (without the .php file extension). It should always be just "m"
     */
    public function register( $namespace, $dirPath, $fileName = 'm' )
    {
        //#! Prevent repetitive calls
        $_fullPath = path_combine( $dirPath, $fileName );
        if ( isset( $this->namespaces[ $namespace ] ) && in_array( $_fullPath, $this->namespaces[ $namespace ] ) ) {
            return $this;
        }
        //#! Load file
        $this->loader->addNamespace( $namespace, $dirPath );
        $this->loader->load( app()->getLocale(), $fileName, $namespace );

        //#! Cache locally - prevents repetitive loading
        if ( !isset( $this->namespaces[ $namespace ] ) ) {
            $this->namespaces[ $namespace ] = [ $_fullPath ];
        }
        else {
            array_push( $this->namespaces[ $namespace ], $_fullPath );
        }
        return $this;
    }
}
