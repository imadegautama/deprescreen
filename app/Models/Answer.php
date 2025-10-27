<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $primaryKey = 'answers_id';

    protected $fillable = [
        'screening_session_id',
        'symptom_id',
        'value',
    ];

    protected $casts = [
        'screening_session_id' => 'integer',
        'symptom_id' => 'integer',
        'value'      => 'integer',
    ];

    /** RELASI */
    public function screeningSession()
    {
        return $this->belongsTo(
            ScreeningSession::class,
            'screening_session_id',  // Foreign key in answers table
            'screening_session_id'   // Primary key in screening_sessions table
        );
    }

    public function symptom()
    {
        return $this->belongsTo(
            Symptom::class,
            'symptom_id',  // Foreign key in answers table
            'symptom_id'   // Primary key in symptoms table
        );
    }

    /** Scope praktis */
    public function scopeForSession($q, int $sessionId)
    {
        return $q->where('screening_session_id', $sessionId);
    }
}
