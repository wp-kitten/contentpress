var pageLocale = (typeof (window.UsersPageLocale) !== 'undefined' ? window.UsersPageLocale : false);
if (!pageLocale) {
    throw new Error('UsersPageLocale locale not loaded.');
}

//<editor-fold desc="user-profile-image">
jQuery(function ($) {
    "use strict";

    var locale = window.AppLocale;

    let __dropifySetup = false;
    const dropifyImageUploader = new DropifyImageUploader(
        /* the selector for the file upload field */
        $( '#user_image_field' )
    );
    if( ! __dropifySetup){
        dropifyImageUploader.setup({
            on_add_image: function ($this) {
                var value = $this.element[0].files[0];
                if (value.length < 1) {
                    return false;
                }

                var ajaxData = new FormData();
                ajaxData.append('action', 'set_user_image');
                ajaxData.append('user_id', pageLocale.user_id);
                ajaxData.append('user_image', value);
                ajaxData.append(locale.nonce_name, locale.nonce_value);

                $.ajax({
                    url: locale.ajax.url,
                    method: 'POST',
                    async: true,
                    timeout: 29000,
                    data: ajaxData,
                    processData: false,
                    contentType: false
                })
                    .done(function (r) {
                        if (r) {
                            if (r.success) {
                                showToast(pageLocale.text_image_set, 'success');
                            }
                            else {
                                showToast(locale.ajax.empty_response, 'warning');
                            }
                        }
                        else {
                            showToast(locale.ajax.no_response, 'error');
                        }
                    })
                    .fail(function (x, s, e) {
                        showToast(e, 'error');
                    })
            },
            on_remove_image: function () {
                $.ajax({
                    url: locale.ajax.url,
                    method: 'POST',
                    async: true,
                    timeout: 29000,
                    data: {
                        action: 'delete_user_image',
                        user_id: pageLocale.user_id,
                        [locale.nonce_name]: locale.nonce_value
                    }
                })
                    .done(function (r) {
                        if (r) {
                            if (r.success) {
                                showToast(pageLocale.text_image_removed, 'success');
                            }
                            else {
                                showToast(locale.ajax.empty_response, 'warning');
                            }
                        }
                        else {
                            showToast(locale.ajax.no_response, 'error');
                        }
                    })
                    .fail(function (x, s, e) {
                        showToast(e, 'error');
                    });
            },
        });
        __dropifySetup = true;
    }
});
//<editor-fold desc="user-profile-image">

//<editor-fold desc="wysiwyg-editors">
jQuery(function ($) {
    "use strict";

    ValPressTextEditor.register('author_bio', new Quill('#field-bio-editor', {
        modules: {
            toolbar: [
                [{header: [false]}],
                ['bold', 'italic', 'underline'],
            ]
        },
        scrollingContainer: '.quill-scrolling-container',
        placeholder: pageLocale.text_info_bio,
        theme: 'bubble'
    }));

    //#! On save button click, get the values from editors
    $('#js-acc-mgmt-update-btn').on('click', function (ev) {
        $('#field-bio').val(ValPressTextEditor.getHTML('author_bio'));
    });
});
//<editor-fold desc="wysiwyg-editors">

