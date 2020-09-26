<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Options extends Model
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
     * Helper method to retrieve the value of the specified option name
     * @static
     * @param $optionName
     * @param bool $default
     * @return mixed
     */
    public function getOption( $optionName, $default = false )
    {
        $v = $this->where( 'name', $optionName )->first();
        return ( $v ? maybe_unserialize( $v->value ) : $default );
    }

    /**
     * Helper method to retrieve the list of all enabled languages
     * @return mixed
     */
    public function getEnabledLanguages()
    {
        return $this->getOption( 'enabled_languages', [] );
    }

    /**
     * Create the option if it doesn't exist or update the existing one.
     * @param string $name
     * @param null|mixed $value
     * @return mixed
     */
    public function addOption( $name, $value = null )
    {
        $v = $this->where( 'name', $name )->first();
        if ( !$v ) {
            $v = $this->create( [
                'name' => $name,
                'value' => maybe_serialize( $value ),
            ] );
        }
        else {
            $v->value = maybe_serialize( $value );
            $v->update();
        }
        return $v;
    }
}
