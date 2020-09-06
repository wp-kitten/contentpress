<?php

namespace App\Helpers;

use App\Options;
use Illuminate\Support\Facades\Http;

/**
 * Class ContentPressCheckForUpdates
 * @package App\Helpers
 *
 * Helper class used internally to check for available updates for the application, themes and plugins
 */
class ContentPressCheckForUpdates
{
    /**
     * @var Options|null
     */
    private $options = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->options = new Options();
    }

    /**
     * Check for updates.
     */
    public function run()
    {
        //#! Check and store info in option: contentpress_updates
        $updatesInfo = $this->options->getOption( 'contentpress_updates', [ 'last_check' => null, 'plugins' => [], 'themes' => [], 'core' => [] ] );
        if ( !is_array( $updatesInfo ) ) {
            $updatesInfo = [ 'plugins' => [], 'themes' => [], 'core' => [] ];
        }

        //#! Check date first
        if ( !isset( $updatesInfo[ 'last_check' ] ) ) {
            $updatesInfo[ 'last_check' ] = null;
        }
        if ( $updatesInfo[ 'last_check' ] ) {
            $expires = ( intval( $updatesInfo[ 'last_check' ] ) ) + intval( apply_filters( 'contentpress/admin/update-interval', CP_DAY_IN_SECONDS ) );
            //#! Not yet expired
            if ( $expires > time() ) {
                return;
            }
        }

        //#! Reset state
        $updatesInfo[ 'plugins' ] = [];
        $updatesInfo[ 'themes' ] = [];
        $updatesInfo[ 'core' ] = [];

        $errors = [];

        //#! Check core for update
        $coreUpdateInfo = $this->__checkCoreForUpdate();
        if ( !empty( $coreUpdateInfo ) ) {
            $updatesInfo[ 'core' ] = $coreUpdateInfo;
        }

        //#! Scan plugins and check for updates
        $pluginsManager = PluginsManager::getInstance();

        $plugins = $pluginsManager->getAllPlugins();

        foreach ( $plugins as $pluginFileName => $pluginInfo ) {
            if ( !isset( $pluginInfo->update_url ) ) {
                continue;
            }
            $result = $this->__checkPluginForUpdate( $pluginFileName, $pluginInfo->update_url );
            if ( !$result ) {
                continue;
            }
            if ( $result[ 'success' ] ) {
                $updatesInfo[ 'plugins' ][ $pluginFileName ] = [
                    'display_name' => $pluginInfo->display_name,
                    'version' => $result[ 'data' ][ 'version' ],
                    'url' => $result[ 'data' ][ 'url' ],
                ];
            }
            else {
                $errors[ $pluginInfo->display_name ] = $result[ 'errors' ];
            }
        }

        //#! Scan themes and check for updates
        $themes = ThemesManager::getInstance()->getInstalledThemes();
        foreach ( $themes as $themeDirName ) {
            $theme = new Theme( $themeDirName );
            $info = $theme->getThemeData();
            if ( !isset( $info[ 'name' ] ) || !isset( $info[ 'update_url' ] ) || !isset( $info[ 'display_name' ] ) ) {
                continue;
            }

            $name = $info[ 'name' ];
            $url = $info[ 'update_url' ];
            $displayName = $info[ 'display_name' ];

            $result = $this->__checkThemeForUpdate( $name, $url );
            if ( !$result ) {
                continue;
            }
            if ( $result[ 'success' ] ) {
                $updatesInfo[ 'themes' ][ $name ] = [
                    'display_name' => $displayName,
                    'version' => $result[ 'data' ][ 'version' ],
                    'url' => $result[ 'data' ][ 'url' ],
                ];
            }
            else {
                $errors[ $name ] = $result[ 'errors' ];
            }
        }

        //#! Render error
        if ( !empty( $errors ) ) {
            foreach ( $errors as $source => $msgs ) {
                UserNotices::getInstance()->addNotice( 'warning', implode( '<br/>', $msgs ) );
            }
        }

        $updatesInfo[ 'last_check' ] = time();
        $this->options->addOption( 'contentpress_updates', $updatesInfo );
    }

    /**
     * Check to see whether the specified plugin has any update available.
     * @param string $name The directory name of the plugin
     * @param string $url The URL to check for plugin updates
     *
     * @return bool|array Boolean false on error, array on success
     * @uses filter 'contentpress/plugin/check-for-update/args'
     */
    private function __checkPluginForUpdate( string $name, string $url )
    {
        if ( empty( $name ) || empty( $url ) ) {
            return false;
        }

        //#! Allows developers to inject other required fields such as authentication
        //#! Expected return: associative array ( ex: key => value )
        $args = \apply_filters( 'contentpress/plugin/check-for-update/args', $name );
        if ( !is_array( $args ) ) {
            $args = [
                'name' => $name,
            ];
        }

        $response = Http::asForm()->post( $url, $args )->json();
        if ( isset( $response[ 'code' ] ) && $response[ 'code' ] == 200 ) {
            //#! Compare versions
            $pluginInfo = PluginsManager::getInstance()->getPluginInfo( $name );
            if ( version_compare( $pluginInfo->version, $response[ 'data' ][ 'version' ], '<' ) ) {
                return [
                    'success' => true,
                    'data' => $response[ 'data' ],
                ];
            }
            return false;
        }
        return [
            'success' => false,
            'code' => ( isset( $response[ 'code' ] ) ? $response[ 'code' ] : 404 ),
            'errors' => ( isset( $response[ 'errors' ] ) ? $response[ 'errors' ] : [] ),
        ];
    }

    /**
     * Check to see whether the specified theme has any update available.
     *
     * @param string $name The directory name of the theme
     * @param string $url The URL to check for theme updates
     *
     * @return bool|array Boolean false on error, array on success
     * @uses filter 'contentpress/theme/check-for-update/args'
     */
    private function __checkThemeForUpdate( string $name, string $url )
    {
        if ( empty( $name ) || empty( $url ) ) {
            return false;
        }

        //#! Allows developers to inject other required fields such as authentication
        //#! Expected return: associative array ( ex: key => value )
        $args = \apply_filters( 'contentpress/theme/check-for-update/args', $name );
        if ( !is_array( $args ) ) {
            $args = [
                'name' => $name,
            ];
        }

        //@see: https://laravel.com/docs/7.x/http-client
        $response = Http::asForm()->post( $url, $args )->json();
        if ( isset( $response[ 'code' ] ) && $response[ 'code' ] == 200 ) {
            $theme = new Theme( $name );
            $themeInfo = $theme->getThemeData();
            if ( version_compare( $themeInfo[ 'version' ], $response[ 'data' ][ 'version' ], '<' ) ) {
                return [
                    'success' => true,
                    'data' => $response[ 'data' ],
                ];
            }
        }
        return [
            'success' => false,
            'code' => ( isset( $response[ 'code' ] ) ? $response[ 'code' ] : 404 ),
            'errors' => ( isset( $response[ 'errors' ] ) ? $response[ 'errors' ] : [] ),
        ];
    }

    /**
     * Internal method to check for core updates
     *
     * @return bool|array Boolean false on error, array on success
     */
    private function __checkCoreForUpdate()
    {
        $url = path_combine( CONTENTPRESS_API_URL, 'updates' );
        $response = Http::get( $url )->json();

        if ( empty( $response ) ) {
            return false;
        }
        if ( isset( $response[ 'data' ][ 'core' ] ) ) {
            if ( version_compare( $response[ 'data' ][ 'core' ], CONTENTPRESS_VERSION, '>' ) ) {
                return [
                    'display_name' => esc_html( __( 'a.ContentPress' ) ),
                    'version' => $response[ 'data' ][ 'core' ],
                ];
            }
        }
        return false;
    }
}
