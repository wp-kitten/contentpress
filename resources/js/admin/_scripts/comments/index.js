var pageLocale = ( typeof ( window.CommentsLocale ) !== 'undefined' ? window.CommentsLocale : false );
if ( !pageLocale ) {
    throw new Error( 'CommentsLocale locale not loaded.' );
}

jQuery( function ($) {
    "use strict";

    //#! Clear filters
    $( '.js-btn-form-filters-clear' ).on( 'click', function (e) {
        const url = $( this ).attr( 'data-url' );
        if ( typeof ( url ) !== 'undefined' ) {
            window.location.href = url;
        }
        return false;
    } );

    //#! Quill
    ValPressTextEditor.register( 'comment_reply', new Quill( '#comment_content-editor', {
        modules: {
            toolbar: [
                [{ header: [false] }],
                ['bold', 'italic', 'underline'],
            ]
        },
        scrollingContainer: '.quill-scrolling-container',
        placeholder: pageLocale.comment_placeholder,
        theme: 'bubble'
    } ) );

    //#! On save button click, get the values from editor
    $( '#js-comment-submit-button' ).on( 'click', function (ev) {
        $( '#comment-content-field' ).val( ValPressTextEditor.getHTML( 'comment_reply' ) );
    } );
} );


