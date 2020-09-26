<?php

namespace App\Http\Controllers;

use App\Helpers\Cache;
use App\Helpers\MediaHelper;
use App\Helpers\PluginsManager;
use App\Helpers\ThemesManager;
use App\Models\Language;
use App\Models\Options;
use App\Models\Settings;
use App\Models\UserMeta;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var Language|null
     */
    protected $language = null;

    /**
     * @var Settings|null
     */
    protected $settings = null;

    /**
     * @var Options|null
     */
    protected $options = null;

    /**
     * @var array|Request|string|null
     */
    protected $request = null;

    /**
     * @var UserMeta
     */
    protected $userMeta = null;

    /**
     * @var PluginsManager|null
     */
    protected $pluginsManager = null;

    /**
     * Stores the reference to the instance of the ThemesManager class
     * @var ThemesManager|null
     */
    protected $themesManager = null;

    /**
     * Holds the reference to the instance of the MediaHelper class
     * @var MediaHelper|null
     */
    protected $media = null;

    /**
     * Stores the reference to the instance of the App\Helpers\Cache class
     * @var Cache|null
     */
    protected $cache = null;

    /**
     * Controller constructor.
     *
     * Check if the default languages option exists and if it doesn't it will be created
     * Checks if the language directories exists for each enabled language and creates them if they don't exist and copies the language file into them
     * Sets the session('frontend_language_dirs_checked') setting so this check is only ran one time
     */
    public function __construct()
    {
        $this->language = new Language();
        $this->settings = new Settings();
        $this->options = new Options();
        $this->userMeta = new UserMeta();
        $this->pluginsManager = PluginsManager::getInstance();
        $this->themesManager = ThemesManager::getInstance();
        $this->request = \request();
        $this->media = new MediaHelper();
        $enabledLanguages = [];

        //#! Check to see whether or not there are any enabled languages
        if ( Schema::hasTable( 'options' ) ) {
            $enabledLanguages = $this->options->getOption( 'enabled_languages', [] );
            if ( empty( $enabledLanguages ) ) {
                //#! Add the default language and save the option
                array_push( $enabledLanguages, 'en' );
                $opt = Options::where('name', 'enabled_languages')->first();
                $opt->value = maybe_serialize( $enabledLanguages );
                $opt->save();
            }
        }

        $this->cache = app( 'cp.cache' );

        if ( !session()->get( 'system_language_dirs_check' ) ) {
            $sourceDirPath = resource_path( 'lang/en' );

            //#! Copy the default lang dir for each enabled language if it doesn't exist
            foreach ( $enabledLanguages as $language ) {
                $dirPath = resource_path( "lang/{$language}" );
                if ( !File::isDirectory( $dirPath ) ) {
                    File::copyDirectory( $sourceDirPath, $dirPath );
                }
            }
            session()->put( 'system_language_dirs_check', true );
        }
    }

    /**
     * @return Authenticatable|null
     */
    public function current_user()
    {
        return ( auth()->check() ? auth()->user() : null );
    }

    /**
     * Check to see if this is a multilanguage instance
     * @return bool
     */
    public function isMultilanguage()
    {
        return ( count( $this->options->getEnabledLanguages() ) > 1 );
    }

    /**
     * Render the 404 template
     * @param array $data Data to send to the view
     * @return Factory|View
     */
    public function _not_found( array $data = [] )
    {
        return response()->view( '404', $data, 404 );
    }

    /**
     * @return array|Request|string|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Settings|null
     */
    public function getSettings(): ?Settings
    {
        return $this->settings;
    }
}
