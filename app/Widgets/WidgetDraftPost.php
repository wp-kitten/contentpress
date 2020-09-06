<?php

namespace App\Widgets;

class WidgetDraftPost extends AbstractWidgetBase
{
    public function __construct( $id = '', $options = [ 'title' => '' ] )
    {
        parent::__construct( $id, $options );

        if ( empty( $id ) ) {
            return;
        }

        if ( !empty( $id ) && cp_current_user_can( 'publish_posts' ) ) {
            add_action( 'contentpress/admin/footer', [ $this, '__loadWidgetScripts' ] );
        }
    }

    public function __loadWidgetScripts()
    {
        $id = $this->getId();
        ?>
        <script id="widget-<?php esc_attr_e( $id ); ?>">
            var editorInput = jQuery("post_draft-<?php esc_attr_e( $id );?>");
            if(editorInput) {
                ContentPressTextEditor.register( "post_draft-<?php esc_attr_e( $id );?>", new Quill( "#post_draft-editor-<?php esc_attr_e( $id );?>", {
                    modules: {
                        toolbar: [
                            [{ header: [false] }],
                            ['bold', 'italic', 'underline'],
                        ]
                    },
                    scrollingContainer: '.quill-scrolling-container',
                    placeholder: "<?php esc_html_e( __( 'a.Post content here' ) );?>",
                    theme: 'bubble'
                } ) );
            }
            jQuery( function ($) {
                "use strict";
                var locale = window.AppLocale,
                    postTitle = $( "#post-draft-title-<?php esc_attr_e( $id ); ?>" ),
                    postEditor = $( "#post_draft-<?php esc_attr_e( $id ); ?>" ),
                    htmlWrap = '<div class="row clearfix"><div class="column full">__HTML__</div></div>';
                $( "#post_draft-submit-<?php esc_attr_e( $id ); ?>" ).on( 'click', function (ev) {
                    ev.preventDefault();

                    var self = $( this );
                    self.addClass( 'no-click' );

                    var content = htmlWrap.replace( '__HTML__', ContentPressTextEditor.getHTML( "post_draft-<?php esc_attr_e( $id );?>" ) );

                    $.ajax( {
                        url: locale.ajax.url,
                        method: 'POST',
                        async: true,
                        timeout: 29000,
                        data: {
                            action: 'create_post_draft',
                            post_title: postTitle.val(),
                            // keeps the post data size small
                            post_content: content,
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
                            self.removeClass( 'no-click' );
                            postTitle.val( '' );
                            postEditor.val( '' );
                            ContentPressTextEditor.clear( "post_draft-<?php esc_attr_e( $id );?>" );
                        } );
                    return false;
                } );
            } );
        </script>
        <?php
    }

    public function render()
    {
        if ( !cp_current_user_can( 'publish_posts' ) ) {
            return;
        }
        if ( empty( $this->getId() ) ) {
            return;
        }

        $id = $this->getId();
        ?>
        <div class="card mb-2 widget"
             data-id="<?php esc_attr_e( $id ); ?>"
             data-class="<?php esc_attr_e( __CLASS__ ); ?>">

            <div class="card-body">
                <h4 class="card-title">
                    <?php echo \apply_filters( 'contentpress/widget/title', esc_html( __( 'a.Draft post' ) ), __CLASS__ ); ?>
                </h4>

                <form id="widget-draft-post">
                    <div class="form-group">
                        <label for="post-draft-title-<?php esc_attr_e( $id ); ?>"><?php esc_html_e( __( 'a.Post title' ) ); ?></label>
                        <input type="text" class="form-control" id="post-draft-title-<?php esc_attr_e( $id ); ?>" placeholder="<?php esc_attr_e( __( 'a.Post title' ) ); ?>" required/>
                    </div>
                    <div class="form-group">
                        <div class="quill-scrolling-container">
                            <div id="post_draft-editor-<?php esc_attr_e( $id ); ?>"></div>
                        </div>
                        <textarea id="post_draft-<?php esc_attr_e( $id ); ?>" class="form-control hidden"></textarea>
                    </div>
                    <input type="button" id="post_draft-submit-<?php esc_attr_e( $id ); ?>" class="btn btn-primary btn-sm" value="<?php esc_attr_e( __( 'a.Submit' ) ); ?>"/>
                </form>
            </div>
        </div>
        <?php
    }
}
