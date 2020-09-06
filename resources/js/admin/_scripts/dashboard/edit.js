jQuery( function ($) {
    'use strict';

    //#! Global data that will be sent through ajax
    let dashData = {};

    //#! Sortable
    dragula( [
        document.querySelector( "#section-1" ),
        document.querySelector( "#section-2" ),
        document.querySelector( "#section-3" ),
        document.querySelector( "#section-4" ),
        /*
         * This section is dynamically injected in the right sidebar,
         * and it will only be available if the current user can "edit_dashboard"
         */
        document.querySelector( "#section-dash-widgets" ),
    ] );

    //#! Save
    const locale = window.AppLocale;
    const _loader = $( '#js-loader' );

    $( '.js-dash-btn-save' ).on( 'click', function (e) {
        e.preventDefault();
        const self = $( this );

        self.addClass( 'no-click' );
        _loader.removeClass( 'hidden' );

        //#! Loop through sections
        $( '.js-dragula-section' ).each( function (ix, el) {
            var id = $( el ).attr( 'id' );
            dashData[id] = {};

            $( '.widget', $( el ) ).each( function (x, w) {
                var widget = $( w ),
                    _widget_class = widget.attr( 'data-class' );
                dashData[id][_widget_class] = widget.attr( 'data-id' );
            } );
        } );

        $.ajax( {
            url: locale.ajax.url,
            method: 'POST',
            async: true,
            timeout: 29000,
            data: {
                action: 'update_dashboard_ui',
                dash_content: dashData,
                [locale.nonce_name]: locale.nonce_value,
            },
        } )
            .done( function (r) {
                if ( r ) {
                    if ( r.success ) {
                        if ( r.data ) {
                            showToast( r.data, 'success' );
                        }
                        else {
                            showToast( AppLocale.ajax.empty_response, 'warning' );
                        }
                    }
                    else {
                        if ( r.data ) {
                            showToast( r.data, 'warning' );
                        }
                        else {
                            showToast( AppLocale.ajax.empty_response, 'warning' );
                        }
                    }
                }
                else {
                    showToast( AppLocale.ajax.no_response, 'warning' );
                }
            } )
            .fail( function (x, s, e) {
                showToast( e, 'error' );
            } )
            .always( function () {
                _loader.addClass( 'hidden' );
                self.removeClass( 'no-click' );
            } );
    } );
} );
