<?php

namespace App\Helpers;

class ScriptsManager
{
    private static $_localizedScripts = [];

    private static $scripts = [
        'head' => [],
        'footer' => [],
    ];
    private static $styles = [];

    /**
     * Enqueue script to head
     * @param string $id
     * @param string $path
     */
    public static function enqueueHeadScript( $id, $path )
    {
        self::$scripts[ 'head' ][ $id ] = $path;
    }

    /**
     * Enqueue script to footer
     * @param string $id
     * @param string $path
     */
    public static function enqueueFooterScript( $id, $path )
    {
        self::$scripts[ 'footer' ][ $id ] = $path;
    }

    /**
     * Enqueue stylesheet to head
     * @param string $id
     * @param string $path
     */
    public static function enqueueStylesheet( $id, $path )
    {
        self::$styles[ $id ] = $path;
    }

    /**
     * Localize script
     * @param string $id
     * @param string $objectName
     * @param array $data
     */
    public static function localizeScript( $id, $objectName, $data = [] )
    {
        self::$_localizedScripts[ $id ] = [
            'locale' => $objectName,
            'data' => $data,
        ];
    }

    /**
     * Print the localized scripts to head
     */
    public static function printLocalizedScripts()
    {
        if ( !empty( self::$_localizedScripts ) ) {
            foreach ( self::$_localizedScripts as $id => $info ) {
                if ( empty( $info ) || empty( $info[ 'locale' ] ) ) {
                    continue;
                }
                ?>
                <script id="<?php esc_attr_e( $id ); ?>-locale">
                    var <?php echo esc_js( $info[ 'locale' ] ); ?> = <?php echo json_encode( $info[ 'data' ] ); ?>;
                </script>
                <?php
            }
        }
    }

    /**
     * Print enqueued stylesheets
     */
    public static function printStylesheets()
    {
        foreach ( self::$styles as $id => $path ) {
            echo '<link id="' . esc_attr( $id ) . '" href="' . esc_attr( $path ) . '" rel="stylesheet" type="text/css"/>';
        }
    }

    /**
     * Print enqueued scripts
     */
    public static function printHeadScripts()
    {
        foreach ( self::$scripts[ 'head' ] as $id => $path ) {
            echo '<script id="' . esc_attr( $id ) . '" src="' . esc_attr( $path ) . '"></script>';
        }
    }

    /**
     * Print enqueued scripts to footer
     */
    public static function printFooterScripts()
    {
        foreach ( self::$scripts[ 'footer' ] as $id => $path ) {
            echo '<script id="' . esc_attr( $id ) . '" src="' . esc_attr( $path ) . '"></script>';
        }
    }
}
