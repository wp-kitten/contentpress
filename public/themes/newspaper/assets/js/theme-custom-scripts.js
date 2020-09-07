jQuery( function ($) {
    "use strict";

    $( '.btn-toggle-nav' ).on( 'click', function (ev) {
        ev.preventDefault();
        $( '.topnav' ).toggleClass( 'responsive' );
    } );
} );
