<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;
    protected $fillable = [
        'plan_id',
        'depot_id',
        'route',
        'total_demand',
        'total_distance',
        'total_time_serving',
        'is_served'
    ];
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

}
