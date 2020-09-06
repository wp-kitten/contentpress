var pageLocale = ( typeof ( window.MediaLocale ) !== 'undefined' ? window.MediaLocale : false );
if ( !pageLocale ) {
    throw new Error( 'MediaLocale locale not loaded.' );
}

jQuery( function ($) {
    "use strict";

    const locale = window.AppLocale;

    //#! Gallery
    $( ".contentpress-media-list" ).lightGallery( { selector: '.js-contentpress-thumbnail' } );

    //#! Clear filters
    $( '.js-btn-form-filters-clear' ).on( 'click', function (e) {
        const url = $( this ).attr( 'data-url' );
        if ( typeof ( url ) !== 'undefined' ) {
            window.location.href = url;
        }
        return false;
    } );
} );
