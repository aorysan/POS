<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanModel extends Model
{
    protected $table = 't_penjualan';
    protected $primaryKey = 'penjualan_id';
    protected $fillable = [
        'user_id',
        'penjualan_kode',
        'pembeli',
        'penjualan_tanggal',
        'image'
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'user_id');
    }

    public function details()
    {
        return $this->hasMany(DetailModel::class, 'penjualan_id', 'penjualan_id');
    }

    public function getImageUrlAttribute($value)
    {
        if ($this->image) {
            return url('/storage/posts/' . $this->image);
        }
        return null;
    }

    use HasFactory;
}