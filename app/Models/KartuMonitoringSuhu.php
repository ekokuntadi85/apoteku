<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuMonitoringSuhu extends Model
{
    protected $fillable = [
        'suhu_ruangan',
        'suhu_pendingin',
        'waktu_pengukuran',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
