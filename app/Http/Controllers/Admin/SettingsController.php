<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\VPML;
use App\Helpers\ScriptsManager;
use App\Helpers\Theme;
use App\Helpers\Util;
use App\Models\CommentStatuses;
use App\Models\Language;
use App\Models\Options;
use App\Models\Post;
use App\Models\PostStatus;
use App\Models\PostType;
use App\Models\Role;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SettingsController extends AdminControllerBase
{
    /**
     * General Settings
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::localizeScript( 'settings-index-locale', 'SettingsPageLocale', [
            'confirm_post_type_delete' => __( 'a.Are you sure you want to delete this post type? Anything associated with this post type will also be deleted' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'settings-index.js', asset( '_admin/js/settings/index.js' ) );

        return view( 'admin.settings.index' )->with( [
            'enabled_languages' => $this->options->getOption( 'enabled_languages', [] ),
            'roles' => Role::all(),
            'post_statuses' => PostStatus::all(),
            'comment_statuses' => CommentStatuses::all(),
            'default_language_code' => $this->settings->getSetting( 'default_language' ),
            'default_post_status' => $this->settings->getSetting( 'default_post_status' ),
            'default_user_role' => $this->settings->getSetting( 'default_user_role' ),
            'default_comment_status' => $this->settings->getSetting( 'default_comment_status' ),
            'user_registration_open' => $this->settings->getSetting( 'user_registration_open' ),
            'registration_verify_email' => $this->settings->getSetting( 'registration_verify_email' ),
            'allow_user_reset_password' => $this->settings->getSetting( 'allow_user_reset_password' ),
            'anyone_can_comment' => $this->settings->getSetting( 'anyone_can_comment' ),
            'site_title' => $this->settings->getSetting( 'site_title' ),
            'site_description' => $this->settings->getSetting( 'site_description' ),
            'date_format' => $this->settings->getSetting( 'date_format' ),
            'time_format' => $this->settings->getSetting( 'time_format' ),
            'blog_title' => $this->settings->getSetting( 'blog_title' ),
            'use_internal_cache' => $this->settings->getSetting( 'use_internal_cache' ),
            'is_under_maintenance' => $this->settings->getSetting( 'is_under_maintenance' ),
            'under_maintenance_page_title' => $this->settings->getSetting( 'under_maintenance_page_title' ),
            'under_maintenance_message' => $this->settings->getSetting( 'under_maintenance_message' ),
            'settings' => $this->settings,
        ] );
    }

    public function languages()
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::enqueueFooterScript( 'settings-languages.js', asset( '_admin/js/settings/languages.js' ) );

        return view( 'admin.settings.languages' )->with( [
            'languages' => Language::all(),
            'default_language_code' => $this->settings->getSetting( 'default_language' ),
            'enabled_languages' => $this->options->getOption( 'enabled_languages', [] ),
            'backend_user_current_language' => vp_get_user_meta( 'backend_user_current_language' ),
        ] );
    }

    public function reading()
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return $this->_forbidden();
        }

        $postTypePage = PostType::where( 'name', 'page' )->first();
        $postStatus = PostStatus::where( 'name', 'publish' )->first();

        return view( 'admin.settings.reading' )->with( [
            'posts_per_page' => $this->settings->getSetting( 'posts_per_page' ),
            'comments_per_page' => $this->settings->getSetting( 'comments_per_page' ),
            'show_on_front' => $this->settings->getSetting( 'show_on_front' ),
            'page_on_front' => $this->settings->getSetting( 'page_on_front' ),
            'blog_page' => $this->settings->getSetting( 'blog_page' ),
            'pages' => ( $postTypePage ?
                Post::where( 'post_type_id', $postTypePage->id )
                    ->where( 'post_status_id', $postStatus ? $postStatus->id : 0 )
                    ->where( 'language_id', VPML::getDefaultLanguageID() )
                    ->get() :
                []
            ),
        ] );
    }

    public function post_types()
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::localizeScript( 'settings-edit-locale', 'SettingsPageLocale', [
            'confirm_post_type_delete' => __( 'a.Are you sure you want to delete this post type? Anything associated with this post type will also be deleted' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'settings-edit.js', asset( '_admin/js/settings/edit.js' ) );

        return view( 'admin.settings.post_types' )->with( [
            'post_types' => PostType::where( 'translated_id', null )->where( 'language_id', VPML::getDefaultLanguageID() )->get(),

            'enabled_languages' => ( new Options() )->getOption( 'enabled_languages', [] ),
            'default_language_code' => $this->settings->getSetting( 'default_language' ),
        ] );
    }

    public function showPostTypeEditPage( $id )
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::localizeScript( 'settings-edit-locale', 'SettingsPageLocale', [
            'confirm_post_type_delete' => __( 'a.Are you sure you want to delete this post type? Anything associated with this post type will also be deleted' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'settings-edit.js', asset( '_admin/js/settings/edit.js' ) );

        return view( 'admin.settings.post_type_edit' )->with( [
            'entry' => PostType::find( $id ),
            'enabled_languages' => ( new Options() )->getOption( 'enabled_languages', [] ),
            'default_language_code' => $this->settings->getSetting( 'default_language' ),
        ] );
    }

    // Update the post type for the default language
    // To update any of this post's translations use self::__translate()
    public function __updatePostTypeDefault( $id )
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        if ( !( $post = PostType::find( $id ) ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.The post type was not found' ),
            ] );
        }
        $request = $this->request;

        $request->validate( [
            'name' => 'required',
            'display_name' => 'required',
            'plural_name' => 'required',
        ] );

        $name = $request->get( 'name' );

        //#! Make sure the name doesn't contain spaces or any other characters. One word only or at least separated by underscores
        if ( !preg_match( "/^[a-zA-Z0-9_]+$/", $name ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The post type name is not valid. Only underscores allowed to separate names.' ),
            ] );
        }

        //#! If name changed
        if ( $post->name != $name ) {
            //#! Update menu post type
            Util::vp_update_menu_item_post_type( $post->name, $name );
        }

        $displayName = $request->get( 'display_name' );
        $pluralName = $request->get( 'plural_name' );

        $post->name = $name;
        $post->display_name = $displayName;
        $post->plural_name = $pluralName;
        $post->language_id = VPML::getDefaultLanguageID();
        $post->translated_id = null;
        $r = $post->update();

        //#! Update options
        $optionNames = [
            //#! request var -> option name
            'allow_categories' => "post_type_{$name}_allow_categories",
            'allow_comments' => "post_type_{$name}_allow_comments",
            'allow_tags' => "post_type_{$name}_allow_tags",
        ];
        foreach ( $optionNames as $requestVar => $optionName ) {
            $opt = $this->options->where( 'name', $optionName )->first();
            if ( $opt && $opt->id ) {
                $opt->value = ( $request->has( $requestVar ) ? '1' : '0' );
                $opt->update();
            }
            else {
                Options::create( [
                    'name' => $optionName,
                    'value' => ( $request->has( $requestVar ) ? '1' : '0' ),
                ] );
            }
        }

        if ( $r ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Post type updated' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.Post type not updated' ),
        ] );
    }

    public function __insertPostType( Request $request )
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        $request->validate( [
            'name' => 'required|unique:post_types',
            'display_name' => 'required',
            'plural_name' => 'required',
            'language_id' => 'required',
        ] );

        $name = $request->get( 'name' );
        $displayName = $request->get( 'display_name' );
        $pluralName = $request->get( 'plural_name' );
        $languageID = $request->get( 'language_id' );

        if ( !Language::find( $languageID ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The specified language is not valid.' ),
            ] );
        }

        $name = Str::lower( $name );

        //#! Prevent adding a custom post type called "custom" since this is an internally reserved name
        if ( $name == 'custom' ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.Sorry, the "custom" name is reserved and it cannot be used as a post type name.' ),
            ] );
        }

        //#! Make sure the name doesn't contain spaces or any other characters. One word only or at least separated by underscores
        if ( !preg_match( "/^[a-zA-Z0-9_]+$/", $name ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The post type name is not valid. Only underscores allowed to separate names.' ),
            ] );
        }

        $postType = PostType::where( 'name', $name )->first();
        if ( $postType ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.A post type with the same name or display name already exists' ),
            ] );
        }

        $r = PostType::create( [
            'name' => $name,
            'display_name' => $displayName,
            'plural_name' => $pluralName,
            'language_id' => $languageID,
        ] );

        if ( VPML::getDefaultLanguageID() == $languageID ) {
            Util::vp_insert_menu_item_post_type( $name );
        }

        if ( $r ) {
            //#! Update options
            $optionNames = [
                //#! request var -> option name
                'allow_categories' => "post_type_{$name}_allow_categories",
                'allow_comments' => "post_type_{$name}_allow_comments",
                'allow_tags' => "post_type_{$name}_allow_tags",
            ];
            foreach ( $optionNames as $requestVar => $optionName ) {
                $opt = $this->options->where( 'name', $optionName )->first();
                if ( $opt ) {
                    $opt->value = ( $request->has( $requestVar ) ? '1' : '0' );
                    $opt->update();
                }
                else {
                    Options::create( [
                        'name' => $optionName,
                        'value' => ( $request->has( $requestVar ) ? '1' : '0' ),
                    ] );
                }
            }

            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Post type added' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.Post type not added' ),
        ] );
    }

    public function __deletePostType( $id )
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        if ( empty( $id ) || !( $pt = PostType::find( $id ) ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The specified post type was not found' ),
            ] );
        }

        //#! Do not delete the default post type - this is a system protected post type
        if ( $pt->name == 'post' ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.Sorry, this post type cannot be deleted.' ),
            ] );
        }

        $name = $pt->name;

        $result = PostType::destroy( $id );
        if ( $result ) {
            //#! Delete associated menu post type
            Util::vp_delete_menu_item_post_type( $name );

            //#! Delete translations
            PostType::where( 'translated_id', $id )->delete();

            return redirect()->route( 'admin.settings.post_types' )->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.Post type deleted' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.The specified post type could not be deleted' ),
        ] );
    }

    /**
     * @param int $post_id The ID of the post type being translated
     * @param int $language_id The language id
     * @param int $new_post_id The ID of the translated post. If omitted, then it will be created
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __translate( $post_id, $language_id, $new_post_id = 0 )
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $request = $this->request;

        $request->validate( [
            'name' => 'required',
            'display_name' => 'required',
            'plural_name' => 'required',
        ] );
        $name = $request->get( 'name' );
        $displayName = $request->get( 'display_name' );
        $pluralName = $request->get( 'plural_name' );

        if ( !PostType::find( $post_id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.The post type is not valid' ),
            ] );
        }
        if ( !Language::find( $language_id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.The language is not valid' ),
            ] );
        }

        //#! Make sure the name doesn't contain spaces or any other characters. One word only or at least separated by underscores
        if ( !preg_match( "/^[a-zA-Z0-9_]+$/", $name ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The post type name is not valid. Only underscores allowed to separate names.' ),
            ] );
        }

        //#! Create translation
        if ( empty( $new_post_id ) ) {
            $r = PostType::create( [
                'name' => $name,
                'display_name' => $displayName,
                'plural_name' => $pluralName,
                'language_id' => $language_id,
                'translated_id' => $post_id,
            ] );
        }
        //#! Update translation
        else {
            $post = PostType::find( $new_post_id );
            if ( !$post ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger', // success or danger on error
                    'text' => __( 'a.The post type was not found' ),
                ] );
            }

            $post->name = $name;
            $post->display_name = $displayName;
            $post->plural_name = $pluralName;
            $post->language_id = $language_id;
            $post->translated_id = $post_id;
            $r = $post->update();
        }

        if ( $r ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Post type updated' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.Post type not updated' ),
        ] );
    }

    public function __updateSettings()
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $this->request->validate( [
            //#! Only if multilanguage
//            'default_language' => 'required',
            'default_post_status' => 'required',
            'default_user_role' => 'required',
            'default_comment_status' => 'required',
//            'anyone_can_comment' => '', since it's a checkbox it will not always be present in the request, so it cannot be here as required
//            'user_registration_open' => 'required', since it's a checkbox it will not always be present in the request, so it cannot be here as required
            //#! Whether or not to require email verification after registration
//            'registration_verify_email' => 'required',
//            'allow_user_reset_password' => 'required',

//#! This can be empty, so no need to require
            'site_title' => '',
//#! This can be empty, so no need to require
            'site_description' => '',
            'date_format' => 'required',
            'time_format' => 'required',
//#! This can be empty, so no need to require
            'blog_title' => '',
        ] );

        if ( $this->request->default_language ) {
            $this->settings->updateSetting( 'default_language', $this->request->default_language );
        }

        $this->settings->updateSetting( 'default_post_status', wp_kses( $this->request->default_post_status, [] ) );
        $this->settings->updateSetting( 'default_comment_status', wp_kses( $this->request->default_comment_status, [] ) );
        $this->settings->updateSetting( 'default_user_role', intval( $this->request->default_user_role ) );
        $this->settings->updateSetting( 'user_registration_open', $this->request->user_registration_open ? true : false );
        $this->settings->updateSetting( 'registration_verify_email', $this->request->registration_verify_email ? true : false );
        $this->settings->updateSetting( 'allow_user_reset_password', $this->request->allow_user_reset_password ? true : false );
        $this->settings->updateSetting( 'anyone_can_comment', $this->request->anyone_can_comment ? true : false );
        $this->settings->updateSetting( 'site_title', wp_kses( $this->request->site_title, [] ) );
        $this->settings->updateSetting( 'site_description', wp_kses( $this->request->site_description, [] ) );
        $this->settings->updateSetting( 'date_format', wp_kses( $this->request->date_format, [] ) );
        $this->settings->updateSetting( 'time_format', wp_kses( $this->request->time_format, [] ) );
        $this->settings->updateSetting( 'blog_title', wp_kses( $this->request->blog_title, [] ) );
        $this->settings->updateSetting( 'use_internal_cache', $this->request->use_internal_cache ? true : false );

        //#! Under maintenance settings
        $this->settings->updateSetting( 'is_under_maintenance', $this->request->is_under_maintenance ? true : false );
        if ( $this->request->get( 'under_maintenance_page_title' ) ) {
            $this->settings->updateSetting( 'under_maintenance_page_title', wp_strip_all_tags( $this->request->under_maintenance_page_title ) );
        }
        if ( $this->request->get( 'under_maintenance_message' ) ) {
            $this->settings->updateSetting( 'under_maintenance_message', wp_strip_all_tags( $this->request->under_maintenance_message ) );
        }

        do_action( 'valpress/admin/general-settings/save', $this->settings, $this->request );

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.Settings updated' ),
        ] );
    }

    public function __updateLanguages()
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $selectedLanguages = $this->request->selected_languages;
        if ( empty( $selectedLanguages ) ) {
            $selectedLanguages = [];
            $enabledLanguages = $this->options->getOption( 'enabled_languages', [] );
            if ( empty( $enabledLanguages ) ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger',
                    'text' => __( 'a.Please specify at least one language' ),
                ] );
            }
        }

        $defaultLanguageCode = $this->settings->getSetting( 'default_language' );

        //#! Add the default language
        //#! Since the input is disabled in the frontend, its value won't be submitted
        if ( !in_array( $defaultLanguageCode, $selectedLanguages ) ) {
            array_push( $selectedLanguages, $defaultLanguageCode );
        }

        //#! Save
        Options::where( 'name', 'enabled_languages' )->update( [ 'value' => serialize( array_values( $selectedLanguages ) ) ] );

        //#! Remove the session var so we can copy the lang directory if it doesn't exist
        session()->remove( 'system_language_dirs_check' );

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.Settings updated' ),
        ] );
    }

    //#! POST
    public function __addLanguage()
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $this->request->validate( [
            'language_code' => 'required|max:2',
            'language_name' => 'required|max:50',
        ] );

        $code = strtolower( wp_kses( $this->request->get( 'language_code' ), [] ) );
        $name = ucfirst( wp_kses( $this->request->get( 'language_name' ), [] ) );

        $entry = Language::where( 'code', $code )->first();

        //#! Update
        if ( $entry ) {
            $entry->name = $name;
            $updated = $entry->update();
            if ( !$updated ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger',
                    'text' => __( 'a.The language could not be updated.' ),
                ] );
            }
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.The language has been updated.' ),
            ] );
        }
        //#! Insert
        $created = Language::create( [
            'code' => $code,
            'name' => $name,
        ] );
        if ( !$created ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The language could not be added.' ),
            ] );
        }

        //#! Create the language directory if it doesn't exist and copy the files from the default language's directory
        $defaultLanguage = $this->settings->getSetting( 'default_language', 'en' );
//        $sourceLangDir = resource_path( "lang/{$defaultLanguage}" );
//        try {
//            if ( File::isDirectory( $sourceLangDir ) ) {
//                $destLangDir = resource_path( "lang/{$code}" );
//                File::copyDirectory( $sourceLangDir, $destLangDir );
//            }
//        }
//        catch ( \Exception $e ) {
//            return redirect()->back()->with( 'message', [
//                'class' => 'danger',
//                'text' => __( 'a.The language has been added but the language directory could not be created.' ),
//            ] );
//        }

        /**
         * @var Theme $crtTheme
         */
        $crtTheme = app()->get( 'cp.theme' );

        //#! [::1] [APP] Copy the default language dir to the new one
        $result = $this->__appCopyLanguageDir( $defaultLanguage, $code );
        if ( $result === true ) {
            //#! [::2] [THEME] Copy the default language dir to the new one
            $result = $this->__themeCopyLanguageDir( $crtTheme, $defaultLanguage, $code );
            if ( $result === true ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'success',
                    'text' => __( 'a.The language has been added.' ),
                ] );
            }

            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => $result,
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => $result,
        ] );
    }

    public function __deleteLanguage( $id )
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $entry = Language::findOrFail( $id );
        $languageCode = $entry->code;

        if ( $this->settings->getSetting( 'default_language' ) == $languageCode ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You cannot delete the language set as default.' ),
            ] );
        }

        $deleted = $entry->delete();
        if ( !$deleted ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The language could not be deleted.' ),
            ] );
        }

        //#! Remove from option
        $updateOption = false;
        $enabledLanguages = $this->options->getOption( 'enabled_languages', [] );
        foreach ( $enabledLanguages as $i => $code ) {
            if ( $code == $languageCode ) {
                unset( $enabledLanguages[ $i ] );
                $updateOption = true;
                break;
            }
        }
        if ( $updateOption ) {
            $this->options->addOption( 'enabled_languages', $enabledLanguages );
        }


        //#! [::1] Attempt to delete the language directory from [root]/resources/lang
        $result = $this->__appDeleteLanguageDir( $languageCode );
        if ( $result === true ) {
            //#! [::2] Attempt to delete the language directory from [current theme]/lang
            $result = $this->__themeDeleteLanguageDir( $languageCode );
            if ( $result !== true ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'warning',
//                'text' => __( "a.The language has been deleted but an error occurred and the language directory couldn't be deleted." ),
                    'text' => $result,
                ] );
            }
        }
        else {
            return redirect()->back()->with( 'message', [
                'class' => 'warning',
//                'text' => __( "a.The language has been deleted but an error occurred and the language directory couldn't be deleted." ),
                'text' => $result,
            ] );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.The language has been deleted.' ),
        ] );
    }

    public function __updateReadingSettings()
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $this->request->validate( [
            'posts_per_page' => 'required',
            'comments_per_page' => 'required',
            'show_on_front' => 'required',
            'blog_page' => 'required',
//            'page_on_front' => 'must be present if show_on_front option is set to page'
        ] );
        Settings::where( 'name', 'posts_per_page' )->update( [ 'value' => $this->request->posts_per_page ] );
        Settings::where( 'name', 'comments_per_page' )->update( [ 'value' => $this->request->comments_per_page ] );

        if ( $this->request->show_on_front == 'page' ) {
            $pageID = $this->request->page_on_front;
            $page = Post::find( $pageID );
            if ( !$page ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger',
                    'text' => __( 'a.The specified page was not found' ),
                ] );
            }
            $s = Settings::where( 'name', 'page_on_front' )->first();
            if ( $s ) {
                $s->value = $pageID;
                $s->update();
            }
            else {
                Settings::create( [
                    'name' => 'page_on_front',
                    'value' => $pageID,
                ] );
            }
        }

        $blogPageID = $this->request->blog_page;
        if ( !empty( $blogPageID ) ) {
            $page = Post::find( $blogPageID );
            if ( !$page ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger',
                    'text' => __( 'a.The page specified as blog page was not found.' ),
                ] );
            }
        }
        $s = Settings::where( 'name', 'blog_page' )->first();
        if ( $s ) {
            $s->value = $blogPageID;
            $s->update();
        }
        else {
            Settings::create( [
                'name' => 'blog_page',
                'value' => $blogPageID,
            ] );
        }
        // Update this last, first make sure we have a valid page
        Settings::where( 'name', 'show_on_front' )->update( [ 'value' => $this->request->show_on_front ] );

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.Settings updated' ),
        ] );
    }

    public function __clearCache()
    {
        if ( !vp_current_user_can( 'manage_options' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }
        if ( $this->settings->getSetting( 'use_internal_cache' ) ) {
            $this->cache->clear();
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Cache cleared.' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.An error occurred.' ),
        ] );
    }

    private function __appCopyLanguageDir( $defaultLanguageCode, $newLanguageCode )
    {
        $sourceLangDir = resource_path( "lang/{$defaultLanguageCode}" );
        try {
            if ( File::isDirectory( $sourceLangDir ) ) {
                $destLangDir = resource_path( "lang/{$newLanguageCode}" );
                File::copyDirectory( $sourceLangDir, $destLangDir );
            }
        }
        catch ( \Exception $e ) {
            return $e->getMessage();
        }
        return true;
    }

    private function __appDeleteLanguageDir( $languageCode )
    {
        try {
            $languageDir = resource_path( "lang/{$languageCode}" );
            if ( File::isDirectory( $languageDir ) ) {
                File::deleteDirectory( $languageDir, false );
            }
        }
        catch ( \Exception $e ) {
            return $e->getMessage();
        }
        return true;
    }

    /**
     * @param Theme $theme
     * @param $defaultLanguageCode
     * @param $newLanguageCode
     * @return bool|string
     */
    private function __themeCopyLanguageDir( Theme $theme, $defaultLanguageCode, $newLanguageCode )
    {
        $themeDirPath = untrailingslashit( $theme->getDirPath() );
        $sourceLangDir = "{$themeDirPath}/lang/{$defaultLanguageCode}";

        //#! If a parent theme
        if ( File::isDirectory( $sourceLangDir ) ) {
            try {
                $destLangDir = "{$themeDirPath}/lang/{$newLanguageCode}";
                File::copyDirectory( $sourceLangDir, $destLangDir );
            }
            catch ( \Exception $e ) {
                return $e->getMessage();
            }
        }
        //#! If this is a child theme
        elseif ( $theme->isChildTheme() ) {
            $parentTheme = $theme->getParentTheme();
            $parentThemeDirPath = untrailingslashit( $parentTheme->getDirPath() );
            $sourceLangDir = "{$parentThemeDirPath}/lang/{$defaultLanguageCode}";

            if ( File::isDirectory( $sourceLangDir ) ) {
                try {
                    $themeDirPath = untrailingslashit( $theme->getDirPath() );
                    $destLangDir = "{$themeDirPath}/lang/{$newLanguageCode}";
                    File::copyDirectory( $sourceLangDir, $destLangDir );
                }
                catch ( \Exception $e ) {
                    return $e->getMessage();
                }
            }
        }
        return true;
    }

    private function __themeDeleteLanguageDir( $languageCode )
    {
        $crtTheme = app()->get( 'cp.theme' );
        $themeDirPath = untrailingslashit( $crtTheme->getDirPath() );
        $sourceLangDir = "{$themeDirPath}/lang/{$languageCode}";

        //#! If found in the current theme
        if ( File::isDirectory( $sourceLangDir ) ) {
            try {
                if ( File::isDirectory( $sourceLangDir ) ) {
                    File::deleteDirectory( $sourceLangDir, false );
                }
            }
            catch ( \Exception $e ) {
                return $e->getMessage();
            }
        }
        return true;
    }
}
