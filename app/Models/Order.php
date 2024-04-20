<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory,SoftDeletes;
    public function partner()
    {
        return $this->belongsTo('App\Models\Partner');
    }
    protected static function booted()
    {
        static::created(function ($order) {
            $order->partner->increment('revenue', $order->price);
            $order->partner->increment('number_of_order');
            $order->partner->update(['commission' => $order->partner->revenue * ($order->partner->discount / 100)]);
        });

        static::updated(function ($order) {
            $order->partner->decrement('revenue', $order->getOriginal('price'));
            $order->partner->increment('revenue', $order->price);
            $order->partner->update(['commission' => $order->partner->revenue * ($order->partner->discount / 100)]);
        });

        static::deleted(function ($order) {
            $order->partner->decrement('revenue', $order->price);
            $order->partner->decrement('number_of_orders');
            $order->partner->update(['commission' => $order->partner->revenue * ($order->partner->discount / 100)]);
        });
    }
}
