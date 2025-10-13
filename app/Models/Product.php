<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $table = 'products';
    protected $appends = ['is_sale'];

    public function getIsSaleAttribute()
    {
        return $this->quantity > 0 && $this->sale_price !== 0 && $this->sale_price !== null && $this->date_on_sale_from < Carbon::now() && $this->date_on_sale_to > Carbon::now();
    }
}
