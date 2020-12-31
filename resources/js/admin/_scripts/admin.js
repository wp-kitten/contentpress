/*#!
 * Global object
 * Themes and plugins MUST override the getContent method in order to inject their own content
 */
window.AppTextEditor = {
    getContent(contentBuilder) {
        return ( contentBuilder ? contentBuilder.html() : '' );
    }
};

//#! Admin sidebar toggle
jQuery( function ($) {
    "use strict";

    const LOCAL_STORAGE_KEY = 'valpress_admin';

    //#! onLoad
    const LocalStorage = {
        init() {
            this.ls = window.localStorage;
            this.data = this.load();
            return this;
        },
        load() {
            let data = this.ls.getItem( LOCAL_STORAGE_KEY );
            if ( data ) {
                return JSON.parse( data );
            }
            return {};
        },
        get(key) {
            return ( typeof ( this.data[key] ) !== 'undefined' ? this.data[key] : null );
        },

        update(key, value) {
            this.data[key] = value;
            this.ls.setItem( LOCAL_STORAGE_KEY, JSON.stringify( this.data ) );
            return this;
        }
    };
    LocalStorage.init();

    let isHidden = false;
    const hidden = LocalStorage.get( 'hide_admin_sidebar' );
    if ( hidden ) {
        $( 'body' ).addClass( 'sidenav-toggled' );
        isHidden = true;
    }
    else {
        $( 'body' ).removeClass( 'sidenav-toggled' );
    }

    //#! On toggle
    $( '.app-sidebar__toggle' ).on( 'click', function (ev) {
        if ( isHidden ) {
            $( 'body' ).removeClass( 'sidenav-toggled' );
            isHidden = false;
        }
        else {
            $( 'body' ).addClass( 'sidenav-toggled' );
            isHidden = true;
        }
        LocalStorage.update( 'hide_admin_sidebar', isHidden );
        return true;
    } );
} );

//#! Delete links
jQuery( function ($) {
    "use strict";

    /**
     * Display the confirmation dialog and submits the form on success
     */
    $( '[data-confirm]' ).on( 'click', function (ev) {
        ev.stopPropagation();

        const element = $( this );
        const formID = element.attr( 'data-form-id' );
        const confirmText = element.attr( 'data-confirm' );
        if ( confirmText.length > 0 ) {
            if ( confirm( confirmText ) ) {
                const form = $( '#' + formID );
                if ( form && form.length > 0 ) {
                    form.trigger( 'submit' );
                }
                return true;
            }
        }
        ev.preventDefault();
        return false;
    } );
} );
