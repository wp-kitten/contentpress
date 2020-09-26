<?php
/*
 * Loaded automatically by the system before the theme is deleted
 */

//#! Delete theme options

use App\Http\Controllers\NewspaperAdminController;
use App\Models\Options;

require_once( dirname( __FILE__ ) . '/controllers/NewspaperAdminController.php' );

$options = new Options();
$option = $options->where( 'name', NewspaperAdminController::THEME_OPTIONS_OPT_NAME )->first();
if ( $option ) {
    $option->destroy();
}
