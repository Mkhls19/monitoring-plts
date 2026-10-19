<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'relay_status',
        'voltage_cutoff',
    ];

    protected $casts = [
        'relay_status' => 'boolean',
        'voltage_cutoff' => 'float',
    ];
}
