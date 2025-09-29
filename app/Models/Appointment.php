<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'patient_first_name',
        'patient_last_name',
        'phone',
        'dni',
        'specialty_id',
        'doctor_id',
        'scheduled_at',
        'status',
        'has_insurance',
        'insurance_name',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'has_insurance' => 'boolean',
    ];

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
