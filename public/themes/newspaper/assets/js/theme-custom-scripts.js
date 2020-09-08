jQuery( function ($) {
    "use strict";

    $( '.btn-toggle-nav' ).on( 'click', function (ev) {
        ev.preventDefault();
        $( '.topnav' ).toggleClass( 'responsive' );
    } );

    $('.masonry-grid').masonry({
        // options
        itemSelector: '.masonry-item',
        columnWidth: '.grid-sizer',
        percentPosition: true,
    });
} );
