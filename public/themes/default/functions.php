<?php
define( 'DEFAULT_THEME_DIR_PATH', untrailingslashit( wp_normalize_path( dirname( __FILE__ ) ) ) );
define( 'DEFAULT_THEME_DIR_NAME', basename( dirname( __FILE__ ) ) );

require_once( DEFAULT_THEME_DIR_PATH . '/src/ThemeHelper.php' );
require_once( DEFAULT_THEME_DIR_PATH . '/controllers/DefaultThemeController.php' );
require_once( DEFAULT_THEME_DIR_PATH . '/theme-hooks.php' );
