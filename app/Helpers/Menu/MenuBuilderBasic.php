<?php

namespace App\Helpers\Menu;

use App\Category;
use App\Helpers\Menu\Traits\MenuInfo;
use App\Menu;
use App\MenuItemMeta;
use App\MenuItemType;
use App\Post;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Class MenuBuilderBase
 * @package App\Helpers\Menu
 *
 * Provides utility methods to display a basic menu
 */
class MenuBuilderBasic
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

    /*
     * Render basic menu
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

                $cssClass = '';

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
                $url = ( Route::has( $meta[ 'url' ] ) ? route( $meta[ 'url' ] ) : $meta[ 'url' ] );
                $activeClass = ( Str::containsAll( url()->current(), [ $url ] ) ? 'active' : '' );
                ?>
                <li class="<?php esc_attr_e( $cssClass ); ?>">
                    <a href="<?php esc_attr_e( $url ); ?>" class="<?php esc_attr_e( $activeClass ); ?>">
                        <?php echo wp_kses_post( $title ); ?>
                    </a>
                </li>
                <?php
            }
        }
    }

    protected function __renderMenuItemCategory( $menuItemID, $refItemID, $menuItemData = [], $cssClass = '' )
    {
        $category = Category::find( $refItemID );
        if ( $category ) {
            $url = cp_get_category_link( $category );
            $activeClass = ( Str::containsAll( url()->current(), [ $url ] ) ? 'active' : '' );
            ?>
            <li class="<?php esc_attr_e( $cssClass ); ?>">
                <a href="<?php esc_attr_e( $url ); ?>" class="<?php esc_attr_e( $activeClass ); ?>">
                    <?php echo wp_kses_post( $category->name ); ?>
                </a>
            </li>
            <?php
        }
    }

    protected function __renderMenuItemPost( $menuItemID, $refItemID, $menuItemData = [], $cssClass = '' )
    {
        $post = Post::find( $refItemID );
        if ( $post ) {
            $url = cp_get_permalink( $post );
            $activeClass = ( Str::containsAll( url()->current(), [ $url ] ) ? 'active' : '' );
            ?>
            <li class="<?php esc_attr_e( $cssClass ); ?>">
                <a href="<?php esc_attr_e( $url ); ?>" class="<?php esc_attr_e( $activeClass ); ?>">
                    <?php echo wp_kses_post( $post->title ); ?>
                </a>
            </li>
            <?php
        }
    }

}
