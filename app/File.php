<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    protected $data = ['deleted_at'];
    protected $fillable = [
        'id',
        'name',
        'size',
        'uni_id',
        'created_by',
        'updated_by',
        'updated_at',
    ];
}
