jQuery( function ($) {
    "use strict";

    /*
     * Check each column for widgets and if it doesn't contain any, remove the column
     */
    $( '.js-dash-widgets-col' ).each( function (i, el) {
        const widgets = $( el ).find( '.widget' );
        if ( !widgets || widgets.length < 1 ) {
            $( el ).parent().remove();
        }
    } )
} );
