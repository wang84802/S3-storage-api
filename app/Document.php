<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{

    protected $fillable = [
        'id',
        'uni_id',
        'file',
        'created_by',
    ];
}
