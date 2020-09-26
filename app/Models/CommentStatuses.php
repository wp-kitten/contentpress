<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentStatuses extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'display_name',
    ];

    public $timestamps = false;

    public function post_comments()
    {
        return $this->hasMany( PostComments::class );
    }

    public function getIDs()
    {
        $entries = [];
        $statuses = $this->all();
        if ( $statuses ) {
            foreach ( $statuses as $status ) {
                array_push( $entries, $status->id );
            }
        }
        return $entries;
    }
}
