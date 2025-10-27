<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Symptom extends Model
{
    use HasFactory;

    protected $primaryKey = 'symptom_id';

    // Biarkan default table "symptoms"
    // PK auto-increment "id" (default Laravel)

    /**
     * Best practice:
     * - gunakan guarded=[] kalau kamu mengontrol input (Form Request)
     * - atau sebutkan kolom yang bisa diisi di fillable
     */
    protected $fillable = [
        'code',
        'label',
        'is_core',
        'is_sensitive',
        'type',
    ];

    protected $casts = [
        'is_core'      => 'bool',
        'is_sensitive' => 'bool',
        // nilai type bisa 'scale' | 'boolean'; validasi di Form Request/Filament
    ];

    /** RELASI */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
