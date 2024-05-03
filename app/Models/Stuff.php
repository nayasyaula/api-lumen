<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stuff extends Model
{
    use SoftDeletes; //optional digunakan hanya untuk table yg menggunakan fitur softdeletes
    protected $fillable = ["name", "category"];

    //mendefinisikan relasi
    // table yg berperan sebagai primary key : hasone /  hasmany /
    // table yg berperan sebagai foreign key : belongsto
    // nama function disarankan menggunakan aturan berikut
    // !. one to one : nama model yg terhubung versi tunggal
    // 2. one to many : nama model yg terhubung versi jarak (untuk foreign keynya)
    public function stock()
    {
        return $this->hasOne(StuffStock::class);
    }

    public function inboundStuffs()
    {
        return $this->hasMany(InboundStuff::class);
    }

    public function lendings()
    {
        return $this->hasMany(Lending::class);
    }
}
