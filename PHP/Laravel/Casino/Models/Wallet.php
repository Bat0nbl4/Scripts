<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'profit_factor',
        'value',
        'status'
    ];

    public function owners()
    {
        return $this->hasMany(Owner::class);
    }
}
