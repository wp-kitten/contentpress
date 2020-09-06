# User Capabilities (must be reviewed)

*All users must have the "read" capability which will grant them access to "Dashboard" and "Users > Your profile" pages*

## SuperAdmin & Administrator
* Dashboard
    * [root] read
    * edit_dashboard (if applicable)

* Posts/Pages/Custom Post types
    * [root] view_posts
    * delete_posts
    * delete_others_posts
    * delete_private_posts
    * delete_published_posts
    * edit_posts
    * edit_others_posts
    * edit_private_posts
    * edit_published_posts
    * publish_posts
    * read_private_posts
    * manage_taxonomies
    * moderate_comments
    
* Meta Fields
    * manage_custom_fields (add/edit/update/delete)

* Users
    * [root] list_users
    ```
    Allows access to Administration Screens options:
        Users
    ```
    * promote_users
    ```
    Enables the "Change role to…" dropdown in the admin user list.
    ```
    * edit_users
    * create_users
    * delete_users
    * block_users

* Plugins
    * [root] list_plugins
    * install_plugins
    * activate_plugins
    * update_plugins
    * delete_plugins

* Themes
    * [root] list_themes
    * install_themes
    * switch_themes
    * update_themes
    * delete_themes

* Settings
    * [root] manage_options (settings)
    * export
    * import

* Menu
    * [root] manage_menus (settings)
    * create_menu
    * update_menu
    * delete_menu

* Core
    * update_core
    * upload_files
    * edit_files (maybe later on through a plugin ?)
    * manage_capabilities (this feature will allow administrators to give other roles extra capabilities - maybe create dynamic roles?)
    * unfiltered_html
    ```
    Allows user to post HTML markup or even JavaScript code in pages, posts & comments.
    ```

* Translations
    * [root] manage_translations


## Contributor
* delete_others_posts
* delete_posts
* delete_private_posts
* delete_published_posts
* edit_posts
* edit_others_posts
* edit_private_posts
* edit_published_posts
* publish_posts
* read_private_posts
* manage_taxonomies
* manage_custom_fields (add/edit/update/delete)
* moderate_comments
* unfiltered_html
* upload_files
    ```
    Allows user to post HTML markup or even JavaScript code in pages, posts & comments.
    ```
* manage_translations


## Member
* read
```
Allows access to Administration Screens options:
Dashboard
Users > Your Profile
```
