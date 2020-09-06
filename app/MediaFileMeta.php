<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MediaFileMeta extends Model
{
    protected $fillable = [ 'media_file_id', 'language_id', 'meta_name', 'meta_value' ];

    public $timestamps = false;

    public function media_files()
    {
        return $this->hasMany( MediaFile::class );
    }
}
