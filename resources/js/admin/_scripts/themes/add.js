var pageLocale = ( typeof ( window.ThemesLocale ) !== 'undefined' ? window.ThemesLocale : false );
if ( !pageLocale ) {
    throw new Error( 'ThemesLocale locale not loaded.' );
}

jQuery( function ($) {
    "use strict";

    var locale = window.AppLocale;

    let __dropifySetup = false;
    const dropifyImageUploader = new DropifyImageUploader(
        /* the selector for the file upload field */
        $( '#theme_upload_field' )
    );
    if( ! __dropifySetup){
        dropifyImageUploader.setup({
            on_add_image: function ($this) {
                var value = $this.element[0].files[0];
                if ( value.length < 1 ) {
                    return false;
                }

                var ajaxData = new FormData();
                ajaxData.append( 'action', 'upload_theme' );
                ajaxData.append( 'the_file', value );
                ajaxData.append( locale.nonce_name, locale.nonce_value );

                $.ajax( {
                    url: locale.ajax.url,
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
                                    showToast( pageLocale.text_theme_uploaded, 'success' );
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
            },
            on_remove_image: function ($this) {
                $.ajax( {
                    url: locale.ajax.url,
                    method: 'POST',
                    async: true,
                    timeout: 29000,
                    data: {
                        action: 'delete_theme',
                        path: $this.ajaxResponse.path,
                        [locale.nonce_name]: locale.nonce_value
                    }
                } )
                    .done( function (r) {
                        if ( r ) {
                            if ( r.success ) {
                                showToast( pageLocale.text_theme_deleted, 'success' );
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
                    } );
            },
        });
        __dropifySetup = true;
    }
} );
