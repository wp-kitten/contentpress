<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemMeta extends Model
{
    protected $fillable = [ 'menu_item_id', 'meta_name', 'meta_value', 'created_at', 'updated_at' ];

    public function menu_item()
    {
        return $this->hasMany( MenuItem::class );
    }

    public function exists( $id )
    {
        return $this->find( $id );
    }
}
