<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_order', 'ref_item_id', 'menu_id', 'menu_item_id', 'menu_item_type_id',
    ];

    public $timestamps = false;

    public function menus()
    {
        return $this->belongsToMany( Menu::class );
    }

    public function menu_items()
    {
        return $this->hasMany( MenuItem::class );
    }

    public function childrenMenuItems()
    {
        return $this->hasMany( MenuItem::class )->with( 'menu_item' );
    }

    public function menuItemType()
    {
        return $this->belongsTo( MenuItemType::class );
    }

    public function getRefEntry( $postTypeName )
    {
        if('category' == $postTypeName){
            return $this->hasOne(Category::class, 'id', 'ref_item_id');
        }
        elseif('custom' == $postTypeName){
            return null;
        }

        //#! Post types
        return $this->hasOne(PostType::class, 'id');
    }

    public function menu_item_metas()
    {
        return $this->hasMany( MenuItemMeta::class );
    }
}
