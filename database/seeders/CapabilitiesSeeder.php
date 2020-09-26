<?php
namespace Database\Seeders;

use App\Models\Capability;
use Illuminate\Database\Seeder;

class CapabilitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //#! All users MUST have this capability
        Capability::create( [
            'name' => 'read',
            'description' => 'Allows access to Dashboard and Users > Your profile pages',
        ] );

        //[::1.1] Shared capabilities between role Contributor and roles: Super Admin & Administrator
        Capability::create( [ 'name' => 'view_posts', ] ); // Allows access to everything under this section - this is the root capability
        Capability::create( [ 'name' => 'delete_others_posts', ] );
        Capability::create( [ 'name' => 'delete_posts', ] );
        Capability::create( [ 'name' => 'delete_private_posts', ] );
        Capability::create( [ 'name' => 'delete_published_posts', ] );
        Capability::create( [ 'name' => 'edit_posts', ] );
        Capability::create( [ 'name' => 'edit_others_posts', ] );
        Capability::create( [ 'name' => 'edit_private_posts', ] );
        Capability::create( [ 'name' => 'edit_published_posts', ] );
        Capability::create( [ 'name' => 'manage_taxonomies', ] );
        Capability::create( [ 'name' => 'moderate_comments', ] );
        Capability::create( [ 'name' => 'publish_posts', ] );
        Capability::create( [ 'name' => 'read_private_posts', ] );
        Capability::create( [ 'name' => 'unfiltered_html', ] );
        Capability::create( [ 'name' => 'upload_files', 'description' => 'Allows user to post HTML markup or even JavaScript code in pages, posts & comments.' ] );
        Capability::create( [ 'name' => 'manage_custom_fields', 'description' => 'Allows user to list and perform CRUD operations on meta fields.' ] );


        //#! Super Admin & Administrators only

        //[::1] Dashboard
        Capability::create( [ 'name' => 'edit_dashboard', 'description' => 'Allows administrators to modify the content of the dashboard.' ] );

        //[::2] Posts/Pages/Custom Post types/Comments/Categories/Tags


        // Users
        Capability::create( [ 'name' => 'list_users', ] ); // Allows access to everything under this section - this is the root capability
        Capability::create( [ 'name' => 'promote_users', 'description' => "Enables the 'Change role to…' dropdown in the admin user list." ] );
        Capability::create( [ 'name' => 'edit_users', ] );
        Capability::create( [ 'name' => 'create_users', ] );
        Capability::create( [ 'name' => 'delete_users', ] );
        Capability::create( [ 'name' => 'block_users', 'description' => "Allows the user to block/unblock other users." ] );

        //[::4] Settings
        Capability::create( [ 'name' => 'manage_options', ] ); // Allows access to everything under this section - this is the root capability
        Capability::create( [ 'name' => 'export', ] );
        Capability::create( [ 'name' => 'import', ] );

        //[::5] Core
        Capability::create( [ 'name' => 'update_core', ] );
        Capability::create( [ 'name' => 'manage_capabilities', ] );

        //[::6] Plugins
        Capability::create( [ 'name' => 'list_plugins', ] ); // Allows access to everything under this section - this is the root capability
        Capability::create( [ 'name' => 'install_plugins', ] );
        Capability::create( [ 'name' => 'activate_plugins', ] );
        Capability::create( [ 'name' => 'deactivate_plugins', ] );
        Capability::create( [ 'name' => 'update_plugins', ] );
        Capability::create( [ 'name' => 'delete_plugins', ] );

        //[::7] Themes
        Capability::create( [ 'name' => 'list_themes', ] ); // Allows access to everything under this section - this is the root capability
        Capability::create( [ 'name' => 'install_themes', ] );
        Capability::create( [ 'name' => 'switch_themes', ] ); // Allows the user to change themes
        Capability::create( [ 'name' => 'update_themes', ] );
        Capability::create( [ 'name' => 'delete_themes', ] );

        //[::8] Translations
        Capability::create( [ 'name' => 'manage_translations', ] ); // Allows access to everything under this section - this is the root capability

        //[::9] Menus
        Capability::create( [ 'name' => 'manage_menus', ] ); // Allows access to everything under this section - this is the root capability
        Capability::create( [ 'name' => 'create_menu', ] );
        Capability::create( [ 'name' => 'update_menu', ] );
        Capability::create( [ 'name' => 'delete_menu', ] );

        //[::10] Media
        Capability::create( [ 'name' => 'list_media', ] ); // Allows access to everything under this section - this is the root capability
        Capability::create( [ 'name' => 'add_media', ] ); // requires upload_files as well
        Capability::create( [ 'name' => 'update_media', ] ); // Allows the user to change themes
        Capability::create( [ 'name' => 'delete_media', ] );
    }
}
