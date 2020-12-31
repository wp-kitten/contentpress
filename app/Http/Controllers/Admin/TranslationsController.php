<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\VPML;
use App\Helpers\PluginsManager;
use App\Helpers\ThemesManager;
use App\Helpers\TranslationManager;
use App\Models\Options;
use App\Models\Settings;
use Illuminate\Support\Facades\File;

class TranslationsController extends PostsController
{
    //#! GET
    public function index()
    {
        if ( !cp_current_user_can( 'manage_translations' ) ) {
            return $this->_forbidden();
        }

        $translationsManager = new TranslationManager();

        //#! If editing
        $editedType = ( $this->request->has( 'type' ) ? $this->request->get( 'type' ) : '' );
        $editedLanguageCode = ( $this->request->has( 'code' ) ? $this->request->get( 'code' ) : '' );
        $edited_fn = ( $this->request->has( 'fn' ) ? $this->request->get( 'fn' ) : '' );
        //! For plugins & themes only
        $edited_dirname = ( $this->request->has( 'dir' ) ? $this->request->get( 'dir' ) : '' );

        return view( 'admin.translations.index' )->with( [
            'default_language_code' => VPML::getDefaultLanguageCode(),
            'edited_type' => $editedType,
            'edited_language_code' => $editedLanguageCode,
            'edited_language_file' => $edited_fn,
            'edited_dir' => $edited_dirname,
            'translations_manager' => $translationsManager,
            'enabled_languages' => ( new Options() )->getOption( 'enabled_languages', [] ),
        ] );
    }

    //#! GET
    public function plugins()
    {
        if ( !cp_current_user_can( 'manage_translations' ) ) {
            return $this->_forbidden();
        }

        $pluginsManager = PluginsManager::getInstance();
        $translationsManager = new TranslationManager();

        //#! If editing
        $editedType = ( $this->request->has( 'type' ) ? $this->request->get( 'type' ) : '' );
        $editedLanguageCode = ( $this->request->has( 'code' ) ? $this->request->get( 'code' ) : '' );
        $edited_fn = ( $this->request->has( 'fn' ) ? $this->request->get( 'fn' ) : '' );
        //! For plugins & themes only
        $edited_dirname = ( $this->request->has( 'dir' ) ? $this->request->get( 'dir' ) : '' );

        return view( 'admin.translations.plugins' )->with( [
            'plugins' => $pluginsManager->getAllPlugins(),
            'has_plugins' => !empty( $pluginsManager->getAllPlugins() ),
            'default_language_code' => VPML::getDefaultLanguageCode(),
            'edited_type' => $editedType,
            'edited_language_code' => $editedLanguageCode,
            'edited_language_file' => $edited_fn,
            'edited_dir' => $edited_dirname,
            'translations_manager' => $translationsManager,
            'enabled_languages' => ( new Options() )->getOption( 'enabled_languages', [] ),
        ] );
    }

    //#! GET
    public function themes()
    {
        if ( !cp_current_user_can( 'manage_translations' ) ) {
            return $this->_forbidden();
        }

        $themesManager = ThemesManager::getInstance();
        $translationsManager = new TranslationManager();

        //#! If editing
        $editedType = ( $this->request->has( 'type' ) ? $this->request->get( 'type' ) : '' );
        $editedLanguageCode = ( $this->request->has( 'code' ) ? $this->request->get( 'code' ) : '' );
        $edited_fn = ( $this->request->has( 'fn' ) ? $this->request->get( 'fn' ) : '' );
        //! For plugins & themes only
        $edited_dirname = ( $this->request->has( 'dir' ) ? $this->request->get( 'dir' ) : '' );

        return view( 'admin.translations.themes' )->with( [
            'themes' => $themesManager->getInstalledThemes(),
            'has_themes' => !empty( $themesManager->getInstalledThemes() ),
            'default_language_code' => VPML::getDefaultLanguageCode(),
            'edited_type' => $editedType,
            'edited_language_code' => $editedLanguageCode,
            'edited_language_file' => $edited_fn,
            'edited_dir' => $edited_dirname,
            'translations_manager' => $translationsManager,
            'enabled_languages' => ( new Options() )->getOption( 'enabled_languages', [] ),
        ] );
    }

    //#! POST
    public function __updateTranslation()
    {
        $this->request->validate( [
            'language_file' => 'required',
            'lang_code' => 'required',
            'type' => 'required',
            //#! Required only for plugins and themes
            'dir_name' => '',
        ] );

        $type = $this->request->get( 'type' );
        $dir_name = $this->request->get( 'dir_name' );
        $lang_code = $this->request->get( 'lang_code' );
        $language_file = $this->request->get( 'language_file' );
        $fileData = $this->request->get( 'file_data' );
        if ( empty( $fileData ) ) {
            $fileData = '<?php return []; ' . PHP_EOL;
        }

        $translationsManager = new TranslationManager();
        $languagesDirPath = $translationsManager->getLanguagesDirPath( $lang_code, $type, $dir_name );
        $languageFilePath = path_combine( $languagesDirPath, $language_file );

        if ( !File::isFile( $languageFilePath ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The language file was not found.' ),
            ] );
        }

        File::put( $languageFilePath, $fileData );

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.Translation file successfully updated.' ),
        ] );
    }

    //#! POST
    public function __pluginCreateTranslation()
    {
        $langCode = $this->request->get( 'lang_code' );
        $pluginDirName = $this->request->get( 'plugin_dir_name' );
        if ( empty( $langCode ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( "a.The language code is missing." ),
            ] );
        }
        if ( empty( $pluginDirName ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( "a.The plugin's directory name is missing." ),
            ] );
        }

        //#! Make sure the default language dir exists
        $defaultLanguage = (new Settings())->getSetting( 'default_language' );
        $defaultLangDir = public_path( "plugins/{$pluginDirName}/lang/{$defaultLanguage}" );
        if ( !File::isDirectory( $defaultLangDir ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( "a.The plugin's default language directory was not found. Unable to create a new translation." ),
            ] );
        }
        $destDir = public_path( "plugins/{$pluginDirName}/lang/{$langCode}" );
        try {
            File::copyDirectory( $defaultLangDir, $destDir );
        }
        catch ( \Exception $e ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( "a.An error occurred: :error", [ 'error' => $e->getMessage() ] ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( "a.Translation created." ),
        ] );
    }

    //#! POST
    public function __themeCreateTranslation()
    {
        $langCode = $this->request->get( 'lang_code' );
        $themeDirName = $this->request->get( 'theme_dir_name' );
        if ( empty( $langCode ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( "a.The language code is missing." ),
            ] );
        }
        if ( empty( $themeDirName ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( "a.The theme's directory name is missing." ),
            ] );
        }

        //#! Make sure the default language dir exists
        $defaultLanguage = (new Settings())->getSetting( 'default_language' );
        $defaultLangDir = public_path( "themes/{$themeDirName}/lang/{$defaultLanguage}" );
        if ( !File::isDirectory( $defaultLangDir ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( "a.The theme's default language directory was not found. Unable to create a new translation." ),
            ] );
        }
        $destDir = public_path( "themes/{$themeDirName}/lang/{$langCode}" );
        try {
            File::copyDirectory( $defaultLangDir, $destDir );
        }
        catch ( \Exception $e ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( "a.An error occurred: :error", [ 'error' => $e->getMessage() ] ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( "a.Translation created." ),
        ] );
    }
}
