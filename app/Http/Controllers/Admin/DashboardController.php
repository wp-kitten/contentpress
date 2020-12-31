<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PluginsManager;
use App\Helpers\PluginUpdater;
use App\Helpers\ScriptsManager;
use App\Helpers\StatsHelper;
use App\Helpers\Theme;
use App\Helpers\ThemeUpdater;
use App\Helpers\UserNotices;
use App\Helpers\Util;
use App\Models\Options;
use Illuminate\Http\Client\Response;
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
                'enabled_languages' => ( new Options() )->getOption( 'enabled_languages', [] ),
            ] );
        }
        return $this->_forbidden();
    }

    /**
     * Language switcher
     * @param string $code
     * @return RedirectResponse
     */
    public function lang( string $code )
    {
        App::setLocale( $code );
        vp_set_user_meta( 'backend_user_current_language', $code );
        session()->put( 'backend_user_current_language', $code );
        return redirect()->back();
    }

    public function showEditDashboardView()
    {
        if ( $this->current_user()->can( 'edit_dashboard' ) ) {

            add_filter( 'valpress/admin/right_sidebar/show', '__return_true' );

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

    public function showCommandsView()
    {
        return view( 'admin.dashboard.commands' )->with( [
            'has_composer' => File::isFile( base_path( 'composer.json' ) ),
        ] );
    }

    public function __refreshStats()
    {
        if ( !vp_current_user_can( [ 'super_admin', 'administrator' ] ) ) {
            return redirect()->back()->with( [
                'class' => 'success',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }
        StatsHelper::getInstance()->refreshStats();
        return redirect()->back()->with( [
            'class' => 'success',
            'text' => __( 'a.Statistics updated.' ),
        ] );
    }

    public function showUpdatesView()
    {
        $updatesInfo = $this->options->getOption( 'valpress_updates', [ 'plugins' => [], 'themes' => [], 'core' => [] ] );

        return view( 'admin.dashboard.updates' )->with( [
            'core' => ( isset( $updatesInfo[ 'core' ] ) ? $updatesInfo[ 'core' ] : [] ),
            'plugins' => ( isset( $updatesInfo[ 'plugins' ] ) ? $updatesInfo[ 'plugins' ] : [] ),
            'themes' => ( isset( $updatesInfo[ 'themes' ] ) ? $updatesInfo[ 'themes' ] : [] ),
        ] );
    }

    public function __checkForUpdates()
    {
        if ( !vp_current_user_can( [ 'super_admin', 'administrator' ] ) ) {
            return redirect()->back()->with( [
                'class' => 'success',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        //#! Run the Updater
        app()->get( 'cp.updater' )->run();

        //#! Get the info from option: valpress_updates
        $updatesInfo = $this->options->getOption( 'valpress_updates', [ 'plugins' => [], 'themes' => [], 'core' => [] ] );
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

        $errors = UserNotices::getInstance()->getAll();
        return redirect()->route( 'admin.dashboard.updates' )->with( [
            'plugins' => ( isset( $updatesInfo[ 'plugins' ] ) ? $updatesInfo[ 'plugins' ] : [] ),
            'themes' => ( isset( $updatesInfo[ 'themes' ] ) ? $updatesInfo[ 'themes' ] : [] ),
            'warnings' => $errors,
        ] );
    }

    public function __forceCheckForUpdates()
    {
        if ( !vp_current_user_can( [ 'super_admin', 'administrator' ] ) ) {
            return redirect()->back()->with( [
                'class' => 'success',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        //#! Run the Updater
        app()->get( 'cp.updater' )->run( true );

        //#! Get the info from option: valpress_updates
        $updatesInfo = $this->options->getOption( 'valpress_updates', [ 'plugins' => [], 'themes' => [], 'core' => [] ] );
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

        $errors = UserNotices::getInstance()->getAll();
        return redirect()->route( 'admin.dashboard.updates' )->with( [
            'plugins' => ( isset( $updatesInfo[ 'plugins' ] ) ? $updatesInfo[ 'plugins' ] : [] ),
            'themes' => ( isset( $updatesInfo[ 'themes' ] ) ? $updatesInfo[ 'themes' ] : [] ),
            'warnings' => $errors,
        ] );
    }

    public function __update_plugin( string $file_name )
    {
        if ( !vp_current_user_can( [ 'super_admin', 'administrator' ] ) ) {
            return redirect()->back()->with( [
                'class' => 'success',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

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

    public function __update_theme( string $file_name )
    {
        if ( !vp_current_user_can( [ 'super_admin', 'administrator' ] ) ) {
            return redirect()->back()->with( [
                'class' => 'success',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        $updater = new ThemeUpdater();
        $theme = new Theme( $file_name );
        $result = $updater->update( $file_name );

        $class = 'warning';
        $m = __( 'a.An error occurred and the theme :name could not be updated.', [ 'name' => $theme->get( 'display_name' ) ] );
        if ( $result ) {
            $class = 'success';
            $m = __( 'a.:theme_name has been updated.', [ 'theme_name' => $theme->get( 'display_name' ) ] );
        }

        return redirect()->route( 'admin.dashboard.updates' )->with( 'message', [
            'class' => $class,
            'text' => $m,
        ] );
    }

    public function __update_core( string $version )
    {
        if ( !vp_current_user_can( [ 'super_admin', 'administrator' ] ) ) {
            return redirect()->back()->with( [
                'class' => 'success',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        if ( empty( $version ) ) {
            return redirect()->route( 'admin.dashboard.updates' )->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The version to update core is missing.' ),
            ] );
        }

        try {
            //#! Put website from under maintenance
            Util::setUnderMaintenance( true );

            $this->__updateCore( $version );

            //#! Remove website from under maintenance
            Util::setUnderMaintenance( false );
        }
        catch ( \Exception $e ) {
            return redirect()->route( 'admin.dashboard.updates' )->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.An error occurred: :error', [ 'error' => $e->getMessage() ] ),
            ] );
        }

        return redirect()->route( 'admin.dashboard.updates' )->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.Core updated to version :version.', [ 'version' => $version ] ),
        ] );
    }

    public function __cmdReinstall()
    {
        if ( !vp_current_user_can( [ 'super_admin', 'administrator' ] ) ) {
            return redirect()->back()->with( [
                'class' => 'success',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        try {
            //#! Put website from under maintenance
            Util::setUnderMaintenance( true );

            $this->__updateCore( VALPRESS_VERSION );

            //#! Remove website from under maintenance
            Util::setUnderMaintenance( false );
        }
        catch ( \Exception $e ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.An error occurred: :error', [ 'error' => $e->getMessage() ] ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.The application has been reset and the default data imported.' ),
        ] );
    }

    public function __cmdReset()
    {
        if ( !vp_current_user_can( [ 'super_admin', 'administrator' ] ) ) {
            return redirect()->back()->with( [
                'class' => 'success',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        try {
            Artisan::call( 'vp:install', [
                '--n' => true,
                '--s' => true,
            ] );

            //#! Trigger the post-install actions
            Artisan::call( 'vp:post-install', [
                //#! Delete the uploads directory
                '--d' => true,
            ] );
        }
        catch ( \Exception $e ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.An error occurred while trying to reset the application: :error', [ 'error' => $e->getMessage() ] ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.The application has been reset and the default data imported.' ),
        ] );
    }

    public function __cmdClearAppCache()
    {
        if ( !vp_current_user_can( [ 'super_admin', 'administrator' ] ) ) {
            return redirect()->back()->with( [
                'class' => 'success',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        try {
            Artisan::call( 'vp:cache' );
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

    public function __cmdComposerUpdate()
    {
        if ( !vp_current_user_can( [ 'super_admin', 'administrator' ] ) ) {
            return redirect()->back()->with( [
                'class' => 'success',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        try {
            Artisan::call( 'vp:composer', [
                '--u' => true,
                '--d' => true,
            ] );
        }
        catch ( \Exception $e ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.An error occurred while executing "composer update": :error', [ 'error' => $e->getMessage() ] ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.Command executed successfully.' ),
        ] );
    }

    public function __cmdComposerDumpAutoload()
    {
        if ( !vp_current_user_can( [ 'super_admin', 'administrator' ] ) ) {
            return redirect()->back()->with( [
                'class' => 'success',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        try {
            Artisan::call( 'vp:composer', [
                '--u' => false,
                '--d' => true,
            ] );
        }
        catch ( \Exception $e ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.An error occurred while executing "composer dumpautoload": :error', [ 'error' => $e->getMessage() ] ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.Command executed successfully.' ),
        ] );
    }

    /**
     * Helper method used to update or reinstall the core files
     * @param string $version
     * @throws \Exception
     * @internal
     */
    private function __updateCore( string $version )
    {
        //#! Get archive from api server
        $response = Http::get( path_combine( VALPRESS_API_URL, 'get_update/core', $version ) );

        if ( empty( $response ) ) {
            throw new \Exception( __( 'a.There was no response from the api server.' ) );
        }
        elseif ( $response instanceof Response && !$response->successful() ) {
            throw new \Exception( __( 'a.The specified ValPress version was not found.' ) );
        }

        //#! Download content locally
        try {
            $saveDirPath = public_path( 'uploads/tmp' );
            if ( !File::isDirectory( $saveDirPath ) ) {
                File::makeDirectory( $saveDirPath, 775, true );
            }
            $fileSavePath = path_combine( $saveDirPath, 'valpress.zip' );
            if ( !File::put( $fileSavePath, $response ) ) {
                throw new \Exception( __( 'a.An error occurred when trying to create the local download file. Check for permissions.' ) );
            }
        }
        catch ( \Exception $e ) {
            throw new \Exception( $e->getMessage() );
        }

        //#! Extract to root
        $zip = new \ZipArchive();
        if ( $zip->open( $fileSavePath ) !== false ) {
            $zip->extractTo( base_path() );
            $zip->close();
        }
        else {
            throw new \Exception( __( 'a.An error occurred when trying to extract the downloaded archive. Check for permissions.' ) );
        }

        //#! Delete temp file
        File::delete( $fileSavePath );

        //#! Update option
        $updatesInfo = $this->options->getOption( 'valpress_updates', [ 'last_check' => null, 'plugins' => [], 'themes' => [], 'core' => [] ] );
        $updatesInfo[ 'core' ] = [];
        $this->options->addOption( 'valpress_updates', $updatesInfo );

        //#! Trigger the post-install actions
        Artisan::call( 'vp:post-install', [
            //#! Do not delete the uploads directory
            '--d' => false,
        ] );
    }
}
