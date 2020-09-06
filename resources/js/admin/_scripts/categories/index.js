var pageLocale = (typeof (window.CategoriesIndexLocale) !== 'undefined' ? window.CategoriesIndexLocale : false);
if (!pageLocale) {
    throw new Error('CategoriesIndexLocale locale not loaded.');
}


//<editor-fold desc="selectize-parent-categories">
jQuery(function ($) {
    "use strict";
    $('#field-category_id').selectize({
        create: false,
    });
    //#! END $( '#post_categories' ).selectize
});
//</editor-fold desc="selectize-parent-categories">


//<editor-fold desc="sortable-categories">
jQuery(function ($) {
    "use strict";

    var locale = window.AppLocale;

    $(".js-sortable").sortable({
        group: 'sortable',

        onDrop: function ($item, container, _super) {
            container.el.removeClass("active");

            var droppedID = $item.attr('data-id'),
                parentID = container.el.parents('li').first().attr('data-id');
            if (typeof (parentID) === 'undefined') {
                parentID = 0;
            }

            //#! Save changes
            $.ajax({
                url: locale.ajax.url,
                method: 'POST',
                async: true,
                timeout: 29000,
                data: {
                    action: 'update_category_parent',
                    category_id: droppedID,
                    parent_category_id: parentID,
                    [locale.nonce_name]: locale.nonce_value,
                },
            })
                .done(function (r) {
                    if (r) {
                        if (r.success) {
                            if (r.data) {
                                showToast(r.data, 'success');
                            }
                            else {
                                showToast(AppLocale.ajax.empty_response, 'warning');
                            }
                        }
                        else {
                            if (r.data) {
                                showToast(r.data, 'warning');
                            }
                            else {
                                showToast(AppLocale.ajax.empty_response, 'warning');
                            }
                        }
                    }
                    else {
                        showToast(AppLocale.ajax.no_response, 'warning');
                    }
                })
                .fail(function (x, s, e) {
                    showToast(e, 'error');
                })
                .always(function () {
                });

            _super($item, container);
        },
        tolerance: 6,
        distance: 10,
    });
});
//</editor-fold desc="sortable-categories">


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

            ContentPressTextEditor.register(textAreaId, new Quill('#' + textAreaId + '-editor', {
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
            $('#' + id).val(ContentPressTextEditor.getHTML(id));
            return id;
        });
    });
});
//</editor-fold desc="category-description-quill">


//<editor-fold desc="category-translations">
jQuery(function($){
    "use strict";

    const locale = window.AppLocale;

    //#! Local cache to save ajax requests for the same plugin
    let currentCategoryID = '';

    //#! Open info modal
    $( '#infoModal' ).on( 'show.bs.modal', function (ev) {
        //#! The element triggering the modal to open
        const sender = $( ev.relatedTarget );
        const categoryID = sender.data( 'categoryId' );
        const $modal = $( this );
        const $modalTitle = $modal.find( '.modal-title' );
        const $modalBody = $modal.find( '.modal-body' );
        const $modalContent = $modalBody.find( '.js-content' );
        const $modalLoader = $modalBody.find( '.js-ajax-loader' );

        if ( typeof ( categoryID ) === 'undefined' ) {
            $modalLoader.addClass( 'hidden' );
            $modalTitle.text( pageLocale.text_error );
            $modalContent.html( pageLocale.text_error_category_id_missing ).removeClass( 'hidden' );
        }
        else {

            if ( currentCategoryID === categoryID ) {
                $modalLoader.addClass( 'hidden' );
                $modalContent.removeClass( 'hidden' );
            }
            else {
                $modalLoader.removeClass( 'hidden' );

                $.ajax( {
                    url: locale.ajax.url,
                    method: 'POST',
                    async: true,
                    timeout: 29000,
                    data: {
                        action: 'get_category_translations',
                        category_id: categoryID,
                        [locale.nonce_name]: locale.nonce_value
                    }
                } )
                    .done( function (r) {
                        if ( r ) {
                            if ( r.success ) {
                                if ( r.data && r.data.length > 0 ) {
                                    currentCategoryID = categoryID;
                                    $modalTitle.text( pageLocale.text_translations );
                                    $modalContent.html( r.data ).removeClass( 'hidden' );
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
                            showToast( locale.ajax.no_response, 'error' );
                        }
                    } )
                    .fail( function (x, s, e) {
                        showToast( e, 'error' );
                    } )
                    .always( function () {
                        $modalLoader.addClass( 'hidden' );
                    } );
            }
        }
    } );
});
//</editor-fold desc="category-translations">

