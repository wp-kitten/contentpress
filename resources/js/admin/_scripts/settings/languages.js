jQuery( function ($) {
    "use strict";

    $( '.js-link-change-lang' ).on( 'click', function (ev) {
        const self = $( this );
        const langSelect = $( '#backend_user_current_language' );
        let url = self.attr( 'href' );
        self.attr( 'href', url + '/' + langSelect.val() );
        return true;
    } );
} );
