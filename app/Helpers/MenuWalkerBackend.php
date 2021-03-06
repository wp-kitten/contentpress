<?php

namespace App\Helpers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuItemMeta;
use App\Models\MenuItemType;
use App\Models\Post;

class MenuWalkerBackend implements IMenuWalker
{
    /**
     * @var Menu|null
     */
    private $menu = null;

    /**
     * Stores the list of menu items
     * @var array
     */
    private $menuItems = [];

    /**
     * MenuWalkerBackend constructor.
     * @param string|int $menu The menu name, slug or id
     * @param int|null $languageID
     * @throws \Exception if the menu doesn't exist
     */
    public function __construct( $menu, $languageID = null )
    {
        if ( empty( $languageID ) ) {
            $languageID = VPML::getDefaultLanguageID();
        }

        $theMenu = Menu::where( 'language_id', $languageID )
            ->where( function ( $query ) use ( $menu ) {
                $query->where( 'id', $menu )
                    ->orWhere( 'name', $menu )
                    ->orWhere( 'slug', $menu );
            } )
            ->first();

        if ( !$theMenu ) {
            throw new \Exception( __( 'a.The specified menu was not found.' ) );
        }

        $this->menu = $theMenu;
    }

    /**
     * Check to see whether or not the current menu has any menu items
     * @return bool
     */
    public function hasMenuItems()
    {
        if ( !empty( $this->menuItems ) ) {
            return true;
        }
        return ( count( $this->getMenuItems() ) > 0 );
    }

    /**
     * Retrieve the list of all menu items
     * @return array
     */
    public function getMenuItems()
    {
        //#! Check the first level menu items first
        $menuItems = MenuItem::where( 'menu_id', $this->menu->id )->where( 'menu_item_id', null )->orderBy( 'menu_order', 'ASC' )->orderBy( 'id', 'ASC' )->get();
        if ( $menuItems->count() > 0 ) {
            foreach ( $menuItems as $menuItem ) {
                $this->menuItems[ $menuItem->id ] = [
                    'ref_item_id' => $menuItem->ref_item_id,
                    'menu_item_type_id' => $menuItem->menu_item_type_id,
                    'items' => [],
                ];
            }

            $this->__processMenuItems();
        }
        return $this->menuItems;
    }

    /**
     * Render the menu
     * @param array $menuItems
     */
    public function outputHtml( array $menuItems = [] )
    {
        if ( empty( $menuItems ) ) {
            return;
        }
        foreach ( $menuItems as $menuItemID => $menuItemData ) {
            $refItemID = $menuItemData[ 'ref_item_id' ];
            $typeID = $menuItemData[ 'menu_item_type_id' ];

            $type = MenuItemType::find( $typeID );

            if ( 'custom' == $type->name ) {
                //#! Get meta info
                $metaData = MenuItemMeta::where( 'menu_item_id', $menuItemID )->where( 'meta_name', '_menu_item_data' )->first();
                if ( $metaData ) {
                    $meta = maybe_unserialize( $metaData->meta_value );
                    if ( !empty( $meta ) && isset( $meta[ 'title' ] ) && isset( $meta[ 'url' ] ) ) {
                        $title = $meta[ 'title' ];
                        ?>
                        <li data-id="<?php esc_attr_e( $refItemID ); ?>"
                            data-menu-item-id="<?php esc_attr_e( $menuItemID ); ?>"
                            data-selector="<?php esc_attr_e( $type->name . $menuItemID ); ?>"
                            data-type="<?php esc_attr_e( $type->name ); ?>"
                            data-title="<?php esc_attr_e( $title ); ?>"
                            data-url="<?php esc_attr_e( $meta[ 'url' ] ); ?>"
                            class="dd-item">
                            <?php
                            if ( !empty( $menuItemData[ 'items' ] ) ) {
                                ?>
                                <button class="dd-collapse" data-action="collapse" type="button"><?php esc_html_e( __( 'a.Collapse' ) ); ?></button>
                                <button class="dd-expand" data-action="expand" type="button"><?php esc_html_e( __( 'a.Expand' ) ); ?></button>
                                <?php
                            }
                            ?>
                            <a href="#" class="js-btn-remove" title="<?php esc_attr_e( __( 'a.Delete' ) ); ?>">
                                <?php echo '&times;'; ?>
                            </a>
                            <div class="dd-handle">
                                <span class="dd-content" title="<?php esc_attr_e( $title ); ?>"><?php esc_html_e( $title ); ?></span>
                            </div>
                            <?php
                            $this->__renderSubmenus( $menuItemData[ 'items' ] );
                            ?>
                        </li>
                        <?php
                    }
                }
            }
            elseif ( 'category' == $type->name ) {
                $category = Category::find( $refItemID );
                if ( $category ) {
                    ?>
                    <li data-id="<?php esc_attr_e( $category->id ); ?>"
                        data-menu-item-id="<?php esc_attr_e( $menuItemID ); ?>"
                        data-selector="<?php esc_attr_e( $type->name . $menuItemID ); ?>"
                        data-type="<?php esc_attr_e( $type->name ); ?>"
                        class="dd-item">
                        <?php
                        if ( !empty( $menuItemData[ 'items' ] ) ) {
                            ?>
                            <button class="dd-collapse" data-action="collapse" type="button"><?php esc_html_e( __( 'a.Collapse' ) ); ?></button>
                            <button class="dd-expand" data-action="expand" type="button"><?php esc_html_e( __( 'a.Expand' ) ); ?></button>
                            <?php
                        }
                        ?>
                        <a href="#" class="js-btn-remove" title="<?php esc_attr_e( __( 'a.Delete' ) ); ?>">
                            <?php echo '&times;'; ?>
                        </a>
                        <div class="dd-handle">
                            <span class="dd-content" title="<?php esc_attr_e( $category->name ); ?>"><?php esc_html_e( $category->name ); ?></span>
                        </div>
                        <?php
                        $this->__renderSubmenus( $menuItemData[ 'items' ] );
                        ?>
                    </li>
                    <?php
                }
            }
            // post/page/custom post type
            else {
                $post = Post::find( $refItemID );
                if ( $post ) {
                    ?>
                    <li data-id="<?php esc_attr_e( $post->id ); ?>"
                        data-menu-item-id="<?php esc_attr_e( $menuItemID ); ?>"
                        data-selector="<?php esc_attr_e( $type->name . $menuItemID ); ?>"
                        data-type="<?php esc_attr_e( $type->name ); ?>"
                        class="dd-item">
                        <?php
                        if ( !empty( $menuItemData[ 'items' ] ) ) {
                            ?>
                            <button class="dd-collapse" data-action="collapse" type="button"><?php esc_html_e( __( 'a.Collapse' ) ); ?></button>
                            <button class="dd-expand" data-action="expand" type="button"><?php esc_html_e( __( 'a.Expand' ) ); ?></button>
                            <?php
                        }
                        ?>
                        <a href="#" class="js-btn-remove" title="<?php esc_attr_e( __( 'a.Delete' ) ); ?>">
                            <?php echo '&times;'; ?>
                        </a>
                        <div class="dd-handle">
                            <span class="dd-content" title="<?php esc_attr_e( $post->title ); ?>"><?php esc_html_e( $post->title ); ?></span>
                        </div>
                        <?php
                        $this->__renderSubmenus( $menuItemData[ 'items' ] );
                        ?>
                    </li>
                    <?php
                }
            }
        }
    }

    private function __renderSubmenus( $menuItemData )
    {
        if ( !empty( $menuItemData ) ) {
            echo '<ul class="dd-list">';
            $this->outputHtml( $menuItemData );
            echo '</ul>';
        }
    }

    //#! Process each first level menu item and recurse into it for children
    private function __processMenuItems()
    {
        foreach ( $this->menuItems as $menuItemID => $menuItemData ) {
            $this->menuItems[ $menuItemID ][ 'items' ] = $this->__processSubMenuItem( $menuItemID );
        }
    }

    private function __processSubMenuItem( $menuItemID )
    {
        $data = [];
        $children = $this->__getChildren( $menuItemID );
        if ( $children && $children->count() ) {
            foreach ( $children as $child ) {
                if ( !isset( $data[ $child->id ] ) ) {
                    $data[ $child->id ] = [];
                }
                $data[ $child->id ][ 'ref_item_id' ] = $child->ref_item_id;
                $data[ $child->id ][ 'menu_item_type_id' ] = $child->menu_item_type_id;
                $data[ $child->id ][ 'items' ] = $this->__processSubMenuItem( $child->id );
            }
        }
        return $data;
    }

    private function __getChildren( $menuItemID )
    {
        return MenuItem::where( 'menu_item_id', $menuItemID )->orderBy( 'menu_order', 'ASC' )->get();
    }
}
