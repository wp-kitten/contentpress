var pageLocale = (typeof (window.SettingsPageLocale) !== 'undefined' ? window.SettingsPageLocale : false);
if (!pageLocale) {
    throw new Error('SettingsPageLocale locale not loaded.');
}

//<editor-fold desc="action-links">
jQuery(function ($) {
    "use strict";

    //#! Edit links
    var editLinks = $('.js-button-edit');
    if (typeof (editLinks) !== 'undefined') {
        editLinks.on('click', function (e) {
            e.preventDefault();
            var link = $(this),
                id = link.attr('data-id'),
                row = $('#row-edit-' + id);
            if (typeof (row) !== 'undefined') {
                if (row.hasClass('hidden')) {
                    row.removeClass('hidden');
                }
                else {
                    row.addClass('hidden');
                }
            }
        });
    }

    //#! Update links
    var updateLinks = $('.js-button-form-update');
    if (typeof (updateLinks) !== 'undefined') {
        updateLinks.on('click', function (e) {
            e.preventDefault();
            var self = $(this),
                id = self.attr('data-id'),
                editRow = $('#row-edit-' + id),
                mainRow = $('#row-' + id);
            if (typeof (editRow) !== 'undefined') {
                var fieldName = $('.field-name', editRow).val(),
                    fieldDisplayName = $('.field-display-name', editRow).val(),
                    fieldPluralName = $('.field-plural-name', editRow).val(),
                    __nameCell = $('.post-type-name-cell', mainRow),
                    __displayNameCell = $('.post-type-display-name-cell', mainRow),
                    __pluralNameCell = $('.post-type-plural-name-cell', mainRow),
                    //#! checkboxes
                    allowCategoriesChk = $('.allow_categories', editRow),
                    allowCommentsChk = $('.allow_comments', editRow),
                    allowTagsChk = $('.allow_tags', editRow),
                    ajaxData = {
                        action: 'update_post_type',
                        id: id,
                        name: fieldName,
                        display_name: fieldDisplayName,
                        plural_name: fieldPluralName,
                        [window.AppLocale.nonce_name]: window.AppLocale.nonce_value,
                    };
                if (allowCategoriesChk.is(':checked')) {
                    ajaxData['allow_categories'] = true;
                }
                if (allowCommentsChk.is(':checked')) {
                    ajaxData['allow_comments'] = true;
                }
                if (allowTagsChk.is(':checked')) {
                    ajaxData['allow_tags'] = true;
                }

                //#! update
                if (fieldName.length > 0 && fieldDisplayName.length > 0) {
                    self.addClass('no-click');
                    $.ajax({
                        url: window.AppLocale.ajax.url,
                        method: 'POST',
                        timeout: 29000,
                        async: true,
                        cache: false,
                        data: ajaxData
                    })
                        .done(function (r) {
                            if (r) {
                                if (r.success) {
                                    // update + hide
                                    __nameCell.text(fieldName);
                                    __displayNameCell.text(fieldDisplayName);
                                    __pluralNameCell.text(fieldPluralName);
                                    editRow.addClass('hidden');
                                }
                                else {
                                    console.warn('response', r);
                                }
                            }
                            else {
                                console.error('no response')
                            }
                        })
                        .fail(function (x, s, e) {
                            console.error(e)
                        })
                        .always(function () {
                            self.removeClass('no-click');
                        });
                }
            }
        });
    }
});
//</editor-fold desc="action-links">


