var pageLocale = ( typeof ( window.PluginsLocale ) !== 'undefined' ? window.PluginsLocale : false );
if ( !pageLocale ) {
    throw new Error( 'PluginsLocale locale not loaded.' );
}

jQuery( function ($) {
    "use strict";

    const locale = window.AppLocale;

    //#! Local cache to save ajax requests for the same plugin
    let currentPluginName = '';

    //#! Open info modal
    $( '#infoModal' ).on( 'show.bs.modal', function (ev) {
        //#! The element triggering the modal to open
        const sender = $( ev.relatedTarget );
        const pluginDirName = sender.data( 'name' );
        const pluginDisplayName = sender.data( 'displayName' );
        const $modal = $( this );
        const $modalTitle = $modal.find( '.modal-title' );
        const $modalBody = $modal.find( '.modal-body' );
        const $modalContent = $modalBody.find( '.js-content' );
        const $modalLoader = $modalBody.find( '.js-ajax-loader' );

        if ( typeof ( pluginDirName ) === 'undefined' ) {
            $modalLoader.addClass( 'hidden' );
            $modalTitle.text( pageLocale.text_error );
            $modalContent.html( pageLocale.text_error_plugin_name_not_found ).removeClass( 'hidden' );
        }
        else {

            if ( currentPluginName === pluginDirName ) {
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
                        action: 'get_plugin_info',
                        plugin_name: pluginDirName,
                        [locale.nonce_name]: locale.nonce_value
                    }
                } )
                    .done( function (r) {
                        if ( r ) {
                            if ( r.success ) {
                                if ( r.data && r.data.length > 0 ) {
                                    currentPluginName = pluginDirName;
                                    $modalTitle.text( pluginDisplayName );
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

    //#! Deactivate plugins
    $( '#js-btn-deactivate' ).on( 'click', function (ev) {
        const self = $( this );
        const theFormID = self.attr( 'data-form-id' );
        const action = self.attr( 'data-action' );
        if ( typeof ( theFormID ) !== 'undefined' && typeof ( action ) !== 'undefined' ) {
            const theForm = $( '#' + theFormID );
            theForm
                .attr( 'action', action )
                .trigger( 'submit' );
        }
        return false;
    } );
} );
