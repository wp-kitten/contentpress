const locale = ( typeof ( window.AppLocale ) !== 'undefined' ? window.AppLocale : false );
if ( !locale ) {
    throw new Error( 'AppLocale locale not loaded.' );
}
const pageLocale = ( typeof ( window.PostsLocale ) !== 'undefined' ? window.PostsLocale : false );
if ( !pageLocale ) {
    throw new Error( 'PostsLocale locale not loaded.' );
}

//<editor-fold desc="post-details">
if ( pageLocale.isMultilanguage ) {
    jQuery( function ($) {
        "use strict";
        var expandLinks = $( '.js-expand-post' );
        if ( typeof ( expandLinks ) !== 'undefined' ) {
            expandLinks.on( 'click', function (e) {
                e.preventDefault();
                var link = $( this ),
                    rowID = link.attr( 'data-target' ),
                    row = $( '#' + rowID );
                if ( typeof ( row ) !== 'undefined' ) {
                    if ( row.hasClass( 'hidden' ) ) {
                        row.removeClass( 'hidden' );
                        link.text( pageLocale.text_collapse );
                    }
                    else {
                        row.addClass( 'hidden' );
                        link.text( pageLocale.text_expand );
                    }
                }
            } );
        }
    } )
}
//</editor-fold desc="post-details">

//<editor-fold desc=":: POST TITLES -- INLINE EDIT ::">
jQuery( function ($) {

    /**
     * Helper object that allows post titles to be edited inline
     * @type {{edit(*): void, enable(*=): void, save(*): void, revert(*): void}}
     */
    const InlineEditor = {
        enable(elements) {
            const self = this;
            if ( elements && elements.length >= 1 ) {
                $.each( elements, function (i, el) {
                    const element = $( el );
                    element
                        .on( 'click', function (ev) {
                            ev.preventDefault();
                            ev.stopPropagation();
                            self.edit( element );
                        } )
                        .on( 'blur', function (ev) {
                            ev.preventDefault();
                            ev.stopPropagation();
                            self.save( element );
                        } )
                        //#! Prevents the ENTER key to produce any change
                        .on( 'keydown', function (ev) {
                            if ( ev.keyCode === 13 ) {
                                ev.preventDefault();
                                return false;
                            }
                        } );
                } );
            }
        },

        edit($element) {
            $element.addClass( 'is-editing' );
            $element.attr( 'data-initial-value', $element.html().trim() );
        },

        save($element) {
            if ( !this.hasChanged( $element ) ) {
                $element.removeClass( 'is-editing' );
                return false;
            }
            const self = this;
            $.ajax( {
                url: locale.ajax.url,
                method: 'POST',
                async: true,
                timeout: 29000,
                cache: false,
                data: {
                    action: 'update_post_title',
                    post_id: $element.attr( 'data-id' ),
                    post_title: $element.html().trim(),
                    post_type: $element.attr( 'data-post-type' ),
                    [locale.nonce_name]: locale.nonce_value,
                }
            } )
                .done( function (r) {
                    if ( r ) {
                        if ( r.success ) {
                            if ( r.data ) {
                                showToast( r.data.message, 'success' );
                                $element.removeClass( 'is-editing' );
                            }
                            else {
                                showToast( AppLocale.ajax.empty_response, 'warning' );
                                self.revert( $element );
                            }
                        }
                        else {
                            if ( r.data ) {
                                showToast( r.data, 'warning' );
                                self.revert( $element );
                            }
                            else {
                                showToast( AppLocale.ajax.empty_response, 'warning' );
                                self.revert( $element );
                            }
                        }
                    }
                    else {
                        showToast( AppLocale.ajax.no_response, 'warning' );
                        self.revert( $element );
                    }
                } )
                .fail( function (x, s, e) {
                    showToast( e, 'error' );
                    self.revert( $element );
                } )
                .always( function () {

                } );
        },

        revert($element) {
            $element.html( $element.attr( 'data-initial-value' ) );
            $element.removeClass( 'is-editing' );
        },

        hasChanged($element) {
            const initialContent = $element.attr( 'data-initial-value' );
            if ( typeof ( initialContent ) === 'undefined' ) {
                return true;
            }
            return ( initialContent !== $element.html().trim() );
        }
    };

    InlineEditor.enable( $( '.posts-list .post-title.js-editable' ) );
} );
//</editor-fold desc=":: POST TITLES -- INLINE EDIT ::">

//<editor-fold desc="post-actions-hover">
jQuery( function ($) {
    "use strict";

    $( '.js-post-title-cell' )
        .mouseenter( function () {
            $( '.post-actions', $( this ) ).removeClass( 'hidden' );
        } )
        .mouseleave( function () {
            $( '.post-actions', $( this ) ).addClass( 'hidden' );
        } );
} );
//</editor-fold desc="post-actions-hover">

//<editor-fold desc="clear-filters">
jQuery( '.js-btn-form-filters-clear' ).on( 'click', function (e) {
    const url = $( this ).attr( 'data-url' );
    if ( typeof ( url ) !== 'undefined' ) {
        window.location.href = url;
    }
    return false;
} );
//</editor-fold desc="clear-filters">



