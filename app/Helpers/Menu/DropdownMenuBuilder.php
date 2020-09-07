<?php

namespace App\Helpers\Menu;

use App\Category;
use App\Helpers\Menu\Traits\MenuInfo;
use App\Menu;
use App\MenuItemMeta;
use App\MenuItemType;
use App\Post;
use Illuminate\Support\Facades\Route;

class DropdownMenuBuilder implements IMenuBuilder
{
    use MenuInfo;

    /**
     * @var Menu
     */
    protected $menu;
    /**
     * @var array
     */
    protected $menuItems;

    /**
     * MenuBuilderBase constructor.
     * @param Menu $menu
     * @param array $menuItems
     */
    public function __construct( Menu $menu, array $menuItems = [] )
    {
        $this->menu = $menu;
        $this->menuItems = $menuItems;
    }

    /**
     * @inheritDoc
     */
    public function outputHtml()
    {
        if ( !empty( $this->menuItems ) ) {
            //#! Render the main menu items & let them recurse into their children
            foreach ( $this->menuItems as $menuItemID => $menuItemData ) {
                $refItemID = $menuItemData[ 'ref_item_id' ];
                $typeID = $menuItemData[ 'menu_item_type_id' ];
                $type = MenuItemType::find( $typeID );
                if ( !$type ) {
                    continue;
                }

                $hasChildren = ( isset( $menuItemData[ 'items' ] ) && !empty( $menuItemData[ 'items' ] ) );
                $cssClass = ( $hasChildren ? 'has-submenu' : 'menu-item' );

                if ( 'custom' == $type->name ) {
                    $this->__renderMenuItemCustom( $menuItemID, $refItemID, $menuItemData, $cssClass );
                }
                elseif ( 'category' == $type->name ) {
                    $this->__renderMenuItemCategory( $menuItemID, $refItemID, $menuItemData, $cssClass );
                }
                else {
                    $this->__renderMenuItemPost( $menuItemID, $refItemID, $menuItemData, $cssClass );
                }
            }
        }
    }

    protected function __renderMenuItemCustom( $menuItemID, $refItemID, $menuItemData = [], $cssClass = '' )
    {
        $metaData = MenuItemMeta::where( 'menu_item_id', $menuItemID )->where( 'meta_name', '_menu_item_data' )->first();
        if ( $metaData ) {
            $meta = maybe_unserialize( $metaData->meta_value );
            if ( !empty( $meta ) && isset( $meta[ 'title' ] ) && isset( $meta[ 'url' ] ) ) {
                $title = $meta[ 'title' ];

                //#! Check to see if this is a route
                if ( Route::has( $meta[ 'url' ] ) ) {
                    $url = route( $meta[ 'url' ] );
                }
                else {
                    $url = $meta[ 'url' ];
                }

                if ( $cssClass == 'has-submenu' ) {
                    ?>
                    <div class="<?php esc_attr_e( $cssClass ); ?>">
                        <button class="show-submenu">
                            <?php esc_html_e( $title ); ?>
                            <i class="fa fa-caret-down"></i>
                        </button>
                        <div class="submenu-content">
                            <?php
                            //#! Render the submenu tree
                            $this->__renderSubmenuTree( $menuItemData );
                            ?>
                        </div>
                    </div>
                    <?php
                }
                else {
                    echo '<a href="' . esc_attr( $url ) . '" class="' . esc_attr( $cssClass ) . '">' . esc_html( $title ) . '</a>';
                }
            }
        }
    }

    protected function __renderMenuItemCategory( $menuItemID, $refItemID, $menuItemData = [], $cssClass = '' )
    {
        $category = Category::find( $refItemID );
        if ( $category ) {
            if ( $cssClass == 'has-submenu' ) {
                ?>
                <div class="<?php esc_attr_e( $cssClass ); ?>">
                    <button class="show-submenu">
                        <?php esc_html_e( $category->name ); ?>
                        <i class="fa fa-caret-down"></i>
                    </button>
                    <div class="submenu-content">
                        <?php
                        //#! Render the submenu tree
                        $this->__renderSubmenuTree( $menuItemData );
                        ?>
                    </div>
                </div>
                <?php
            }
            else {
                ?>
                <a href="<?php cp_get_category_link( $category ); ?>" class="<?php esc_attr_e( $cssClass ); ?>"><?php esc_html_e( $category->name ); ?></a>
                <?php
            }
        }
    }

    protected function __renderMenuItemPost( $menuItemID, $refItemID, $menuItemData = [], $cssClass = '' )
    {
        $post = Post::find( $refItemID );
        if ( $post ) {
            $menuItemInfo = $this->__getMenuInfo( $menuItemID, $menuItemData );
            if ( $cssClass == 'has-submenu' ) {
                ?>
                <div class="<?php esc_attr_e( $cssClass ); ?>">
                    <button class="show-submenu">
                        <?php esc_html_e( $post->title ); ?>
                        <i class="fa fa-caret-down"></i>
                    </button>
                    <div class="submenu-content">
                        <?php
                        //#! Render the submenu tree
                        $this->__renderSubmenuTree( $menuItemData );
                        ?>
                    </div>
                </div>
                <?php
            }
            else {
                ?>
                <a href="<?php esc_attr_e( $menuItemInfo[ 'url' ] ); ?>" class="<?php esc_attr_e( $cssClass ); ?>">
                    <?php esc_html_e( $post->title ); ?>
                </a>
                <?php
            }
        }
    }

    protected function __renderSubmenuTree( $menuItemData = [] )
    {
        if ( !empty( $menuItemData[ 'items' ] ) ) {

            foreach ( $menuItemData[ 'items' ] as $miID => $miData ) {
                $menuItemInfo = $this->__getMenuInfo( $miID, $miData );
                $cssClass = ( empty( $miData[ 'items' ] ) ? 'menu-item' : 'has-submenu' );

                if ( $cssClass == 'has-submenu' ) {
                    ?>
                    <div class="<?php esc_attr_e( $cssClass ); ?>">
                        <button class="show-submenu">
                            <?php esc_html_e( $menuItemInfo[ 'title' ] ); ?>
                            <i class="fa fa-caret-down"></i>
                        </button>
                        <div class="submenu-content">
                            <?php
                            //#! Recurse into children
                            $this->__renderSubmenuTree( $miData );
                            ?>
                        </div>
                    </div>
                    <?php
                }
                else {
                    echo '<a href="' . esc_attr( $menuItemInfo[ 'url' ] ) . '" class="' . $cssClass . '">' . esc_html( $menuItemInfo[ 'title' ] ) . '</a>';
                }
            }
        }
    }
}
