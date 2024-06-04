<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'total_demand',
        'total_distance',
        'total_time_serving',
        'total_demand_without_allocating_vehicles',
        'total_distance_without_allocating_vehicles',
        'total_time_serving_without_allocating_vehicles',
        'status',
        'total_vehicle_used',
        'total_num_customer_served',
        'total_num_customer_not_served'
    ];
    public function routes()
{
    return $this->hasMany(Route::class);
}
}
