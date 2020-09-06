<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cache extends Model
{
    protected $fillable = [ 'key', 'value', 'created_at', 'updated_at' ];

    public $timestamps = true;

    public function add( $key, $value = null )
    {
        $key = wp_kses( $key, [] );
        $value = ( $value ? maybe_serialize( $value ) : $value );

        $entry = $this->__getEntry( $key );
        if ( $entry ) {
            $entry->value = $value;
            return $entry->update();
        }
        return $this->create( [
            'key' => $key,
            'value' => $value,
        ] );
    }

    /**
     * @param string $key
     * @param mixed $default
     * @param int|null $cacheTimeout The number of minutes the cache is valid
     * @return mixed|null
     */
    public function get( $key, $default = null, $cacheTimeout = null )
    {
        $entry = $this->__getEntry( $key );
        if ( $entry ) {
            //#! Check if expired
            if ( $cacheTimeout ) {
                try {
                    $updatedAt = new \DateTime( $entry->updated_at );
                    $expiresAt = ( 60 * $cacheTimeout ) + strtotime( $updatedAt->format( "Y-m-d H:i:s" ) );
                    if ( $expiresAt < time() ) {
                        return $default;
                    }
                }
                catch ( \Exception $e ) {
                    logger( $e->getMessage() );
                }
            }
            return maybe_unserialize( $entry->value );
        }
        return $default;
    }

    /**
     * Delete all entries from the table
     */
    public function clear()
    {
        $this->where( 'key', '!=', '' )->delete();
    }

    private function __getEntry( $key )
    {
        return $this->where( 'key', $key )->first();
    }

}
