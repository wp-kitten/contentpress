jQuery( function ($) {
    "use strict";

    //#! Filter search results
    $( '#js-sort-results' ).on( 'change', function () {
        var formID = $( this ).attr( 'data-form-id' );
        if ( formID ) {
            $( '#' + formID ).trigger( 'submit' );
        }
    } );

} );
