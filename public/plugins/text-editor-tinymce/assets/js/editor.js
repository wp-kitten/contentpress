var pageLocale = ( typeof ( window.PostsLocale ) !== 'undefined' ? window.PostsLocale : false );
if ( !pageLocale ) {
    throw new Error( 'PostsLocale locale not loaded.' );
}
var locale = ( typeof ( window.AppLocale ) !== 'undefined' ? window.AppLocale : false );
if ( !locale ) {
    throw new Error( 'AppLocale locale not loaded.' );
}

/*#!
 * Global object
 * Themes and plugins MUST override the getContent method in order to inject their own content
 */
window.AppTextEditor = {
    getContent(contentBuilder) {
        return tinymce.activeEditor.getContent();
    }
};

/*Tinymce editor*/
jQuery( function ($) {
    "use strict";
    if ( $( "#tinymce_text_editor" ).length ) {
        tinymce.init( {
            selector: '#tinymce_text_editor',
            height: 1000,
            theme: 'silver',
            plugins: [
                'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                'searchreplace wordcount visualblocks visualchars code fullscreen',
                'insertdatetime media nonbreaking save table directionality',
                'emoticons template paste textpattern imagetools codesample toc help'
            ],
            toolbar1: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            toolbar2: 'print preview media | forecolor backcolor emoticons | codesample help',
            image_advtab: true,
            //#! Urls to the stylesheets to customize the texteditor in the admin area
            content_css: pageLocale.editor_styles,
            images_upload_handler: function (blobInfo, success, failure) {
                var ajaxData = new FormData();
                ajaxData.append( 'action', 'tinymce_save_editor_image' );
                ajaxData.append( 'post_id', pageLocale.post_id );
                ajaxData.append( 'editor_image', blobInfo.blob() );
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
                                success( r.data.image_url );
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
                        failure( e );
                    } );
            },
        } );
    }

} );
