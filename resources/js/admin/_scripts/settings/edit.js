var pageLocale = (typeof (window.SettingsPageLocale) !== 'undefined' ? window.SettingsPageLocale : false);
if (!pageLocale) {
    throw new Error('SettingsPageLocale locale not loaded.');
}

//<editor-fold desc="delete-links">
jQuery(function ($) {
    "use strict";

    var deleteLinks = $('.js-button-form-delete');
    if (typeof (deleteLinks) !== 'undefined') {
        deleteLinks.on('click', function (ev) {
            if (!confirm(pageLocale.confirm_post_type_delete)) {
                ev.preventDefault();
                return false;
            }
        });
    }
});
//<editor-fold desc="delete-links">



//<editor-fold desc=":: Post types - Preview ::">
jQuery(function ($) {
    "use strict";

    var links = $('.js-button-preview');
    if (typeof (links) !== 'undefined') {
        links.on('click', function (ev) {
            ev.preventDefault();
            var id = $(this).attr('data-id');
            if(typeof(id) !== 'undefined'){
                var row = $('#row-edit-'+id);
                if(row){
                    row.removeClass('hidden');
                }
            }
        });
        $('.js-button-form-close').on('click', function(ev){
            ev.preventDefault();
            var id = $(this).attr('data-id');
            if(typeof(id) !== 'undefined'){
                var row = $('#row-edit-'+id);
                if(row){
                    row.addClass('hidden');
                }
            }
        });
    }
});
//</editor-fold desc=":: Post types - Preview ::">
