<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Cache;
use App\Helpers\PluginsManager;
use App\Helpers\ScriptsManager;
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
        $cache = app( 'cp.cache' );
        $defaultPlugins = $cache->get( 'cp_default_plugins', [] ); // get from cache

        if ( empty( $defaultPlugins ) ) {
            $response = Http::get( CONTENTPRESS_API_URL . '/plugins', [ 'verify' => false ] )->json();
            if ( is_array( $response ) && isset( $response[ 'data' ] ) ) {
                $defaultPlugins = $response[ 'data' ];
                $cache->set( 'cp_default_plugins', $defaultPlugins );
            }
        }

        return view( 'admin.plugins.add' )->with( [
            'plugins' => $defaultPlugins,
            'pluginsManager' => PluginsManager::getInstance(),
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
                do_action( 'contentpress/plugin/activate', $plugin );
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

        do_action( 'contentpress/plugin/activate', $plugin_dir_name );
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

        do_action( 'contentpress/plugin/deactivate', $plugin_dir_name );
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

        do_action( 'contentpress/plugin/delete', $plugin_dir_name );
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
            do_action( 'contentpress/plugin/deactivate', $plugin_dir_name );
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
}
