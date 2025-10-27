<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningSession extends Model
{
    use HasFactory;

    protected $primaryKey = 'screening_session_id';

    protected $fillable = [
        'score',
        'level',
        'has_core',
        'crisis_flag',
        'created_at',
    ];

    protected $casts = [
        'score'       => 'integer',
        'has_core'    => 'bool',
        'crisis_flag' => 'bool',
        'created_at'  => 'datetime',
    ];

    /** RELASI */
    public function answers()
    {
        return $this->hasMany(
            Answer::class,
            'screening_session_id',  // Foreign key in answers table
            'screening_session_id'   // Primary key in this table
        );
    }

    /** Scope berguna untuk panel/analitik */
    public function scopeRecent($q)
    {
        return $q->orderByDesc('created_at');
    }
    public function scopeByLevel($q, string $level)
    {
        return $q->where('level', $level);
    }
    public function scopeCrisis($q)
    {
        return $q->where('crisis_flag', true);
    }

    /** Accessor kecil (membantu di blade/Filament) */
    public function getRiskBadgeColorAttribute(): string
    {
        return match ($this->level) {
            'tinggi' => 'danger',
            'sedang' => 'warning',
            default  => 'success',
        };
    }
}
