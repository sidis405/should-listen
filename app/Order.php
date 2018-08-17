<?php

namespace App;

use App\Events\NewPurchase;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public static function boot()
    {
        parent::boot();

        static::created(function ($order) {
            event(new NewPurchase($order->load('customer')));
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
