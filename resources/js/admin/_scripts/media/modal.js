var pageLocale = ( typeof ( window.MediaLocale ) !== 'undefined' ? window.MediaLocale : false );
if ( !pageLocale ) {
    throw new Error( 'MediaLocale locale not loaded.' );
}

jQuery( function ($) {
    "use strict";

    const locale = window.AppLocale;
    const $mediaModal = $( '#mediaModal' );

    //#! Referenced fields to update
    let $imagePreview = null;
    let $imageInput = null;

    let __set = false;

    //#! onClick listener for media files inside the modal
    const __setupImageOnClickListener = function ($context, sender) {
        //#! Update references
        $imagePreview = $( sender.data( 'imageTarget' ) );
        $imageInput = $( sender.data( 'inputTarget' ) );

        $( '.js-valpress-thumbnail', $context ).on( 'click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();

            const jsItem = $( this );
            const jsImage = $( 'img', jsItem );

            //#! Update preview & hidden field
            $imageInput.val( jsItem.data( 'id' ) );
            $imagePreview.attr( 'src', jsImage.attr( 'src' ) ).removeClass( 'hidden' );
        } );
    };

    //#! OnClick listener to delete the attached image
    $( '.js-preview-image-delete' ).on( 'click', function (ev) {
        ev.preventDefault();
        ev.stopPropagation();

        const jsItemParent = $( this ).parents( '.js-image-preview' );

        $( 'input[type="hidden"]', jsItemParent ).val( '' );
        $( 'img', jsItemParent ).addClass( 'hidden' ).attr( 'src', '' );
    } );

    //<editor-fold desc=":: IMAGE UPLOADER ::">
    //#! Render media file -- this content is populated after the file upload ajax request and displayed in the media files tab
    const imageTemplate = '<div class="item js--item" data-id="__FILE_ID__">\n' +
        '<a href="#" class="js-valpress-thumbnail thumbnail" data-id="__FILE_ID__">\n' +
        '     <img src="__FILE_URL__" alt="" class="valpress-thumbnail"/>\n' +
        '</a>\n' +
        '</div>';

    let __dropifySetup = false;
    const dropifyImageUploader = new DropifyImageUploader(
        /* the selector for the file upload field */
        $( '#dropify_image_field' )
    );
//</editor-fold desc=":: IMAGE UPLOADER ::">

    //#! Media Modal
    $mediaModal.on( 'show.bs.modal', function (ev) {
        //#! The element triggering the modal to open
        const sender = $( ev.relatedTarget );
        const $modal = $( this );
        const $modalBody = $modal.find( '.modal-body' );
        const $modalContent = $modalBody.find( '.js-content' );
        const $modalContentList = $modalContent.find( '.valpress-media-list' );

        $modal.find( '.modal-title' ).text( pageLocale.text_media );

        //#! Setup the on-click event listener for all existent media files inside the modal
        if ( !__set ) {
            __setupImageOnClickListener( $modal, sender );
            __set = true;
        }

        $modalContent.removeClass( 'hidden' );

        if ( !__dropifySetup ) {
            dropifyImageUploader.setup( {
                on_add_image: function ($this) {
                    var value = $this.element[0].files[0];
                    if ( value.length < 1 ) {
                        return false;
                    }

                    var ajaxData = new FormData();
                    ajaxData.append( 'action', 'media_upload_image' );
                    ajaxData.append( 'media_image', value );
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

                                        //!# Add the image to the list
                                        let __template = imageTemplate;
                                        __template = __template.replace( /__FILE_ID__/g, r.data.id ).replace( '__FILE_URL__', r.data.url );
                                        $modalContentList.prepend( __template );
                                        $modalContentList.find( '.js--info' ).addClass( 'hidden' );
                                        __setupImageOnClickListener( $modalContentList, sender );

                                        //#! Update preview and the hidden field
                                        $imageInput.val( r.data.id );
                                        $imagePreview.attr( 'src', r.data.url ).removeClass( 'hidden' );

                                        showToast( pageLocale.text_image_set, 'success' );
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
                            action: 'modal_delete_image',
                            id: $this.ajaxResponse.id,
                            [locale.nonce_name]: locale.nonce_value
                        }
                    } )
                        .done( function (r) {
                            if ( r ) {
                                if ( r.success ) {
                                    if ( $this.ajaxResponse.id ) {
                                        let $image = $( '.js--item[data-id="' + $this.ajaxResponse.id + '"]', $modalContentList );
                                        if ( $image ) {
                                            $image.remove();
                                        }

                                        //#! Update preview and the hidden field
                                        $imageInput.val( '' );
                                        $imagePreview.addClass( 'hidden' ).attr( 'src', '' );

                                        //#! Display the no content alert if there are no images
                                        const _images = $modalContentList.find( '.js--item' );
                                        if ( !_images || _images.length < 1 ) {
                                            $modalContentList.find( '.js--info' ).removeClass( 'hidden' );
                                        }
                                    }
                                    showToast( pageLocale.text_image_removed, 'success' );
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
                }
            } );
            __dropifySetup = true;
        }
    } )
        .on( 'hidden.bs.modal', function (ev) {
            //#! Clear the preview of the uploaded image
            let dropify = $( '#dropify_image_field' ).dropify();
            dropify = dropify.data( 'dropify' );
            dropify.resetPreview();
        } );
    //#! END Media Modal
} );

