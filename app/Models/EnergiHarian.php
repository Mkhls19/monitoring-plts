<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergiHarian extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'tanggal',
        'energi_pv_harian',
        'energi_pv_bulanan',
        'energi_pv_total',
        'month_cost_pv',
        'total_cost_pv',
        'energi_charge_ac_harian',
        'energi_charge_ac_bulanan',
        'energi_charge_ac_total',
        'month_cost_charge_ac',
        'total_cost_charge_ac',
        'psh'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
