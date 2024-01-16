<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    const UPDATED_AT = 'updated';
    const CREATED_AT = 'created';
    public $timestamps = false;
    protected $table = 'cms_order_detail';
    protected $fillable = ['quantity','product_name', 'price','bid_id','order_id','product_id','product_code'];
    use HasFactory;
}