const locale = ( typeof ( window.AppLocale ) !== 'undefined' ? window.AppLocale : false );
if ( !locale ) {
    throw new Error( 'AppLocale locale not loaded.' );
}
const pageLocale = ( typeof ( window.MenuLocale ) !== 'undefined' ? window.MenuLocale : false );
if ( !pageLocale ) {
    throw new Error( 'MenuLocale locale not loaded.' );
}

/* Sortable menu items */
jQuery( function ($) {
    "use strict";

    //#! vars
    let menuList = $( '#list-1' ),
        btnAddToMenu = $( '.js-btn-add-to-menu' ),
        btnAddCustomToMenu = $( '.js-custom-add-to-menu-button' ),
        btnSaveMenu = $( '.js-btn-save-menu' ),
        emptyMenuItem = $( '.menu-empty' ),
        __ACTION_ADD__ = 'x0000',
        __ACTION_REMOVE__ = 'x0001'
    ;

    /**
     * Stores the last ID used for custom entries
     * @type {number}
     * @internal
     * @private
     */
    let _lastCustomMenuItemID = 1000;

    //#! Helpers
    const UIHelper = {

        initMainSortable() {
            $( ".js-menu-list" ).sortable( {
                placeholder: "ui-state-highlight",
                connectWith: '.submenu-list',
                forcePlaceholderSize: true,
                forceHelperSize: true,
                tolerance: "pointer",
                start: function (event, ui) {
                    $( 'body' ).addClass( 'dragging' );
                },
                stop: function (event, ui) {
                    $( 'body' ).removeClass( 'dragging' );
                    btnSaveMenu.removeClass( 'no-click disabled' );
                },
            } ).disableSelection();

        },

        initConnectedSortable() {
            const submenuLists = $( ".submenu-list" );
            submenuLists.sortable( {
                placeholder: "ui-state-highlight",
                connectWith: '.submenu-list,.js-menu-list',
                forcePlaceholderSize: true,
                forceHelperSize: true,
                tolerance: "pointer",
                start: function (event, ui) {
                    $( 'body' ).addClass( 'dragging' );
                },
                stop: function (event, ui) {
                    $( 'body' ).removeClass( 'dragging' );
                    btnSaveMenu.removeClass( 'no-click disabled' );
                },
            } ).disableSelection();
        },

        updateSaveButtonState(action, enable = true) {
            if ( action === __ACTION_ADD__ ) {
                emptyMenuItem.hide();
                btnSaveMenu.removeClass( 'hidden' );
            }

            if ( !UIHelper.__hasMenuItems() ) {
                if ( !enable ) {
                    btnSaveMenu.addClass( 'hidden' );
                }
                emptyMenuItem.show();
            }

            if ( enable ) {
                btnSaveMenu.removeClass( 'no-click disabled' );
            }
            else {
                btnSaveMenu.addClass( 'no-click disabled' );
            }
        },

        __hasMenuItems() {
            const c = menuList.find( '.list-item' );
            return ( c && c.length >= 1 );
        },

        __removeMenuItem(ev, $element) {
            ev.preventDefault();
            ev.stopPropagation();

            if ( confirm( pageLocale.confirm_delete_item ) ) {
                //#! Get the target
                const targetID = $element.attr( 'data-target' );
                if ( typeof ( targetID ) !== 'undefined' ) {
                    menuList.find( '.list-item[data-selector="' + targetID + '"]' ).remove();
                    UIHelper.updateSaveButtonState( __ACTION_REMOVE__, true );
                }
            }
        },

        __setupDynamicListeners() {
            $.each( $( '.js-btn-remove', menuList ), function (ix, el) {
                $( el ).off( 'click' ).on( 'click', function (ev) {
                    UIHelper.__removeMenuItem( ev, $( el ) );
                } );
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

        __saveMenu(ev) {
            const menuItems = UIHelper.__menuToArray();
            const loader = $( '#menu-items-sortable .js-ajax-loader' );

            btnSaveMenu.addClass( 'no-click' );
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
                    //! Hide the save button if there are no menu items
                    UIHelper.updateSaveButtonState( __ACTION_REMOVE__, false );
                    loader.addClass( 'hidden' );
                } );
        },

        //#! Retrieve the menu data as a list to send to server
        __menuToArray() {
            const parentIds = menuList.sortable( 'toArray', {
                //#! the attribute to look for
                attribute: 'data-selector',
                //#! The key to extract
                key: 'data-id',
                //#! The RegExp expression allowing to determine how to split the data in key-value. In this case it's just the value
                expression: /(.+)/
            } );

            const mainMenuItems = $( '>.list-item', $( '.js-menu-list' ) );

            let itemData = {};
            $.each( mainMenuItems, function (i, el) {
                const $item = $( el );
                const dataSelector = $item.attr( 'data-selector' );
                const type = $item.attr( 'data-type' );

                itemData[i] = {
                    id: $item.attr( 'data-id' ),
                    type: type,
                    selector: dataSelector,
                    menuItemId: $item.attr('data-menu-item-id'),
                    children: [],
                };
                if ( 'custom' === type ) {
                    itemData[i]['title'] = $item.attr( 'data-title' );
                    itemData[i]['url'] = $item.attr( 'data-url' );
                }
                itemData[i]['children'] = UIHelper.__getChildren( $item );
            } );
            return itemData;
        },

        //#! Recursively build submenu items
        __getChildren($menuItem) {
            let _result = [];
            let children = $( '> .submenu-list > li', $menuItem );
            if ( children.length > 0 ) {
                $.each( children, function (i, el) {
                    let child = $( el );
                    let dataSelector = child.attr( 'data-selector' );
                    const type = child.attr( 'data-type' );
                    _result[i] = {
                        id: child.attr( 'data-id' ),
                        type: type,
                        selector: dataSelector,
                        menuItemId: child.attr('data-menu-item-id'),
                        children: UIHelper.__getChildren( child ),
                    };
                    if ( 'custom' === type ) {
                        _result[i]['title'] = child.attr( 'data-title' );
                        _result[i]['url'] = child.attr( 'data-url' );
                    }
                } );
            }
            return _result;
        }
    };

    //[[ OnLoad ===================================================
    UIHelper.initMainSortable();
    UIHelper.initConnectedSortable();
    UIHelper.updateSaveButtonState( __ACTION_ADD__, false );
    UIHelper.__setupDynamicListeners();

    //! Event listeners
    btnSaveMenu.on( 'click', UIHelper.__saveMenu );

    //#! Add entries to menu (post types & categories), except custom menu items
    btnAddToMenu.on( 'click', function (ev) {
        const self = $( this ),
            target = $( '.collapse.show ' + self.attr( 'data-target' ) );
        if ( typeof ( target ) !== 'undefined' ) {
            let selectedEntries = target.find( '.js-check-input:checked' );
            $.each( selectedEntries, function (ix, el) {
                let entry = $( el ),
                    dataID = entry.val(),
                    dataType = entry.attr( 'data-type' ),
                    dataMenuItemID = entry.attr( 'data-menu-item-id' ),
                    dataTitle = entry.attr( 'data-title' ),
                    dataSelector = dataType + dataID;

                menuList.append( '<li data-selector="' + dataSelector + '" data-id="' + dataID + '" data-menu-item-id="' + dataMenuItemID + '" data-type="' + dataType + '" class="list-item"><p>' + dataTitle + '<a href="#" class="js-btn-remove" data-target="' + dataSelector + '" title="' + pageLocale.delete_text_title + '">' + pageLocale.delete_text + '</a></p><ul class="list-unstyled submenu-list"></ul></li>' );
            } );
            if ( selectedEntries.length >= 1 ) {
                UIHelper.updateSaveButtonState( __ACTION_ADD__ );
                UIHelper.__setupDynamicListeners();
                UIHelper.__resetChecked( target );
                UIHelper.initConnectedSortable();
            }
        }
    } );

    //#! Add custom entries to menu
    btnAddCustomToMenu.on( 'click', function (ev) {
        let title = $( '#menu-item-title' ).val(),
            url = $( '#menu-item-url' ).val(),
            type = $( '#menu-item-data-type' ).val(),
            id = _lastCustomMenuItemID + 1,
            dataSelector = type + id;

        if ( !title || title.length < 1 ) {
            return false;
        }
        if ( !url || url.length < 1 ) {
            return false;
        }

        menuList.append( '<li data-selector="' + dataSelector + '" data-id="' + id + '" data-menu-item-id="0" data-type="' + type + '" data-title="' + title + '" data-url="' + url + '" class="list-item"><p>' + title + '<a href="#" class="js-btn-remove" data-target="' + dataSelector + '" title="' + pageLocale.delete_text_title + '">' + pageLocale.delete_text + '</a></p><ul class="list-unstyled submenu-list"></ul></li>' );

        UIHelper.updateSaveButtonState( __ACTION_ADD__ );
        UIHelper.__setupDynamicListeners();
    } );
} );

/* Menu name */
jQuery( function ($) {
    "use strict";

    const loader = $( '#form-menu-name .js-ajax-loader' );

    $( '.js-save-menu-title-button' ).on( 'click', function (ev) {
        ev.preventDefault();

        const self = $( this );
        const form = $('#form-menu-name');
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

        const self = $(this);
        const icon = self.next('.js-sign');

        if(self.hasClass('collapsed')){
            icon.removeClass('fa-plus').addClass('fa-minus');
        }
        else {
            icon.removeClass('fa-minus').addClass('fa-plus');
        }
    } );
} );
