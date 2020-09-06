<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MediaFile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug', 'path', 'title', 'alt', 'caption', 'language_id', 'created_at', 'updated_at',
    ];

    public function media_file_metas()
    {
        return $this->hasMany( MediaFileMeta::class );
    }
}
