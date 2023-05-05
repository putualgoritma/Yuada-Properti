<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderCompensation extends Model
{
    public $table = 'order_compensations';
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'created_at',
        'updated_at',
        'deleted_at',
        'order_id',
        'item_name',
        'compensation_amount',
        'compensation_cost',
        'memo',
    ];
}
