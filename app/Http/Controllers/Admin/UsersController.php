<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CPML;
use App\Helpers\MetaFields;
use App\Helpers\ScriptsManager;
use App\Helpers\Util;
use App\Role;
use App\User;
use App\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class UsersController extends AdminControllerBase
{
    public function __construct( Request $request )
    {
        parent::__construct();

        $this->request = $request;
    }

    public function index()
    {
        if ( !cp_current_user_can( 'list_users' ) ) {
            return $this->_forbidden();
        }

        $admins = User::whereIn( 'role_id', [
            Role::where( 'name', Role::ROLE_SUPER_ADMIN )->first()->id,
            Role::where( 'name', Role::ROLE_ADMIN )->first()->id,
        ] )->get();
        $adminUsersID = Arr::pluck( $admins, 'id' );
        $users = User::latest()->whereNotIn( 'id', $adminUsersID )->paginate( 10 );

        return view( 'admin.users.index' )->with( [
            'admins' => $admins,
            'users' => $users,
            'current_user' => cp_get_current_user(),
        ] );
    }

    public function showCreatePage()
    {
        if ( !cp_current_user_can( 'create_users' ) ) {
            return $this->_forbidden();
        }

        return view( 'admin.users.create' )->with( [
            'users' => User::paginate( 15 ),
            'roles' => Role::all(),
            'default_role_id' => $this->settings->getSetting( 'default_user_role' ),
        ] );
    }

    public function showEditPage( $id )
    {
        //#! Only the admin can update other users
        if ( !cp_current_user_can( 'administrator' ) ) {
            //#! Users can only update their own profile
            if ( $this->current_user()->getAuthIdentifier() != $id ) {
                return $this->_forbidden();
            }
        }

        ScriptsManager::enqueueStylesheet( 'dropify.min.css', asset( 'vendor/dropify/css/dropify.min.css' ) );
        ScriptsManager::enqueueFooterScript( 'dropify.min.js', asset( 'vendor/dropify/js/dropify.min.js' ) );
        ScriptsManager::enqueueFooterScript( 'DropifyImageUploader.js', asset( '_admin/js/DropifyImageUploader.js' ) );

        ScriptsManager::localizeScript( 'users-scripts-locale', 'UsersPageLocale', [
            'user_id' => $id,
            'confirm_user_delete' => __( 'a.Are you sure you want to delete this user? All items associated with it will also be deleted.' ),
            'text_image_set' => __( 'a.Image set.' ),
            'text_image_removed' => __( 'a.Image removed.' ),
            'text_info_bio' => __( 'a.Biographical Info' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'users-scripts-edit.js', asset( '_admin/js/users/edit.js' ) );

        MetaFields::generateProtectedMetaFields( $this->userMeta, 'user_id', $id, MetaFields::SECTION_USER );

        $user = User::findOrFail( $id );

        return view( 'admin.users.edit' )->with( [
            //#! the user being edited
            'user' => $user,
            'roles' => Role::all(),
            'meta_fields' => MetaFields::getAll( $this->userMeta, 'user_id', $id, CPML::getDefaultLanguageID() ),
            'default_role_id' => $this->settings->getSetting( 'default_user_role' ),
        ] );
    }

    public function __insert()
    {
        if ( !cp_current_user_can( 'create_users' ) ) {
            return $this->_forbidden();
        }

        $this->validate( $this->request, [
            'name' => 'required|string',
            'display_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',

//#! Only available if current user can: promote_users
//            'role' => 'required|exists:roles,id',
        ] );

        $user = new User;
        $user->name = $this->request->name;
        $user->email = $this->request->email;
        $user->password = bcrypt( $this->request->password );
        if ( cp_current_user_can( 'promote_users' ) ) {
            $user->role_id = $this->request->role;
        }
        else {
            $user->role_id = Role::where( 'name', Role::ROLE_MEMBER )->first()->id;
        }
        $user->display_name = $this->request->display_name;
        $user->save();

        return redirect()->route( 'admin.users.all' )->with( 'message', [
            'class' => 'success', // success or danger on error
            'text' => __( 'a.User added.' ),
        ] );
    }

    public function __delete( $id )
    {
        if ( !cp_current_user_can( 'delete_users' ) ) {
            return $this->_forbidden();
        }

        $user = User::find( $id );

        if ( !$user ) {
            return redirect()->route( 'admin.users.all' )->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.User not found.' ),
            ] );
        }

        // If the user is super administrator, prevent this action
        if ( $user->is_super_admin ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.The super admin user cannot be deleted.' ),
            ] );
        }

        $r = $user->delete();

        //#! Delete user's files
        if ( $r ) {
            $dirPath = public_path( "uploads/users/{$id}" );
            if ( File::isDirectory( $dirPath ) ) {
                @File::deleteDirectory( $dirPath );
            }
        }

        return redirect()->route( 'admin.users.all' )->with( 'message', [
            'class' => 'success', // success or danger on error
            'text' => __( 'a.User deleted.' ),
        ] );
    }

    public function __block( $id )
    {
        if ( !cp_current_user_can( 'block_users' ) ) {
            return $this->_forbidden();
        }

        $user = User::find( $id );

        if ( !$user ) {
            return redirect()->route( 'admin.users.all' )->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.User not found.' ),
            ] );
        }

        $user->is_blocked = 1;
        $user->save();

        return redirect()->route( 'admin.users.all' )->with( 'message', [
            'class' => 'success', // success or danger on error
            'text' => __( 'a.User blocked.' ),
        ] );
    }

    public function __unblock( $id )
    {
        if ( !cp_current_user_can( 'block_users' ) ) {
            return $this->_forbidden();
        }

        $user = User::find( $id );

        if ( !$user ) {
            return redirect()->route( 'admin.users.all' )->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.User not found.' ),
            ] );
        }

        $user->is_blocked = 0;
        $user->save();

        return redirect()->route( 'admin.users.all' )->with( 'message', [
            'class' => 'success', // success or danger on error
            'text' => __( 'a.User unblocked.' ),
        ] );
    }

    public function __update()
    {
        $isOwnProfile = ( $this->current_user()->getAuthIdentifier() == $this->request->user_id );

        //#! Only the admin can update other users
        //#! Other users can only update their own profile
        if ( !cp_current_user_can( 'edit_users' ) && !$isOwnProfile ) {
            return $this->_forbidden();
        }

        $user = User::find( $this->request->user_id );

        //#! If the edited user is a super admin and the current user is not
        if ( $user->is_super_admin && !$isOwnProfile ) {
            return $this->_forbidden();
        }

        if ( !$user ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.User not found.' ),
            ] );
        }

        $this->validate( $this->request, [
            'name' => 'required|string',
            'display_name' => 'required|string',
            'email' => 'required|email',

//#! part of the request, but only present if the user's capabilities match
//            'role' => 'required|exists:roles,id',
//            'blocked' => 'required|in:0,1'
        ] );

        $user->name = $this->request->name;
        $user->display_name = $this->request->display_name;
        $user->email = $this->request->email;
        $user->password = bcrypt( $this->request->password );

        if ( cp_current_user_can( 'promote_users' ) ) {
            $user->role_id = $this->request->role;
        }

        if ( cp_current_user_can( 'block_users' ) ) {
            $user->is_blocked = $this->request->blocked;
        }
        $user->update();

        return redirect()->back()->with( 'message', [
            'class' => 'success', // success or danger on error
            'text' => __( 'a.User updated.' ),
        ] );
    }

    public function __updateProfile( $id )
    {
        $isOwnProfile = ( $this->current_user()->getAuthIdentifier() == $id );

        //#! Only the admin can update other users
        //#! Other users can only update their own profile
        if ( !cp_current_user_can( 'edit_users' ) && !$isOwnProfile ) {
            return $this->_forbidden();
        }

        $user = User::find( $id );

        if ( !$user ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.User not found.' ),
            ] );
        }

        //#! If the edited user is a super admin and the current user is not
        if ( $user->is_super_admin && !$isOwnProfile ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        //#! Website url
        $websiteUrl = $this->request->user_profile_website;
        if ( !empty( $websiteUrl ) && !filter_var( $websiteUrl, FILTER_VALIDATE_URL ) ) {
            return back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.The website url is not valid.' ),
            ] );
        }
        $meta = UserMeta::where( 'user_id', $user->id )
            ->where( 'language_id', CPML::getDefaultLanguageID() )
            ->where( 'meta_name', '_website_url' )
            ->first();
        if ( $meta ) {
            $meta->meta_value = $websiteUrl;
            $meta->update();
        }
        else {
            UserMeta::create( [
                'user_id' => $user->id,
                'language_id' => CPML::getDefaultLanguageID(),
                'meta_name' => '_website_url',
                'meta_value' => $websiteUrl,
            ] );
        }

        //#! User bio
        $userBio = $this->request->user_profile_bio;

        if ( !cp_current_user_can( 'unfiltered_html' ) ) {
            $userBio = strip_tags( $userBio );
        }

        $meta = UserMeta::where( 'user_id', $user->id )
            ->where( 'language_id', CPML::getDefaultLanguageID() )
            ->where( 'meta_name', '_user_bio' )
            ->first();
        if ( $meta ) {
            $meta->meta_value = $userBio;
            $meta->update();
        }
        else {
            UserMeta::create( [
                'user_id' => $user->id,
                'language_id' => CPML::getDefaultLanguageID(),
                'meta_name' => '_user_bio',
                'meta_value' => $userBio,
            ] );
        }

        return back()->with( 'message', [
            'class' => 'success', // success or danger on error
            'text' => __( 'a.Profile updated.' ),
        ] );
    }

}
