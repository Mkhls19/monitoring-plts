<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrediksiDes extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'tanggal_prediksi',
        'data_aktual',
        'hasil_prediksi',
        'alpha',
        'beta',
        'level_lt',
        'trend_tt',
        'mape'
    ];

    protected $casts = [
        'tanggal_prediksi' => 'date',
    ];
}
