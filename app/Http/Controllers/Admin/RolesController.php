<?php

namespace App\Http\Controllers\Admin;

use App\Models\Capability;
use App\Models\Role;

class RolesController extends AdminControllerBase
{
    //<editor-fold desc=":: ROLES ::">
    public function showRolesPage()
    {
        return view( 'admin.roles.roles' )->with( [
            'roles' => Role::all(),
        ] );
    }

    public function showRoleEditPage( $id )
    {
        return view( 'admin.roles.role_edit' )->with( [
            'role' => Role::findOrFail( $id ),
        ] );
    }

    public function showRoleCreatePage()
    {
        return view( 'admin.roles.role_add' );
    }

    public function updateRole( $id )
    {
        $role = Role::findOrFail( $id );
        $this->request->validate( [
            'name' => 'required|max:190',
            'display_name' => 'required|max:190',
            'description' => 'max:500',
        ] );

        $roleName = vp_filter_role_name( $this->request->get( 'name' ) );
        $roleDisplayName = wp_kses( $this->request->get( 'display_name' ), [] );
        $roleDescription = wp_kses_post( $this->request->get( 'description' ) );

        //#! If the role is protected and the name is changed
        if ( vp_is_role_protected( $role->name ) ) {
            if ( $roleName != $role->name ) {
                return redirect()->back()->withInput()->with( 'message', [
                    'class' => 'danger',
                    'text' => __( 'a.This role is protected, you cannot change its name.' ),
                ] );
            }
        }

        //#! Make sure the name is unique
        $r = Role::where( 'name', $roleName )->where( 'id', '!=', $role->id )->first();
        if ( $r && $r->id ) {
            return redirect()->back()->withInput()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.Another role with the same name already exists.' ),
            ] );
        }

        $role->name = $roleName;
        $role->display_name = $roleDisplayName;
        $role->description = ( empty( $roleDescription ) ? null : $roleDescription );
        $success = $role->update();

        if ( $success ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Role updated.' ),
            ] );
        }
        return redirect()->back()->withInput()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.An error occurred.' ),
        ] );
    }

    public function createRole()
    {
        $this->request->validate( [
            'name' => 'required|max:190',
            'display_name' => 'required|max:190',
            'description' => 'max:500',
        ] );

        $roleName = vp_filter_role_name( $this->request->get( 'name' ) );
        $roleDisplayName = wp_kses( $this->request->get( 'display_name' ), [] );
        $roleDescription = wp_kses_post( $this->request->get( 'description' ) );

        //#! Make sure the name is unique
        $r = Role::where( 'name', $roleName )->first();
        if ( $r && $r->id ) {
            return redirect()->back()->withInput()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.Another role with the same name already exists.' ),
            ] );
        }

        $success = Role::create( [
            'name' => $roleName,
            'display_name' => $roleDisplayName,
            'description' => ( empty( $roleDescription ) ? null : $roleDescription ),
        ] );

        if ( $success ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Role created. You should now assign the capabilities.' ),
            ] );
        }
        return redirect()->back()->withInput()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.An error occurred.' ),
        ] );
    }

    public function deleteRole( $id )
    {
        $role = Role::findOrFail( $id );

        if ( vp_is_role_protected( $role->name ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.This role is protected and cannot be deleted.' ),
            ] );
        }

        if ( $role->delete() ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Role deleted.' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.An error occurred.' ),
        ] );
    }
    //</editor-fold desc=":: ROLES ::">

    //<editor-fold desc=":: CAPABILITIES ::">
    public function showCapabilitiesPage()
    {
        $roles = Role::all();
        return view( 'admin.roles.capabilities' )->with( [
            'roles' => $roles,
            'capabilities' => Capability::all(),
        ] );
    }

    public function updateRoleCapabilities()
    {
        $postData = $this->request->get( 'capabilities' );

        foreach ( $postData as $roleID => $capabilities ) {
            $role = Role::findOrFail( $roleID );
            //#! Ignore malicious attempts to update the super admin's capabilities
            if ( 'super_admin' == $role->name ) {
                continue;
            }
            $capIds = array_keys( $capabilities );
            $role->capabilities()->detach();
            $role->capabilities()->attach( $capIds );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'a.Capabilities updated.' ),
        ] );
    }
    //</editor-fold desc=":: CAPABILITIES ::">
}
