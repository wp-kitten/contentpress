var pageLocale = ( typeof ( window.MediaLocale ) !== 'undefined' ? window.MediaLocale : false );
if ( !pageLocale ) {
    throw new Error( 'MediaLocale locale not loaded.' );
}

jQuery( function ($) {
    "use strict";

    var locale = window.AppLocale;

    let __dropifySetup = false;
    const dropifyImageUploader = new DropifyImageUploader(
        /* the selector for the file upload field */
        $( '#media_image_upload_field' )
    );

    const previewWrapper = $('.js-image-preview-uploads');
    const __dropBgItem = $('.backdrop-preview');

    if( ! __dropifySetup){
        dropifyImageUploader.setup({
            on_add_image: function ($this) {
                var value = $this.element[0].files[0];
                if ( value.length < 1 ) {
                    return false;
                }

                var ajaxData = new FormData();
                ajaxData.append( 'action', 'media_upload_image' );
                ajaxData.append( 'media_image', value );
                ajaxData.append( locale.nonce_name, locale.nonce_value );

                __dropBgItem.addClass('visible');
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
                                    showToast( pageLocale.text_image_set, 'success' );

                                    //#! Clear the preview of the uploaded image
                                    let dropify = $( '#media_image_upload_field' ).dropify();
                                    dropify = dropify.data( 'dropify' );
                                    dropify.resetPreview();

                                    previewWrapper.append('<div class="col-md-2"><div class="thumbnail"><img src="'+r.data.url+'" data-id="'+r.data.id+'" alt=""/></div></div>').addClass('visible');
                                    __dropBgItem.removeClass('visible');
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
                        __dropBgItem.removeClass('visible');
                    } )
            },
            on_remove_image: function ($this) {
                $.ajax( {
                    url: locale.ajax.url,
                    method: 'POST',
                    async: true,
                    timeout: 29000,
                    data: {
                        action: 'media_delete_image',
                        path: $this.ajaxResponse.path,
                        [locale.nonce_name]: locale.nonce_value
                    }
                } )
                    .done( function (r) {
                        if ( r ) {
                            if ( r.success ) {
                                showToast( pageLocale.text_image_removed, 'success' );
                            }
                            else {
                                showToast( locale.ajax.empty_response, 'warning' );
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
