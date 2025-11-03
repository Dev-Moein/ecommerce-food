<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
   use HasFactory,SoftDeletes;
    protected $guarded =[];
    public function getStatusAttribute($status){
        switch($status){
        case '0':
            $status = 'ناموفق';
            break;
            case '1':
            $status = 'موفق' ;
            break;
        }
        return $status;
    }
}
