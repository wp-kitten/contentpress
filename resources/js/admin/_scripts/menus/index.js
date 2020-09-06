const locale = ( typeof ( window.AppLocale ) !== 'undefined' ? window.AppLocale : false );
if ( !locale ) {
    throw new Error( 'AppLocale locale not loaded.' );
}
var pageLocale = ( typeof ( window.MenuLocale ) !== 'undefined' ? window.MenuLocale : false );
if ( !pageLocale ) {
    throw new Error( 'MenuLocale locale not loaded.' );
}

//#! Delete links
jQuery( function ($) {
    "use strict";
    $( '.js-menu-link-delete' ).on( 'click', function (ev) {
        ev.preventDefault();
        const formID = $( this ).attr( 'data-form-id' );
        if ( typeof ( formID ) !== 'undefined' ) {
            if ( confirm( pageLocale.confirm_delete ) ) {
                $( '#' + formID ).submit();
            }
        }
    } );
} );
