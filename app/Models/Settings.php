<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'value',
    ];

    public $timestamps = false;

    /**
     * Retrieve a setting
     * @param string $name The name of the setting which value to retrieve
     * @param bool $defaultValue The default value to retrieve if the setting is not found
     * @return bool|mixed
     */
    public function getSetting( $name, $defaultValue = false )
    {
        $m = $this->where( 'name', $name )->first();
        return ( $m ? maybe_unserialize( $m->value ) : $defaultValue );
    }

    /**
     * Helper method to retrieve the default language code set in the database
     * @return bool|mixed
     */
    public function getDefaultLanguageCode()
    {
        return $this->getSetting( 'default_language', app()->getLocale() );
    }

    // Create or update
    public function updateSetting( $name, $value = false )
    {
        $setting = $this->where( 'name', $name )->first();
        if ( $setting ) {
            $setting->value = maybe_serialize( $value );
            return $setting->update();
        }
        return $this->create( [
            'name' => esc_html( $name ),
            'value' => maybe_serialize( $value ),
        ] );
    }

    public function deleteSetting( $name )
    {
        $setting = $this->where( 'name', $name )->first();
        if ( $setting ) {
            return $this->destroy([$setting->id]);
        }
        return false;
    }
}
