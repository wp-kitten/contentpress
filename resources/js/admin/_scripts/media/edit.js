var pageLocale = ( typeof ( window.MediaLocale ) !== 'undefined' ? window.MediaLocale : false );
if ( !pageLocale ) {
    throw new Error( 'MediaLocale locale not loaded.' );
}

jQuery( function ($) {
    "use strict";

    //#! Delete media
    $( '.js-btn-delete' ).on( 'click', function (ev) {
        return confirm( pageLocale.text_confirm_delete );
    } );

    //#! OnClick Copy URl
    $( '#media-url-field' ).on( 'click', function (ev) {
        /* Select the text field */
        const self = $( this );
        self.focus();
        self.select();

        try {
            document.execCommand( "copy" );
            showToast( pageLocale.text_copied, 'success' );
        }
        catch ( err ) {
            console.error( err );
        }

    } );
} );
