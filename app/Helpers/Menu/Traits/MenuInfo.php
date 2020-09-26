<?php

namespace App\Helpers\Menu\Traits;

use App\Models\Category;
use App\Models\MenuItemMeta;
use App\Models\MenuItemType;
use App\Models\Post;

trait MenuInfo
{
    /**
     * Retrieve the title and link of the menu item
     * @param int $menuItemID
     * @param array $menuItemData
     * @return array
     */
    public function __getMenuInfo( $menuItemID, $menuItemData = [] )
    {
        $result = [
            'title' => '',
            'url' => '',
        ];

        if ( empty( $menuItemData ) ) {
            return $result;
        }

        $type = MenuItemType::find( $menuItemData[ 'menu_item_type_id' ] );
        if ( !$type ) {
            return $result;
        }

        if ( 'custom' == $type->name ) {
            $metaData = MenuItemMeta::where( 'menu_item_id', $menuItemID )->where( 'meta_name', '_menu_item_data' )->first();
            if ( $metaData ) {
                $meta = maybe_unserialize( $metaData->meta_value );
                if ( !empty( $meta ) && isset( $meta[ 'title' ] ) && isset( $meta[ 'url' ] ) ) {
                    $result[ 'title' ] = $meta[ 'title' ];
                    $result[ 'url' ] = $meta[ 'url' ];
                }
            }
        }
        elseif ( 'category' == $type->name ) {
            if ( !isset( $menuItemData[ 'ref_item_id' ] ) ) {
                return $result;
            }
            $category = Category::find( $menuItemData[ 'ref_item_id' ] );
            if ( $category ) {
                $result[ 'title' ] = $category->name;
                $result[ 'url' ] = route( 'blog.category', $category->slug );
            }
        }
        else {
            if ( !isset( $menuItemData[ 'ref_item_id' ] ) ) {
                return $result;
            }
            $post = Post::find( $menuItemData[ 'ref_item_id' ] );
            if ( $post ) {
                $result[ 'title' ] = $post->title;
                if ( 'post' == $post->post_type ) {
                    $url = route( 'app.post.view', $post->slug );
                }
                elseif ( 'page' == $post->post_type ) {
                    $url = route( 'app.page.view', $post->slug );
                }
                else {
                    $url = route( 'app.' . $type->name . '.view', $post->slug );
                }

                $result[ 'url' ] = $url;
            }
        }
        return $result;
    }
}
