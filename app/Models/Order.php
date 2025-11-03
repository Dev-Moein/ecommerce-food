<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory,SoftDeletes;
    protected $table ='orders';
    public function getStatusAttribute($status){
        switch($status){
        case '0':
            $status = 'درانتطار پرداخت';
            break;
            case '1':
            $status = 'در حال پردازش' ;
            break;
             case '2':
            $status = 'ارسال شده' ;
            break;
            case '3':
            $status = 'لغو شده' ;
            break;
        }
        return $status;
    }
        public function getPaymentStatusAttribute($paymentStatus){
            switch($paymentStatus){
                case '0':
                    $paymentStatus='ناموفق';
                    break;
                     case '1':
                    $paymentStatus='موفق';
                    break;
            }
            return $paymentStatus;
        }


     protected $fillable = [
        'user_id',
        'address_id',
        'total_amount',
        'coupon_amount',
        'paying_amount',
        'coupon_id',
        'payment_status',
    ];
    public function products()
{
    return $this->belongsToMany(Product::class, 'order_items');
}
    public function address()
{
    return $this->belongsTo(UserAddress::class);
}
    public function orderItems()
{
    return $this->hasMany(OrderItem::class);
}
}

