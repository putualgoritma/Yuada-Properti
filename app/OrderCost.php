<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderCost extends Model
{
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
        'cost',
    ];


    public function orders()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
