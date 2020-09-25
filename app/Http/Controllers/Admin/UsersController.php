<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CPML;
use App\Helpers\MetaFields;
use App\Helpers\ScriptsManager;
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
        $authUser = $this->current_user();

        if ( !$authUser->can( 'list_users' ) ) {
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
            'current_user' => $authUser,
        ] );
    }

    public function showCreatePage()
    {
        if ( !$this->current_user()->can( 'create_users' ) ) {
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
        $currentUser = User::findOrFail( $id );
        $isUserSuperAdmin = $currentUser->isInRole( [ Role::ROLE_SUPER_ADMIN ] );

        $authUser = cp_get_current_user();
        $isOwnProfile = ( $currentUser->id == $authUser->getAuthIdentifier() );
        $isAuthUserSuperAdmin = $authUser->isInRole( [ Role::ROLE_SUPER_ADMIN ] );
        $isAuthUserAdmin = $authUser->isInRole( [ Role::ROLE_ADMIN ] );

        //#! If the edited user is super admin and the current user is not
        if ( !$isOwnProfile ) {
            if ( $isUserSuperAdmin && !$isAuthUserSuperAdmin ) {
                return $this->_forbidden();
            }
            //#! Only administrators can edit others' profiles
            elseif ( !$isAuthUserAdmin ) {
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
            'auth_user' => cp_get_current_user(),
        ] );
    }

    public function __insert()
    {
        $authUser = $this->current_user();

        if ( !$authUser->can( 'create_users' ) ) {
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
        if ( $authUser->can( 'promote_users' ) ) {
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
        if ( !$this->current_user()->can( 'delete_users' ) ) {
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
        if ( !$this->current_user()->can( 'block_users' ) ) {
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
        if ( !$this->current_user()->can( 'block_users' ) ) {
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
        $currentUser = User::findOrFail( $this->request->user_id );
        $isUserSuperAdmin = $currentUser->isInRole( [ Role::ROLE_SUPER_ADMIN ] );

        $authUser = $this->current_user();
        $isOwnProfile = ( $currentUser->id == $authUser->getAuthIdentifier() );
        $isAuthUserSuperAdmin = $authUser->isInRole( [ Role::ROLE_SUPER_ADMIN ] );
        $isAuthUserAdmin = $authUser->isInRole( [ Role::ROLE_ADMIN ] );

        //#! If the edited user is super admin and the current user is not
        if ( !$isOwnProfile ) {
            if ( $isUserSuperAdmin && !$isAuthUserSuperAdmin ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger', // success or danger on error
                    'text' => __( 'a.You are not allowed to perform this action.' ),
                ] );
            }
            //#! Only administrators can edit others' profiles
            elseif ( !$isAuthUserAdmin ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger', // success or danger on error
                    'text' => __( 'a.You are not allowed to perform this action.' ),
                ] );
            }
            elseif ( !$authUser->can( 'edit_users' ) ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger', // success or danger on error
                    'text' => __( 'a.You are not allowed to perform this action.' ),
                ] );
            }
        }

        $this->validate( $this->request, [
            'name' => 'required|string',
            'display_name' => 'required|string',
            'email' => 'required|email',

//#! part of the request, but only present if the user's capabilities match
//            'role' => 'required|exists:roles,id',
//            'blocked' => 'required|in:0,1'
        ] );

        $currentUser->name = $this->request->name;
        $currentUser->display_name = $this->request->display_name;
        $currentUser->email = $this->request->email;
        $currentUser->password = bcrypt( $this->request->password );

        if ( $authUser->can( 'promote_users' ) && $this->request->has( 'role' ) ) {
            //#! If the selected role is super admin then the current user must be super admin
            if ( $this->request->role == Role::where( 'name', Role::ROLE_SUPER_ADMIN )->first()->id ) {
                if ( !$isUserSuperAdmin ) {
                    return redirect()->back()->with( 'message', [
                        'class' => 'danger', // success or danger on error
                        'text' => __( 'a.You are not allowed to perform this action.' ),
                    ] );
                }
            }
            $currentUser->role_id = $this->request->role;
        }

        if ( $authUser->can( 'block_users' ) ) {
            $currentUser->is_blocked = $this->request->blocked;
        }
        $currentUser->update();

        return redirect()->back()->with( 'message', [
            'class' => 'success', // success or danger on error
            'text' => __( 'a.User updated.' ),
        ] );
    }

    public function __updateProfile( $id )
    {
        $currentUser = User::findOrFail( $id );
        $isUserSuperAdmin = $currentUser->isInRole( [ Role::ROLE_SUPER_ADMIN ] );

        $authUser = $this->current_user();
        $isOwnProfile = ( $id == $authUser->getAuthIdentifier() );
        $isAuthUserSuperAdmin = $authUser->isInRole( [ Role::ROLE_SUPER_ADMIN ] );
        $isAuthUserAdmin = $authUser->isInRole( [ Role::ROLE_ADMIN ] );

        if ( !$isOwnProfile ) {
            if ( $isUserSuperAdmin && !$isAuthUserSuperAdmin ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger', // success or danger on error
                    'text' => __( 'a.You are not allowed to perform this action.' ),
                ] );
            }
            //#! Only administrators can edit others' profiles
            elseif ( !$isAuthUserAdmin ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger', // success or danger on error
                    'text' => __( 'a.You are not allowed to perform this action.' ),
                ] );
            }
            elseif ( !$authUser->can( 'edit_users' ) ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger', // success or danger on error
                    'text' => __( 'a.You are not allowed to perform this action.' ),
                ] );
            }
        }

        //#! Website url
        $websiteUrl = $this->request->user_profile_website;
        if ( !empty( $websiteUrl ) && !filter_var( $websiteUrl, FILTER_VALIDATE_URL ) ) {
            return back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.The website url is not valid.' ),
            ] );
        }
        $meta = UserMeta::where( 'user_id', $currentUser->id )
            ->where( 'language_id', CPML::getDefaultLanguageID() )
            ->where( 'meta_name', '_website_url' )
            ->first();
        if ( $meta ) {
            $meta->meta_value = $websiteUrl;
            $meta->update();
        }
        else {
            UserMeta::create( [
                'user_id' => $currentUser->id,
                'language_id' => CPML::getDefaultLanguageID(),
                'meta_name' => '_website_url',
                'meta_value' => $websiteUrl,
            ] );
        }

        //#! User bio
        $userBio = $this->request->user_profile_bio;

        if ( !$authUser->can( 'unfiltered_html' ) ) {
            $userBio = strip_tags( $userBio );
        }

        $meta = UserMeta::where( 'user_id', $currentUser->id )
            ->where( 'language_id', CPML::getDefaultLanguageID() )
            ->where( 'meta_name', '_user_bio' )
            ->first();
        if ( $meta ) {
            $meta->meta_value = $userBio;
            $meta->update();
        }
        else {
            UserMeta::create( [
                'user_id' => $currentUser->id,
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
