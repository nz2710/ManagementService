<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'address', 'register_date' , 'phone', 'discount', 'revenue', 'commission', 'status', 'created_at', 'updated_at','number_of_order'];
    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

}
