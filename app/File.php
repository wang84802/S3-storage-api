<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    protected $data = ['deleted_at'];
    protected $fillable = [
        'name',
        'extension',
        'size',
        'created_by',
        'updated_by',
        'updated_at',
    ];
}
