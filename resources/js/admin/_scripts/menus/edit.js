const locale = ( typeof ( window.AppLocale ) !== 'undefined' ? window.AppLocale : false );
if ( !locale ) {
    throw new Error( 'AppLocale locale not loaded.' );
}
const pageLocale = ( typeof ( window.MenuLocale ) !== 'undefined' ? window.MenuLocale : false );
if ( !pageLocale ) {
    throw new Error( 'MenuLocale locale not loaded.' );
}

//#! nestable v2
//#! https://github.com/RamonSmit/Nestable2
jQuery( function ($) {
    "use strict";

    const MenuBuilder = {
        __ACTION_ADD__: 'x0000',
        __ACTION_REMOVE__: 'x0001',
        // the element storing be nestable list (.dd)
        __nestable: null,
        __btnSaveMenu: null,
        __placeholder: null,
        __btnAddToMenu: null,
        __btnCustomAddToMenu: null,

        init() {
            this.__nestable = $( '.dd' );
            this.__btnSaveMenu = $( '.js-btn-save-menu' );
            this.__placeholder = $( '.menu-empty' );
            this.__btnAddToMenu = $( '.js-btn-add-to-menu' );
            this.__btnCustomAddToMenu = $( '.js-custom-add-to-menu-button' );

            this.__initNestable();
            this.updateSaveButtonState( this.__ACTION_ADD__, false );
            this.__setupListeners();
        },

        __initNestable() {
            const $this = this;

            this.__nestable.nestable( {
                maxDepth: 20,
                scroll: true,
                callback: function (l, e) {
                    // l is the main container
                    // e is the element that was moved

                    console.info( $( e ).attr( 'data-id' ) );

                    //#! Enable the Save Menu button
                    $this.updateSaveButtonState( $this.__ACTION_ADD__ );
                }
            } );
        },

        __hasMenuItems() {
            const c = this.__nestable.find( '.dd-item' );
            return ( c && c.length >= 1 );
        },

        __bindClickMenuItemRemove() {
            const $this = this;
            $( '.js-btn-remove', $this.__nestable ).on( 'click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                if ( confirm( pageLocale.confirm_delete_item ) ) {
                    const target = $( ev.target ).parents( '.dd-item' ).first();
                    if ( target ) {
                        target.addClass( 'js-deleted hidden' );
                        $this.updateSaveButtonState( $this.__ACTION_REMOVE__, true );
                    }
                }
            } );

        },

        __resetChecked(target) {
            let items = target.find( '.js-check-input:checked' );
            if ( items && items.length >= 1 ) {
                $.each( items, function (ix, el) {
                    const self = $( el );
                    if ( self.is( ':checked' ) ) {
                        self.prop( 'checked', false );
                    }
                } );
            }
        },

        __setupListeners() {
            const $this = this;

            if ( this.__hasMenuItems() ) {
                this.__bindClickMenuItemRemove();
            }

            //#! [POST, PAGE, CATEGORY, ETC] Add to menu button on click (posts, pages, categories.. but Custom)
            this.__btnAddToMenu.on( 'click', function (ev) {
                ev.preventDefault();
                const self = $( this );
                const target = $( '.collapse.show ' + self.attr( 'data-target' ) );
                if ( typeof ( target ) !== 'undefined' ) {
                    let selectedEntries = target.find( '.js-check-input:checked' );
                    $.each( selectedEntries, function (ix, el) {
                        let entry = $( el ),
                            dataID = entry.val(),
                            dataType = entry.attr( 'data-type' ),
                            dataMenuItemID = entry.attr( 'data-menu-item-id' ),
                            dataTitle = entry.attr( 'data-title' ),
                            dataSelector = dataType + dataID;

                        $this.__nestable.nestable( 'add', {
                            "id": dataID,
                            "selector": dataSelector,
                            "menu-item-id": dataMenuItemID,
                            "type": dataType,
                            "children": []
                        } );
                        //#! Update the inner content (text + remove button)
                        const theElement = $( '[data-id="' + dataID + '"]' );
                        const ddHandle = $( '.dd-handle', theElement );
                        $( '> .dd-content', ddHandle ).html( dataTitle );
                        $( '<a href="#" class="js-btn-remove" title="' + pageLocale.delete_text_title + '">' + pageLocale.delete_text + '</a>' )
                            .insertBefore( ddHandle );
                    } );
                    if ( selectedEntries.length >= 1 ) {
                        $this.updateSaveButtonState( $this.__ACTION_ADD__ );
                        $this.__bindClickMenuItemRemove();
                        $this.__resetChecked( target );
                    }
                }
            } );

            //#! [CUSTOM] Add to menu button on click (Custom entries)
            this.__btnCustomAddToMenu.on( 'click', function (ev) {
                ev.preventDefault();

                let dataTitle = $( '#menu-item-title' ).val(),
                    dataUrl = $( '#menu-item-url' ).val(),
                    dataType = $( '#menu-item-data-type' ).val(),
                    dataID = $this.__uniqueID(),
                    dataSelector = dataType + dataID;

                if ( !dataTitle || dataTitle.length < 1 ) {
                    return false;
                }
                if ( !dataUrl || dataUrl.length < 1 ) {
                    return false;
                }

                $this.__nestable.nestable( 'add', {
                    "id": dataID,
                    "selector": dataSelector,
                    "menu-item-id": 0,
                    "type": dataType,
                    "title": dataTitle,
                    "url": dataUrl,
                    "children": []
                } );
                //#! Update the inner content (text + remove button)
                const theElement = $( '[data-id="' + dataID + '"]' );
                const ddHandle = $( '.dd-handle', theElement );
                $( '> .dd-content', ddHandle ).html( dataTitle );
                $( '<a href="#" class="js-btn-remove" title="' + pageLocale.delete_text_title + '">' + pageLocale.delete_text + '</a>' )
                    .insertBefore( ddHandle );

                $this.updateSaveButtonState( $this.__ACTION_ADD__ );
                $this.__bindClickMenuItemRemove();
            } );

            //#! [SAVE MENU]
            this.__btnSaveMenu.on( 'click', function (ev) {
                const self = $( this );
                const menuItems = $this.__menuToArray( $this );
                const loader = $( '#menu-items-sortable .js-ajax-loader' );

                self.addClass( 'no-click' );
                loader.removeClass( 'hidden' );

                let ajaxData = {
                    url: locale.ajax.url,
                    method: 'POST',
                    async: true,
                    timeout: 29000,
                    data: {
                        action: 'menu_save',
                        menu_id: pageLocale.menu_id,
                        menu_items: menuItems,
                        [locale.nonce_name]: locale.nonce_value,
                    },
                };

                $.ajax( ajaxData )
                    .done( function (r) {
                        if ( r ) {
                            if ( r.success ) {
                                if ( r.data ) {
                                    showToast( r.data, 'success' );
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
                            showToast( locale.ajax.no_response, 'warning' );
                        }
                    } )
                    .fail( function (x, s, e) {
                        showToast( e, 'error' );
                    } )
                    .always( function () {
                        $this.updateSaveButtonState( $this.__ACTION_ADD__ );
                        loader.addClass( 'hidden' );
                    } );
            } );
        },

        //#! Retrieve the menu data as a list to send to server
        __menuToArray($this) {
            const mainMenuItems = $( '>.dd-list > .dd-item:not(.js-deleted)', $this.__nestable );

            let itemData = {};
            $.each( mainMenuItems, function (i, el) {
                const $item = $( el );
                const dataSelector = $item.attr( 'data-selector' );
                const type = $item.attr( 'data-type' );

                itemData[i] = {
                    id: $item.attr( 'data-id' ),
                    type: type,
                    selector: dataSelector,
                    menuItemId: $item.attr( 'data-menu-item-id' ),
                    children: [],
                };
                if ( 'custom' === type ) {
                    itemData[i]['title'] = $item.attr( 'data-title' );
                    itemData[i]['url'] = $item.attr( 'data-url' );
                }
                itemData[i]['children'] = $this.__getChildren( $this, $item );
            } );
            return itemData;
        },

        //#! Recursively build submenu items
        __getChildren($this, $menuItem) {
            let _result = [];
            let children = $( '>.dd-list >.dd-item:not(.js-deleted)', $menuItem );
            if ( children.length > 0 ) {
                $.each( children, function (i, el) {
                    let child = $( el );
                    let dataSelector = child.attr( 'data-selector' );
                    const type = child.attr( 'data-type' );
                    _result[i] = {
                        id: child.attr( 'data-id' ),
                        type: type,
                        selector: dataSelector,
                        menuItemId: child.attr( 'data-menu-item-id' ),
                        children: $this.__getChildren( $this, child ),
                    };
                    if ( 'custom' === type ) {
                        _result[i]['title'] = child.attr( 'data-title' );
                        _result[i]['url'] = child.attr( 'data-url' );
                    }
                } );
            }
            return _result;
        },

        __uniqueID() {
            // Math.random should be unique because of its seeding algorithm.
            // Convert it to base 36 (numbers + letters), and grab the first 9 characters
            // after the decimal.
            return '_' + Math.random().toString( 36 ).substr( 2, 9 );
        },

        updateSaveButtonState(action, enable = true) {
            if ( action === this.__ACTION_ADD__ ) {
                this.__placeholder.hide();
                this.__btnSaveMenu.removeClass( 'hidden' );
            }

            if ( !this.__hasMenuItems() ) {
                if ( !enable ) {
                    this.__btnSaveMenu.addClass( 'hidden' );
                }
                this.__placeholder.show();
            }

            if ( enable ) {
                this.__btnSaveMenu.removeClass( 'no-click disabled' );
            }
            else {
                this.__btnSaveMenu.addClass( 'no-click disabled' );
            }
        },

    };

    MenuBuilder.init();
} );

/* Menu name */
jQuery( function ($) {
    "use strict";

    const loader = $( '#form-menu-name .js-ajax-loader' );

    $( '.js-save-menu-title-button' ).on( 'click', function (ev) {
        ev.preventDefault();

        const self = $( this );
        const form = $( '#form-menu-name' );
        const nameField = $( '.name-field', form );
        if ( !nameField || !nameField.val().length > 0 ) {
            return false;
        }

        self.addClass( 'no-click' );
        loader.removeClass( 'hidden' );

        let ajaxConfig = {
            url: locale.ajax.url,
            method: 'POST',
            cache: false,
            timeout: 29000,
            data: {
                action: 'save_menu_name',
                menu_name: nameField.val(),
                menu_id: pageLocale.menu_id,
                [locale.nonce_name]: locale.nonce_value,
            }
        };
        $.ajax( ajaxConfig )
            .done( function (r) {
                if ( r ) {
                    if ( r.success ) {
                        showToast( r.data, 'success' );
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
                self.removeClass( 'no-click' );
                loader.addClass( 'hidden' );
            } );
    } );
} );

/* Menu options */
jQuery( function ($) {
    "use strict";

    const loader = $( '.form-menu-options .js-ajax-loader' );

    $( '.js-menu-save-options-button' ).on( 'click', function (ev) {
        ev.preventDefault();

        const self = $( this );
        const radio = $( 'input[name="display_as"]:checked' );
        if ( !radio || !radio.val().length > 0 ) {
            return false;
        }

        self.addClass( 'no-click' );
        loader.removeClass( 'hidden' );

        let ajaxConfig = {
            url: locale.ajax.url,
            method: 'POST',
            cache: false,
            timeout: 29000,
            data: {
                action: 'save_menu_options',
                display_as: radio.val(),
                menu_id: pageLocale.menu_id,
                [locale.nonce_name]: locale.nonce_value,
            }
        };
        $.ajax( ajaxConfig )
            .done( function (r) {
                if ( r ) {
                    if ( r.success ) {
                        showToast( pageLocale.text_options_saved, 'success' );
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
                self.removeClass( 'no-click' );
                loader.addClass( 'hidden' );
            } );
    } );
} );

/* Accordion */
jQuery( function ($) {
    "use strict";

    const accordion = $( '.cp-menu-edit-accordion' );

    $( '.js-trigger' ).on( 'click', function (ev) {
        ev.preventDefault();

        const self = $( this );
        const icon = self.next( '.js-sign' );

        if ( self.hasClass( 'collapsed' ) ) {
            icon.removeClass( 'fa-plus' ).addClass( 'fa-minus' );
        }
        else {
            icon.removeClass( 'fa-minus' ).addClass( 'fa-plus' );
        }
    } );
} );
