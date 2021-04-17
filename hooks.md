# Hooks  (must be reviewed)

This document lists all hooks registered by the application, and the order they're executed.

### Frontend

* valpress/plugins/loaded
* valpress/theme/loaded ($themeName)
* valpress/app/loaded
* [since v0.12] valpress/frontend/init (Must be manually added to the layout file before the <!doctype html>
  declaration)

### Backend

* valpress/plugins/loaded
* valpress/theme/loaded
* valpress/admin/init
* valpress/app/loaded
* [since v0.12] valpress/backend/init

### Backend Action Hooks

* valpress/admin/head
* valpress/admin/footer
* valpress/post/deleted ($postID)
* valpress/post/new (App\Models\Post $post)
* valpress/post/actions ($postID)
* valpress/comment/status_changed (PostComments $comment, $oldStatusID)
* valpress/enqueue_text_editor () -> cp_enqueue_text_editor_scripts()
* valpress/post_editor_content ($postContent = '')
* valpress/post_editor_content/before
* valpress/post_editor_content/after
* valpress/plugin/activate ($pluginDirName)
* valpress/plugin/activated ($pluginDirName, $pluginInfo)
* valpress/plugin/deactivate ($pluginDirName)
* valpress/plugin/deactivated ($pluginDirName, $pluginInfo)
* valpress/plugin/delete ($pluginDirName)
* valpress/plugin/deleted ($pluginDirName)
* valpress/admin/sidebar/menu
* valpress/admin/sidebar/menu/dashboard
* valpress/admin/sidebar/menu/posts/{$postType}
* valpress/admin/sidebar/menu/menus
* valpress/admin/sidebar/menu/media
* valpress/admin/sidebar/menu/plugins
* valpress/admin/sidebar/menu/themes
* valpress/admin/sidebar/menu/users
* valpress/admin/sidebar/menu/settings

### Frontend Action Hooks

* valpress/site/head
* valpress/site/footer
* valpress/submit_comment
* valpress/submit_comment
* valpress/submit_comment
* valpress/submit_comment
* valpress/post/footer
* valpress/menu::{menu-slug}/before
* valpress/menu::{menu-slug}/after
* valpress/menu::{menu-slug}
* valpress/comment/render (PostComments $comment, $withReplies = true)
* valpress/comment/replies (PostComments $comment)
* valpress/comment/actions (PostComments $comment, $postID)
* valpress/after_body_open (Must be manually added to the layout file after the <body> tag)

### Backend Filter Hooks

* ValPress
    * vp::image-sizes ($imageSizes = [])
    * valpress/the_post_editor_content ($postContent = '')
    * valpress/register_view_paths ($viewPaths = [])
    * valpress/share-buttons ($buttons)
    * valpress/post/excerpt ($postExcerpt)
    * valpress/admin/right_sidebar/show ($visibility = false) == verify necessity
    * valpress/share-buttons/list ($list = [])
    * valpress/texteditor/editor-styles ($stylesheetLinks = [])
    * valpress/widget/title ($title)

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

* valpress/body-class ($classes = [])
* valpress/post-class ($classes = [])

### Globals
* [since v1.0.1] Action: valpress/heartbeat/success, param: User $user
* [since v1.0.1] Action: valpress/heartbeat/error

### Frontend Auth Filters

* valpress/after-login/redirect-path (\App\Models\User $user)
  Use this filter to override the default redirect path after user login

### Keep in mind

----

**valpress/app/loaded**
   * The **auth()->user()** is not yet available even if logged in. 
   * Use the new **valpress/frontend/init** or **valpress/backend/init** actions instead if you need to access the current user's info.
