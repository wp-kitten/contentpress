const appLocale = ( typeof ( window.AppLocale ) !== 'undefined' ? window.AppLocale : false );
if ( !appLocale ) {
    throw new Error( 'PluginsLocale locale not loaded.' );
}
const pageLocale = ( typeof ( window.PluginsLocale ) !== 'undefined' ? window.PluginsLocale : false );
if ( !pageLocale ) {
    throw new Error( 'PluginsLocale locale not loaded.' );
}

jQuery( function ($) {
    "use strict";

    //#! Dropify -- upload plugin
    let __dropifySetup = false;
    const dropifyImageUploader = new DropifyImageUploader(
        /* the selector for the file upload field */
        $( '#plugin_upload_field' )
    );
    if( ! __dropifySetup){
        dropifyImageUploader.setup({
            on_add_image: function ($this) {
                var value = $this.element[0].files[0];
                if ( value.length < 1 ) {
                    return false;
                }

                var ajaxData = new FormData();
                ajaxData.append( 'action', 'upload_plugin' );
                ajaxData.append( 'the_file', value );
                ajaxData.append( appLocale.nonce_name, appLocale.nonce_value );

                $.ajax( {
                    url: appLocale.ajax.url,
                    method: 'POST',
                    async: true,
                    timeout: 29000,
                    data: ajaxData,
                    processData: false,
                    contentType: false
                } )
                    .done( function (r) {
                        if ( r ) {
                            if ( r.success ) {
                                if ( r.data ) {
                                    $this.ajaxResponse = r.data;
                                    showToast( pageLocale.text_plugin_uploaded, 'success' );
                                }
                                else {
                                    showToast( appLocale.ajax.empty_response, 'warning' );
                                }
                            }
                            else {
                                if ( r.data ) {
                                    showToast( r.data, 'warning' );
                                }
                                else {
                                    showToast( appLocale.ajax.empty_response, 'warning' );
                                }
                            }
                        }
                        else {
                            showToast( appLocale.ajax.no_response, 'error' );
                        }
                    } )
                    .fail( function (x, s, e) {
                        showToast( e, 'error' );
                    } )
            },
            on_remove_image: function ($this) {
                $.ajax( {
                    url: appLocale.ajax.url,
                    method: 'POST',
                    async: true,
                    timeout: 29000,
                    data: {
                        action: 'delete_plugin',
                        path: $this.ajaxResponse.path,
                        [appLocale.nonce_name]: appLocale.nonce_value
                    }
                } )
                    .done( function (r) {
                        if ( r ) {
                            if ( r.success ) {
                                showToast( pageLocale.text_plugin_deleted, 'success' );
                            }
                            else {
                                if ( r.data ) {
                                    showToast( r.data, 'warning' );
                                }
                                else {
                                    showToast( appLocale.ajax.empty_response, 'warning' );
                                }
                            }
                        }
                        else {
                            showToast( appLocale.ajax.no_response, 'error' );
                        }
                    } )
                    .fail( function (x, s, e) {
                        showToast( e, 'error' );
                    } );
            },
        });
        __dropifySetup = true;
    }

    //#! Search plugins
    const searchField = $('#plugin-search-field');
    $('#plugin-search-button').on('click', function(){

    });
} );
