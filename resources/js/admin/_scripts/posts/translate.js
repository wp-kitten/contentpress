var pageLocale = (typeof (window.PostsLocale) !== 'undefined' ? window.PostsLocale : false);
if (!pageLocale) {
    throw new Error('PostsLocale locale not loaded.');
}

/*#!
 * Global object
 * Themes and plugins MUST override the getContent method in order to inject their own content
 */
window.AppTextEditor = {
    getContent(contentBuilder) {
        var editor = $( '#plugin_text_editor' );
        return ( editor ? editor.val() : '' );
    }
};


//<editor-fold desc="post-actions">

jQuery(function ($) {
    "use strict";
    var locale = window.AppLocale;

    ValPressTextEditor.register('post_excerpt', new Quill('#post_excerpt-editor', {
        modules: {
            toolbar: [
                [{header: [false]}],
                ['bold', 'italic', 'underline'],
            ]
        },
        scrollingContainer: '.quill-scrolling-container',
        placeholder: pageLocale.text_description,
        theme: 'bubble'
    }));

    $('.js-save-post-button').on('click', function (e) {
        e.preventDefault();

        var self = $(this);
        self.addClass('no-click');

        $.ajax({
            url: locale.ajax.url,
            method: 'POST',
            async: true,
            timeout: 29000,
            data: {
                action: 'save_post_translation',
                post_status: $('#post_status').val(),
                post_title: $('#post_title').val(),
                // keeps the post data size small
                post_content: AppTextEditor.getContent( null ),
                post_excerpt: ValPressTextEditor.getHTML('post_excerpt'),
                post_categories: $('#post_categories').val(),
                post_tags: $('#post_tags').val(),
                language_id: pageLocale.language_id,
                parent_post_id: pageLocale.parent_post_id,
                current_post_id: pageLocale.current_post_id,
                sticky_featured: $('#sticky_featured').val(),
                comments_enabled: $('#comments_enabled').val(),
                __post_image_id: $('#__post_image_id').val(),
                [locale.nonce_name]: locale.nonce_value,
            },
        })
            .done(function (r) {
                if (r) {
                    if (r.success) {
                        if (r.data) {
                            showToast(r.data.message, 'success');

                            //#! Update the preview url
                            $('.view-post-button').attr('href', r.data.preview_url);
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
                self.removeClass('no-click');
            });
        return false;
    });
});
//</editor-fold desc="post-actions">

//<editor-fold desc="selectize-categories-tags">
jQuery(function ($) {
    "use strict";
    $('#post_categories, #post_tags').selectize({
        create: false
    });
    //#! END $( '#post_categories, #post_tags' ).selectize
});
//</editor-fold desc="selectize-categories-tags">

