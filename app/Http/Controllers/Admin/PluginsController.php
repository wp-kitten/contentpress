<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Cache;
use App\Helpers\Marketplace;
use App\Helpers\PluginsManager;
use App\Helpers\ScriptsManager;
use App\Models\Options;
use Illuminate\Support\Facades\Http;

class PluginsController extends AdminControllerBase
{
    public function index()
    {
        if ( !cp_current_user_can( 'list_plugins' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::enqueueStylesheet( 'admin.plugins-styles', asset( '_admin/css/plugins/index.css' ) );
        ScriptsManager::localizeScript( 'plugins-script-locale', 'PluginsLocale', [
            'text_confirm_delete' => __( 'a.Are you sure you want to delete this plugin?' ),
            'text_error_plugin_name_not_found' => __( 'a.The specified plugin was not found.' ),
            'text_error' => __( 'a.Error' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'plugins-index.js', asset( '_admin/js/plugins/index.js' ) );

        return view( 'admin.plugins.index' )->with( [
            'pluginsManager' => PluginsManager::getInstance(),
            'all_plugins' => $this->pluginsManager->getAllPlugins(),
            'active_plugins' => PluginsManager::getInstance()->getActivePlugins(),
            'enabled_languages' => ( new Options() )->getOption( 'enabled_languages', [] ),
        ] );
    }

    public function renderAddView()
    {
        if ( !cp_current_user_can( 'install_plugins' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::enqueueStylesheet( 'dropify.min.css', asset( 'vendor/dropify/css/dropify.min.css' ) );
        ScriptsManager::enqueueFooterScript( 'dropify.min.js', asset( 'vendor/dropify/js/dropify.min.js' ) );
        ScriptsManager::enqueueFooterScript( 'DropifyImageUploader.js', asset( '_admin/js/DropifyImageUploader.js' ) );
        ScriptsManager::localizeScript( 'plugins-script-locale', 'PluginsLocale', [
            'text_plugin_uploaded' => __( 'a.Plugin uploaded' ),
            'text_plugin_deleted' => __( 'a.Plugin deleted' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'plugins-add.js', asset( '_admin/js/plugins/add.js' ) );
        ScriptsManager::enqueueFooterScript( 'plugins-index.js', asset( '_admin/js/plugins/index.js' ) );

        /**@var $cache Cache */
        $cache = app( 'vp.cache' );
        $defaultPlugins = $cache->get( 'cp_default_plugins', [] ); // get from cache

        if ( empty( $defaultPlugins ) ) {
            if ( '' != VALPRESS_API_URL ) {
                $response = Http::get( VALPRESS_API_URL . '/plugins', [ 'verify' => false ] )->json();
                if ( is_array( $response ) && isset( $response[ 'data' ] ) ) {
                    $defaultPlugins = $response[ 'data' ];
                    $cache->set( 'cp_default_plugins', $defaultPlugins );
                }
            }
        }

        return view( 'admin.plugins.add' )->with( [
            'plugins' => $defaultPlugins,
            'pluginsManager' => PluginsManager::getInstance(),
            'enabled_languages' => ( new Options() )->getOption( 'enabled_languages', [] ),
        ] );
    }

    public function __activatePlugins()
    {
        if ( !cp_current_user_can( 'activate_plugins' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        //#! If
        $plugins = [];
        if ( $this->request->has( '__activate_plugins' ) ) {
            $plugins = $this->request->get( 'plugins' );
            if ( empty( $plugins ) ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger',
                    'text' => __( 'a.You need to specify at least one plugin.' ),
                ] );
            }
            foreach ( $plugins as $plugin ) {
                do_action( 'valpress/plugin/activate', $plugin );
            }
        }
        elseif ( $this->request->has( '__deactivate_plugins' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.Not yet implemented.' ),
            ] );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => 'Plugin(s) activated',
        ] );
    }

    public function __activatePlugin__GET( $plugin_dir_name )
    {
        if ( !cp_current_user_can( 'activate_plugins' ) ) {
            return $this->_forbidden();
        }

        do_action( 'valpress/plugin/activate', $plugin_dir_name );
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => 'Plugin activated',
        ] );
    }

    public function __deactivatePlugin__GET( $plugin_dir_name )
    {
        if ( !cp_current_user_can( 'deactivate_plugins' ) ) {
            return $this->_forbidden();
        }

        do_action( 'valpress/plugin/deactivate', $plugin_dir_name );
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.Plugin deactivated' ),
        ] );
    }

    public function __delete__GET( $plugin_dir_name )
    {
        if ( !cp_current_user_can( 'delete_plugins' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        if ( $this->pluginsManager->isActivePlugin( $plugin_dir_name ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You cannot delete an active plugin.' ),
            ] );
        }

        do_action( 'valpress/plugin/delete', $plugin_dir_name );
        return redirect()->route( 'admin.plugins.all' )->with( 'message', $this->pluginsManager->getNotice() );
    }

    public function __deactivatePlugins__POST()
    {
        if ( !cp_current_user_can( 'deactivate_plugins' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        $plugins = $this->request->get( 'plugins' );
        if ( empty( $plugins ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.Please select at least one plugin.' ),
            ] );
        }

        foreach ( $plugins as $plugin_dir_name ) {
            do_action( 'valpress/plugin/deactivate', $plugin_dir_name );
        }

        $m = __( 'a.The selected plugin has been deactivated.' );
        if ( count( $plugins ) > 1 ) {
            $m = __( 'a.The selected plugins have been deactivated.' );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => $m,
        ] );
    }

    public function __viewMarketplace()
    {
        ScriptsManager::enqueueStylesheet( 'admin.plugins-styles', asset( '_admin/css/plugins/index.css' ) );

        $plugins = ( new Marketplace() )->getPlugins();
        return view( 'admin.plugins.marketplace' )->with( [
            'pluginsManager' => PluginsManager::getInstance(),
            'plugins' => $plugins,
            'enabled_languages' => ( new Options() )->getOption( 'enabled_languages', [] ),
        ] );
    }

    public function __marketplaceInstallPlugin( $plugin_dir_name, $version )
    {
        if ( empty( $plugin_dir_name ) || empty( $version ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.Invalid request: some values are missing.' ),
            ] );
        }
        elseif ( !cp_current_user_can( 'install_plugins' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }
        elseif ( !cp_current_user_can( 'activate_plugins' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        try {
            if ( $installed = ( new Marketplace() )->installPlugin( $plugin_dir_name, $version ) ) {
                do_action( 'valpress/plugin/activate', $plugin_dir_name );
            }
        }
        catch ( \Exception $e ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => $e->getMessage(),
            ] );
        }

        $pluginInfo = $this->pluginsManager->getPluginInfo( $plugin_dir_name );

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.The plugin :name has been successfully installed and activated.', [ 'name' => $pluginInfo->display_name ] ),
        ] );
    }

    /**
     * Clear the marketplace cache for plugins
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refresh()
    {
        ( new Marketplace() )->clearCache( Marketplace::CACHE_KEY_PLUGINS );
        return redirect()->route( 'admin.plugins.marketplace' );
    }
}
