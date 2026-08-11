<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldHeartDayEntry extends Model
{
    protected $fillable = [
        'source_row',
        'employee_name',
        'employee_code',
        'doctor_name',
        'msl_code',
        'speciality',
        'gender',
        'photo_url',
        'photo_path',
        'banner_path',
        'doctor_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
