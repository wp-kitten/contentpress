<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'name', 'created_at', 'updated_at',
    ];

    public function posts()
    {
        return $this->hasMany( Post::class );
    }

    public function categories()
    {
        return $this->hasMany( Post::class );
    }

    public function tags()
    {
        return $this->belongsTo( Post::class );
    }

    public function post_types()
    {
        return $this->hasMany( PostType::class );
    }

    public function getID( $languageCode )
    {
        $entry = $this->where( 'code', $languageCode )->first();
        return ( $entry ? $entry->id : 0 );
    }

    /**
     * Retrieve the human readable name of the language
     * @param int|string $langCodeOrID
     * @return mixed
     */
    public function getNameFrom( $langCodeOrID )
    {
        $entry = $this->getFrom( $langCodeOrID );
        return ( $entry ? $entry->name : '' );
    }

    /**
     * Retrieve a row identified by $langCodeOrID
     * @param int|string $langCodeOrID
     * @return mixed
     */
    public function getFrom( $langCodeOrID )
    {
        return $this->where( 'code', $langCodeOrID )->orWhere( 'id', $langCodeOrID )->first();
    }

    /**
     * Retrieve the language code from either the language name or id
     * @param string|int $nameOrID
     * @return string
     */
    public function getCodeFrom( $nameOrID )
    {
        $entry = $this->where( 'name', $nameOrID )->orWhere( 'id', $nameOrID )->first();
        return ( $entry ? $entry->code : '' );
    }
}
