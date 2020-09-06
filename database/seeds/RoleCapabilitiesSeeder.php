<?php

use App\Capability;
use App\Role;
use Illuminate\Database\Seeder;

class RoleCapabilitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superAdminRole = Role::where( 'name', Role::ROLE_SUPER_ADMIN )->first();
        $adminRole = Role::where( 'name', Role::ROLE_ADMIN )->first();
        $contributorRole = Role::where( 'name', Role::ROLE_CONTRIBUTOR )->first();
        $memberRole = Role::where( 'name', Role::ROLE_MEMBER )->first();

        //****
        //#! >> All roles must have the read capability
        //****
        $readCapID = Capability::where( 'name', 'read' )->first()->id;

        $superAdminRole->capabilities()->attach( [ $readCapID ] );
        $adminRole->capabilities()->attach( [ $readCapID ] );
        $contributorRole->capabilities()->attach( [ $readCapID ] );
        $memberRole->capabilities()->attach( [ $readCapID ] );

        //****
        //#! >> Shared capabilities between role Contributor and roles: Super Admin & Administrator
        //****
        $sharedCapabilities = [
            Capability::where( 'name', 'view_posts' )->first()->id,
            Capability::where( 'name', 'delete_others_posts' )->first()->id,
            Capability::where( 'name', 'delete_posts' )->first()->id,
            Capability::where( 'name', 'delete_private_posts' )->first()->id,
            Capability::where( 'name', 'delete_published_posts' )->first()->id,
            Capability::where( 'name', 'edit_posts' )->first()->id,
            Capability::where( 'name', 'edit_others_posts' )->first()->id,
            Capability::where( 'name', 'edit_private_posts' )->first()->id,
            Capability::where( 'name', 'edit_published_posts' )->first()->id,
            Capability::where( 'name', 'manage_taxonomies' )->first()->id,
            Capability::where( 'name', 'moderate_comments' )->first()->id,
            Capability::where( 'name', 'publish_posts' )->first()->id,
            Capability::where( 'name', 'read_private_posts' )->first()->id,
            Capability::where( 'name', 'unfiltered_html' )->first()->id,
            Capability::where( 'name', 'upload_files' )->first()->id,
            Capability::where( 'name', 'manage_custom_fields' )->first()->id,
            Capability::where( 'name', 'manage_translations' )->first()->id,
        ];

        $superAdminRole->capabilities()->attach( $sharedCapabilities );
        $adminRole->capabilities()->attach( $sharedCapabilities );
        $contributorRole->capabilities()->attach( $sharedCapabilities );

        //****
        //#! >> Super Admin & Administrators only
        //****
        $sharedCaps = [
            Capability::where( 'name', 'edit_dashboard' )->first()->id,
            Capability::where( 'name', 'list_users' )->first()->id,
            Capability::where( 'name', 'promote_users' )->first()->id,
            Capability::where( 'name', 'edit_users' )->first()->id,
            Capability::where( 'name', 'create_users' )->first()->id,
            Capability::where( 'name', 'delete_users' )->first()->id,
            Capability::where( 'name', 'block_users' )->first()->id,
            Capability::where( 'name', 'manage_options' )->first()->id,
            Capability::where( 'name', 'export' )->first()->id,
            Capability::where( 'name', 'import' )->first()->id,
            Capability::where( 'name', 'update_core' )->first()->id,
            Capability::where( 'name', 'manage_capabilities' )->first()->id,

            Capability::where( 'name', 'list_plugins' )->first()->id,
            Capability::where( 'name', 'install_plugins' )->first()->id,
            Capability::where( 'name', 'activate_plugins' )->first()->id,
            Capability::where( 'name', 'deactivate_plugins' )->first()->id,
            Capability::where( 'name', 'update_plugins' )->first()->id,
            Capability::where( 'name', 'delete_plugins' )->first()->id,

            Capability::where( 'name', 'list_themes' )->first()->id,
            Capability::where( 'name', 'install_themes' )->first()->id,
            Capability::where( 'name', 'switch_themes' )->first()->id,
            Capability::where( 'name', 'update_themes' )->first()->id,
            Capability::where( 'name', 'delete_themes' )->first()->id,

            Capability::where( 'name', 'manage_menus' )->first()->id,
            Capability::where( 'name', 'create_menu' )->first()->id,
            Capability::where( 'name', 'update_menu' )->first()->id,
            Capability::where( 'name', 'delete_menu' )->first()->id,

            Capability::where( 'name', 'list_media' )->first()->id,
            Capability::where( 'name', 'add_media' )->first()->id,
            Capability::where( 'name', 'update_media' )->first()->id,
            Capability::where( 'name', 'delete_media' )->first()->id,
        ];

        $superAdminRole->capabilities()->attach( $sharedCaps );
        $adminRole->capabilities()->attach( $sharedCaps );
    }
}
