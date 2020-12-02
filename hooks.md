#Hooks  (must be reviewed)

This document lists all hooks registered by the application, and the order they're executed.

### Frontend
* contentpress/plugins/loaded
* contentpress/theme/loaded ($themeName)
* contentpress/app/loaded

### Backend
* contentpress/plugins/loaded
* contentpress/theme/loaded
* contentpress/admin/init
* contentpress/app/loaded


### Backend Action Hooks
* contentpress/admin/head
* contentpress/admin/footer
* contentpress/post/deleted ($postID)
* contentpress/post/new (App\Models\Post $post)
* contentpress/post/actions ($postID)
* contentpress/comment/status_changed (PostComments $comment, $oldStatusID)
* contentpress/enqueue_text_editor () -> cp_enqueue_text_editor_scripts()
* contentpress/post_editor_content ($postContent = '')
* contentpress/post_editor_content/before
* contentpress/post_editor_content/after
* contentpress/plugin/activate ($pluginDirName)
* contentpress/plugin/activated ($pluginDirName, $pluginInfo)
* contentpress/plugin/deactivate ($pluginDirName)
* contentpress/plugin/deactivated ($pluginDirName, $pluginInfo)
* contentpress/plugin/delete ($pluginDirName)
* contentpress/plugin/deleted ($pluginDirName)
* contentpress/admin/sidebar/menu
* contentpress/admin/sidebar/menu/dashboard
* contentpress/admin/sidebar/menu/posts/{$postType}
* contentpress/admin/sidebar/menu/menus
* contentpress/admin/sidebar/menu/media
* contentpress/admin/sidebar/menu/plugins
* contentpress/admin/sidebar/menu/themes
* contentpress/admin/sidebar/menu/users
* contentpress/admin/sidebar/menu/settings

### Frontend Action Hooks
* contentpress/site/head
* contentpress/site/footer
* contentpress/submit_comment
* contentpress/submit_comment
* contentpress/submit_comment
* contentpress/submit_comment
* contentpress/post/footer
* contentpress/menu::{menu-slug}/before
* contentpress/menu::{menu-slug}/after
* contentpress/menu::{menu-slug}
* contentpress/comment/render (PostComments $comment, $withReplies = true)
* contentpress/comment/replies (PostComments $comment)
* contentpress/comment/actions (PostComments $comment, $postID)


### Backend Filter Hooks
* ContentPress
    * contentpress::image-sizes ($imageSizes = [])
    * contentpress/the_post_editor_content ($postContent = '')
    * contentpress/register_view_paths ($viewPaths = [])
    * contentpress/share-buttons ($buttons)
    * contentpress/post/excerpt ($postExcerpt)
    * contentpress/admin/right_sidebar/show ($visibility = false) == verify necessity
    * contentpress/share-buttons/list ($list = [])
    * contentpress/texteditor/editor-styles ($stylesheetLinks = [])
    * contentpress/widget/title ($title)

* Imported from WordPress
    * kses_allowed_protocols ($protocols = [])
    * attribute_escape ($safe_text, $text)
    * esc_textarea ($safe_text, $text)
    * js_escape ($safe_text, $text)
    * esc_html ($safe_text, $text)
    * wp_parse_str ($array = [])
    * sanitize_text_field ($filtered, $str)
    * sanitize_file_name_chars ($special_chars, $filename_raw)
    * sanitize_file_name ($filename, $filename_raw)
    * mime_types ($mimeTypes = [])
    * upload_mimes ($t, $user)
    * sanitize_textarea_field ($filtered, $str)
    * wp_kses_allowed_html ($allowedposttags, $context = 'explicit')
    * pre_kses ($string, $allowed_html, $allowed_protocols)
    * wp_kses_uri_attributes ($uri_attributes)
    * safe_style_css ($styleRules = [])
 

### Frontend Filter Hooks
* contentpress/body-class ($classes = [])
* contentpress/post-class ($classes = [])
* contentpress/social-icons ($icons, $userID)

### Frontend Auth Filters
* contentpress/after-login/redirect-path (\App\Models\User $user)
    Use this filter to override the default redirect path after user login
