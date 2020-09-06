<?php

namespace App;

use App\Helpers\CPML;
use App\Helpers\Util;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Menu extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'language_id', 'created_at', 'updated_at',
    ];

    public function menus()
    {
        return $this->hasMany( Menu::class );
    }

    public function menuItems()
    {
        return $this->hasMany( MenuItem::class );
    }

    /**
     * Check to see whether or not a specific menu or menu item exists
     * @param string $menuSlug
     * @param int|null $languageID
     * @return mixed
     */
    public function exists( $menuSlug, $languageID = null )
    {
        if ( empty( $languageID ) ) {
            $languageID = CPML::getDefaultLanguageID();
        }
        return $this->where( 'slug', $menuSlug )->where( 'language_id', $languageID )->first();
    }

    public function createOrUpdate( $menuName, $languageID = null )
    {
        $slug = Str::slug( $menuName );
        if ( $v = $this->exists( $slug, $languageID ) ) {
            $v->name = Str::title( $menuName );
            $v->slug = $slug;
            $v->language_id = $languageID;

            $v->update();
        }
        else {
            $v = $this->create( [
                'name' => Str::title( $menuName ),
                'slug' => $slug,
                'language_id' => $languageID,
            ] );
        }
        return $v->id;
    }
}
