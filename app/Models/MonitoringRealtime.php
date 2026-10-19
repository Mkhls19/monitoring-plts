<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringRealtime extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'timestamp',
        'battery_voltage',
        'pv_voltage',
        'pv_charge_current',
        'pv_power',
        'battery_capacity_percent',
        'ac_input_voltage',
        'ac_output_voltage',
        'ac_charge_current',
        'ac_charge_power',
        'ac_input_frequency',
        'ac_output_frequency',
        'inverter_load_percent'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
