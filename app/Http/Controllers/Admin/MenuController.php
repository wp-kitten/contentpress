<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CPML;
use App\Helpers\MenuWalkerBackend;
use App\Helpers\ScriptsManager;
use App\Menu;
use Illuminate\Support\Str;

class MenuController extends AdminControllerBase
{
    public function index()
    {
        if ( !cp_current_user_can( 'manage_menus' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::enqueueStylesheet( 'menus-index.css', asset( '_admin/css/menus/index.css' ) );
        ScriptsManager::localizeScript( 'menus-index-scripts', 'MenuLocale', [
            'confirm_delete' => __( 'a.Are you sure you want to delete this menu?' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'menus-index.js', asset( '_admin/js/menus/index.js' ) );

        return view( 'admin.menu.index' )->with( [
            'menus' => Menu::where( 'language_id', cp_get_backend_user_language_id() )->get(),
        ] );
    }

    public function showCreatePage()
    {
        if ( !cp_current_user_can( 'create_menu' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::enqueueStylesheet( 'menus-index.css', asset( '_admin/css/menus/index.css' ) );
        ScriptsManager::localizeScript( 'menus-index-scripts', 'MenuLocale', [
            'confirm_delete' => __( 'a.Are you sure you want to delete this menu?' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'menus-index.js', asset( '_admin/js/menus/index.js' ) );

        return view( 'admin.menu.new' )->with( [
            'menus' => Menu::where( 'language_id', cp_get_backend_user_language_id() )->get(),
        ] );
    }

    public function showEditPage( $id )
    {
        if ( !cp_current_user_can( 'update_menu' ) ) {
            return $this->_forbidden();
        }

        //#! Because delete menu will redirect back, in edit screen this will throw an error since the menu cannot be found anymore
        if ( !Menu::find( $id ) ) {
            return redirect()->route( 'admin.menus.all' );
        }

        ScriptsManager::enqueueStylesheet( 'jquery-ui.css', asset( 'vendor/jquery-ui/jquery-ui.css' ) );
        ScriptsManager::enqueueStylesheet( 'menus-index.css', asset( '_admin/css/menus/index.css' ) );

        ScriptsManager::localizeScript( 'menus-edit-scripts', 'MenuLocale', [
            'confirm_delete_item' => __( 'a.Are you sure you want to delete this menu item?' ),
            'delete_text' => '&times;',
            'delete_text_title' => __( 'a.Delete' ),
            'menu_id' => $id,
            'confirm_delete' => __( 'a.Are you sure you want to delete this menu?' ),
            'text_options_saved' => __( 'a.Options saved.' ),
        ] );

        ScriptsManager::enqueueFooterScript( 'jquery-ui-js', asset( 'vendor/jquery-ui/jquery-ui.min.js' ) );
        ScriptsManager::enqueueFooterScript( 'menus-edit.js', asset( '_admin/js/menus/edit.js' ) );

        $menuClass = null;
        $backendLanguageID = cp_get_backend_user_language_id();
        try {
            $menuClass = new MenuWalkerBackend( $id, $backendLanguageID );
        }
        catch ( \Exception $e ) {
            logger( $e->getMessage() );
        }

        return view( 'admin.menu.edit' )->with( [
            'default_language_code' => $this->settings->getDefaultLanguageCode(),
            'menu' => Menu::where( 'id', $id )->where( 'language_id', $backendLanguageID )->first(),
            'walker' => $menuClass,
            'display_as' => $this->options->getOption( "menu-{$id}-display-as", 'megamenu' ),
        ] );
    }

    public function __insert()
    {
        if ( !cp_current_user_can( 'manage_menus' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }
        $this->request->validate( [
            'menu_name' => 'required',
        ] );

        $menuNameSlug = Str::slug( $this->request->menu_name );

        $menuModel = new Menu();

        $languageID = cp_get_backend_user_language_id();

        $exists = $menuModel->exists( $menuNameSlug, $languageID );

        $r = $menuModel->createOrUpdate( $this->request->menu_name, $languageID );

        if ( $r ) {
            $t = ( $exists ? __( 'a.Menu updated.' ) : __( 'a.Menu created.' ) );
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => $t,
            ] );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.An error occurred and the menu could not be created.' ),
        ] );
    }

    public function __update( $id )
    {
        if ( !cp_current_user_can( 'manage_menus' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }
        $this->request->validate( [
            'menu_name' => 'required',
        ] );

        $menu = Menu::findOrFail( $id );
        $menu->name = Str::title( $this->request->menu_name );
        $menu->slug = Str::slug( $this->request->menu_name );
        $r = $menu->update();

        if ( $r ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Menu updated.' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.An error occurred and the menu could not be created.' ),
        ] );
    }

    public function __delete( $id )
    {
        if ( !cp_current_user_can( 'manage_menus' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }
        $menu = Menu::find( $id );
        if ( !$menu ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The specified menu was not found.' ),
            ] );
        }
        $deleted = $menu->destroy( [ $menu->id ] );
        if ( $deleted ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.The menu has been deleted.' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.An error occurred and the menu could not be deleted.' ),
        ] );
    }
}
