<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class project extends Model
{
    // public $table = 'orders';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'created_at',
        'updated_at',
        'deleted_at',
        'code',
        'name',
        'project_id',
    ];
}
