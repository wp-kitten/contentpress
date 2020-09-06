<?php

namespace App\Helpers\Menu;


use App\Category;
use App\Helpers\Menu\Traits\MenuInfo;
use App\Menu;
use App\MenuItemMeta;
use App\MenuItemType;
use App\Post;
use Illuminate\Support\Facades\Route;

class MegaMenuBuilder implements IMenuBuilder
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
            foreach ( $this->menuItems as $menuItemID => $menuItemData ) {
                $refItemID = $menuItemData[ 'ref_item_id' ];
                $typeID = $menuItemData[ 'menu_item_type_id' ];
                $type = MenuItemType::find( $typeID );
                if ( !$type ) {
                    continue;
                }

                $hasChildren = ( isset( $menuItemData[ 'items' ] ) && !empty( $menuItemData[ 'items' ] ) );
                $cssClass = ( $hasChildren ? 'has-dropdown megamenu' : '' );

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
                ?>
                <li class="<?php esc_attr_e( $cssClass ); ?>">
                    <a href="<?php esc_attr_e( $url ); ?>"><?php esc_html_e( $title ); ?></a>
                </li>
                <?php
            }
        }
    }

    protected function __renderMenuItemCategory( $menuItemID, $refItemID, $menuItemData = [], $cssClass = '' )
    {
        $category = Category::find( $refItemID );
        if ( $category ) {
            ?>
            <li class="<?php esc_attr_e( $cssClass ); ?>">
                <a href="#"><?php esc_html_e( $category->name ); ?></a>
                <?php
                //  $this->__renderHtmlBackendSubmenus( $menuItemData[ 'items' ] );
                ?>
            </li>
            <?php
        }
    }

    protected function __renderMenuItemPost( $menuItemID, $refItemID, $menuItemData = [], $cssClass = '' )
    {
        $post = Post::find( $refItemID );
        if ( $post ) {
            $menuItemInfo = $this->__getMenuInfo( $menuItemID, $menuItemData );
            ?>
            <li class="<?php esc_attr_e( $cssClass ); ?>">
                <a href="<?php esc_attr_e( $menuItemInfo[ 'url' ] ); ?>"><?php esc_html_e( $post->title ); ?></a>
                <?php
                //#! Render the submenu tree
                if ( !empty( $menuItemData[ 'items' ] ) ) {
                    ?>
                    <ul class="sub-menu megamenu-wrapper flex">
                        <?php
                        foreach ( $menuItemData[ 'items' ] as $miID => $miData ) {
                            $captionInfo = $this->__getMenuInfo( $miID, $miData );
                            ?>
                            <li>
                                <ul class="megamenu-child">
                                    <li class="megamenu-title"><?php esc_html_e( $captionInfo[ 'title' ] ); ?></li>
                                    <?php
                                    if ( !empty( $miData[ 'items' ] ) ) {
                                        foreach ( $miData[ 'items' ] as $micID => $micData ) {
                                            $menuItemInfo = $this->__getMenuInfo( $micID, $micData );
                                            echo '<li><a href="' . esc_attr( $menuItemInfo[ 'url' ] ) . '">' . esc_html( $menuItemInfo[ 'title' ] ) . '</a></li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                    <?php
                }
                ?>
            </li>
            <?php
        }
    }
}
