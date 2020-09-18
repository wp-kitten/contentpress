<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PluginsManager;
use App\Helpers\PluginUpdater;
use App\Helpers\ScriptsManager;
use App\Helpers\StatsHelper;
use App\Helpers\Util;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class DashboardController extends AdminControllerBase
{
    public function index()
    {
        if ( $this->current_user()->can( 'read' ) ) {
            ScriptsManager::enqueueFooterScript( 'dashboard-index.js', asset( '_admin/js/dashboard/index.js' ) );
            return view( 'admin.dashboard.index' )->with( [
                'widgets' => cp_get_registered_dashboard_widgets(),
            ] );
        }
        return $this->_forbidden();
    }

    /**
     * Language switcher
     * @param string $code
     * @return RedirectResponse
     */
    public function lang( $code )
    {
        App::setLocale( $code );
        cp_set_user_meta( 'backend_user_current_language', $code );
        return redirect()->back();
    }

    public function showEditDashboardView()
    {
        if ( $this->current_user()->can( 'edit_dashboard' ) ) {

            add_filter( 'contentpress/admin/right_sidebar/show', '__return_true' );

            ScriptsManager::enqueueStylesheet( 'dragula.min.css', asset( 'vendor/dragula/dragula.min.css' ) );
            ScriptsManager::enqueueStylesheet( 'dashboard-edit.css', asset( '_admin/css/dashboard/edit.css' ) );

            ScriptsManager::enqueueFooterScript( 'dragula.min.js', asset( 'vendor/dragula/dragula.min.js' ) );
            ScriptsManager::enqueueFooterScript( 'dashboard-edit.js', asset( '_admin/js/dashboard/edit.js' ) );

            Util::getAvailableWidgets();
            return view( 'admin.dashboard.edit' )->with( [
                'widgets' => cp_get_registered_dashboard_widgets(),
            ] );
        }
        return $this->_forbidden();
    }

    public function __refreshStats()
    {
        StatsHelper::getInstance()->refreshStats();
        return redirect()->back()->with( [
            'class' => 'success',
            'text' => __( 'a.Statistics updated.' ),
        ] );
    }

    public function showUpdatesView()
    {
        $updatesInfo = $this->options->getOption( 'contentpress_updates', [ 'plugins' => [], 'themes' => [], 'core' => [] ] );

        return view( 'admin.dashboard.updates' )->with( [
            'core' => ( isset( $updatesInfo[ 'core' ] ) ? $updatesInfo[ 'core' ] : [] ),
            'plugins' => ( isset( $updatesInfo[ 'plugins' ] ) ? $updatesInfo[ 'plugins' ] : [] ),
            'themes' => ( isset( $updatesInfo[ 'themes' ] ) ? $updatesInfo[ 'themes' ] : [] ),
        ] );
    }

    public function __checkForUpdates()
    {
        //#! Run the Updater
        app()->get( 'cp.updater' )->run();

        //#! Get the info from option: contentpress_updates
        $updatesInfo = $this->options->getOption( 'contentpress_updates', [ 'plugins' => [], 'themes' => [], 'core' => [] ] );
        if ( !is_array( $updatesInfo ) ) {
            $updatesInfo = [ 'plugins' => [], 'themes' => [], 'core' => [] ];
        }
        if ( !isset( $updatesInfo[ 'plugins' ] ) ) {
            $updatesInfo[ 'plugins' ] = [];
        }
        if ( !isset( $updatesInfo[ 'themes' ] ) ) {
            $updatesInfo[ 'themes' ] = [];
        }
        if ( !isset( $updatesInfo[ 'core' ] ) ) {
            $updatesInfo[ 'core' ] = [];
        }

        $errors = [];
        return redirect()->route( 'admin.dashboard.updates' )->with( [
            'plugins' => ( isset( $updatesInfo[ 'plugins' ] ) ? $updatesInfo[ 'plugins' ] : [] ),
            'themes' => ( isset( $updatesInfo[ 'themes' ] ) ? $updatesInfo[ 'themes' ] : [] ),
            'warnings' => $errors,
        ] );
    }

    public function __update_plugin( $file_name )
    {
        $pluginUpdater = new PluginUpdater();

        $pluginInfo = PluginsManager::getInstance()->getPluginInfo( $file_name );
        $pluginName = $pluginInfo->display_name;

        //#! Update the plugin
        $result = $pluginUpdater->update( $file_name );

        $class = 'warning';
        $m = __( 'a.An error occurred and the plugin :name could not be updated.', [ 'name' => $pluginName ] );
        if ( $result ) {
            $class = 'success';
            $m = __( 'a.:plugin_name has been updated.', [ 'plugin_name' => $pluginName ] );
        }

        return redirect()->route( 'admin.dashboard.updates' )->with( 'message', [
            'class' => $class,
            'text' => $m,
        ] );
    }

    public function __update_core( $version )
    {
        if ( empty( $version ) ) {
            return redirect()->route( 'admin.dashboard.updates' )->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The version to update core is missing.' ),
            ] );
        }

        //#! Put website under maintenance
        Util::setUnderMaintenance( true );

        //#! Get archive from api server
        $response = Http::get( path_combine( CONTENTPRESS_API_URL, 'get_update/core', $version ) );

        if ( empty( $response ) ) {
            Util::setUnderMaintenance( false );
            return redirect()->route( 'admin.dashboard.updates' )->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.There was no response from the api server.' ),
            ] );
        }

        //#! Download content locally
        $fileSavePath = public_path( 'uploads/tmp/contentpress.zip' );
        if ( !File::put( $fileSavePath, $response ) ) {
            Util::setUnderMaintenance( false );
            return redirect()->route( 'admin.dashboard.updates' )->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.An error occurred when trying to create the local download file. Check for permissions.' ),
            ] );
        }

        //#! Extract to root
        $zip = new \ZipArchive();
        if ( $zip->open( $fileSavePath ) !== false ) {
            $zip->extractTo( base_path() );
            $zip->close();
        }
        else {
            Util::setUnderMaintenance( false );
            return redirect()->route( 'admin.dashboard.updates' )->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.An error occurred when trying to extract the downloaded archive. Check for permissions.' ),
            ] );
        }

        //#! Delete temp file
        File::delete( $fileSavePath );

        //#! Remove website from under maintenance
        Util::setUnderMaintenance( false );

        return redirect()->route( 'admin.dashboard.updates' )->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.Core updated to version :version.', [ 'version' => $version ] ),
        ] );
    }

    public function __reinstallApp()
    {
        try {
            //#! Clear cache
            Artisan::call( 'cp:cache' );

            //#! Delete all uploaded files
            $uploadsDir = public_path( 'uploads' );
            File::deleteDirectory( $uploadsDir, true );

            //#! Reinstall
            Artisan::call( 'cp:setup', [
                '--n' => true,
                '--s' => true,
            ] );
        }
        catch ( \Exception $e ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.An error occurred while trying to reinstall the application: :error', [ 'error' => $e->getMessage() ] ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.The application has been reinstalled and the dummy data imported.' ),
        ] );
    }

    public function __clearAppCache()
    {
        try {
            Artisan::call( 'cp:cache' );
        }
        catch ( \Exception $e ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.An error occurred while trying to clear the application cache: :error', [ 'error' => $e->getMessage() ] ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.The application cache has been cleared.' ),
        ] );
    }
}
