var pageLocale = (typeof (window.CategoriesIndexLocale) !== 'undefined' ? window.CategoriesIndexLocale : false);
if (!pageLocale) {
    throw new Error('CategoriesIndexLocale locale not loaded.');
}

//<editor-fold desc="category-featured-image">
jQuery(function ($) {
    "use strict";

    var locale = window.AppLocale;

    let __dropifySetup = false;
    const dropifyImageUploader = new DropifyImageUploader(
        /* the selector for the file upload field */
        $( '#category_image_field' )
    );
    if( ! __dropifySetup){
        dropifyImageUploader.setup();
        __dropifySetup = true;
    }

});
//</editor-fold desc="category-featured-image">

//<editor-fold desc="category-description-quill">
jQuery(function ($) {
    "use strict";

    var elementsMap = {};

    $('.category-form').each(function (ix, el) {
        var theForm = $(el),
            textareas = $('.js-text-editor', theForm);

        elementsMap[theForm.attr('id')] = [];

        //#! Enable the editor for each textarea
        textareas.each(function (x, tx) {
            var textarea = $(tx),
                textAreaId = textarea.attr('id');

            elementsMap[theForm.attr('id')].push(textAreaId);

            ValPressTextEditor.register(textAreaId, new Quill('#' + textAreaId + '-editor', {
                modules: {
                    toolbar: [
                        [{header: [false]}],
                        ['bold', 'italic', 'underline'],
                    ]
                },
                scrollingContainer: '.quill-scrolling-container',
                placeholder: pageLocale.description_placeholder,
                theme: 'bubble'
            }));
        });
    }).on('submit', function (e) {
        var textareas = elementsMap[$(this).attr('id')];
        textareas.map(function (id, x) {
            $('#' + id).val(ValPressTextEditor.getHTML(id));
            return id;
        });
    });
});
//</editor-fold desc="category-description-quill">
