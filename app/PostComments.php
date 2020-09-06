<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostComments extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content',
        'author_name',
        'author_email',
        'author_url',
        'author_ip',
        'user_agent',
        'user_id',
        'post_id',
        'comment_status_id',
        'comment_id',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo( User::class );
    }

    public function post()
    {
        return $this->belongsTo( Post::class );
    }

    public function posts()
    {
        return $this->belongsToMany( Post::class );
    }

    public function post_status()
    {
        return $this->belongsTo( CommentStatuses::class );
    }

    public function comment()
    {
        return $this->hasMany( PostComments::class, 'comment_id' );
    }

    public function childrenComments()
    {
        return $this->hasMany( PostComments::class, 'comment_id' )->with( 'comment' );
    }
}
