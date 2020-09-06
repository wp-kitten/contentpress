/*
 * Execute an ajax request every minute to check if the user is still logged in.
 * If not, it will be redirected to the login page
 *
 * @requires jQuery
 * @requires AppLocale
 */
jQuery(($) => {
    const locale = (typeof (window.AppLocale) !== 'undefined' ? window.AppLocale : false);
    if (locale) {
        window.setInterval(() => {
            let ajaxData = {
                action: 'heartbeat',
                [locale.nonce_name]: locale.nonce_value,
            };

            $.ajax( {
                url: locale.ajax.url,
                method: 'POST',
                cache: false,
                async: true,
                timeout: 20000,
                data: ajaxData
            } )
                .done( function (r) {
                    if ( r ) {
                        if ( ! r.success ) {
                            alert('Todo: notify user in a better way than an alert. You are not logged in.');
                            window.location.href = locale.ajax.login_url;
                        }
                    }
                    else {
                        console.warn( locale.ajax.no_response );
                    }
                } )
                .fail( function (x, s, e) {
                    console.warn( e );
                } );
        }, 60000);
    }
});
