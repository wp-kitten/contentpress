var pageLocale = ( typeof ( window.ThemesLocale ) !== 'undefined' ? window.ThemesLocale : false );
if ( !pageLocale ) {
    throw new Error( 'ThemesLocale locale not loaded.' );
}

jQuery( function ($) {
    "use strict";

    const locale = window.AppLocale;

    //#! Confirm theme delete
    $( '.js-theme-delete' ).on( 'click', function (ev) {
        return confirm( pageLocale.text_confirm_delete );
    } );

    //#! Local cache to save ajax requests for the same theme
    let currentThemeName = '';

    //#! Open info modal
    $( '#infoModal' ).on( 'show.bs.modal', function (ev) {
        //#! The element triggering the modal to open
        const sender = $( ev.relatedTarget );
        const themeDirName = sender.data( 'name' );
        const themeDisplayName = sender.data( 'displayName' );
        const $modal = $( this );
        const $modalTitle = $modal.find( '.modal-title' );
        const $modalBody = $modal.find( '.modal-body' );
        const $modalContent = $modalBody.find( '.js-content' );
        const $modalLoader = $modalBody.find( '.js-ajax-loader' );

        if ( typeof ( themeDirName ) === 'undefined' ) {
            $modalLoader.addClass( 'hidden' );
            $modalTitle.text( pageLocale.text_error );
            $modalContent.html( pageLocale.text_error_theme_name_not_found ).removeClass( 'hidden' );
        }
        else {

            if ( currentThemeName === themeDirName ) {
                $modalLoader.addClass( 'hidden' );
                $modalContent.removeClass( 'hidden' );
            }
            else {
                $modalLoader.removeClass( 'hidden' );

                $.ajax( {
                    url: locale.ajax.url,
                    method: 'POST',
                    async: true,
                    timeout: 29000,
                    data: {
                        action: 'get_theme_info',
                        theme_name: themeDirName,
                        [locale.nonce_name]: locale.nonce_value
                    }
                } )
                    .done( function (r) {
                        if ( r ) {
                            if ( r.success ) {
                                if ( r.data && r.data.length > 0) {
                                    currentThemeName = themeDirName;
                                    $modalTitle.text( themeDisplayName );
                                    $modalContent.html( r.data ).removeClass( 'hidden' );
                                }
                                else {
                                    showToast( locale.ajax.empty_response, 'warning' );
                                }
                            }
                            else {
                                if ( r.data ) {
                                    showToast( r.data, 'warning' );
                                }
                                else {
                                    showToast( locale.ajax.empty_response, 'warning' );
                                }
                            }
                        }
                        else {
                            showToast( locale.ajax.no_response, 'error' );
                        }
                    } )
                    .fail( function (x, s, e) {
                        showToast( e, 'error' );
                    } )
                    .always( function () {
                        $modalLoader.addClass( 'hidden' );
                    } );
            }
        }
    } );
} );
