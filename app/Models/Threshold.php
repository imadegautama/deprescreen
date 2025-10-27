<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Threshold extends Model
{
    use HasFactory;

    protected $primaryKey = 'threshold_id';

    protected $fillable = [
        'level',
        'min_score',
        'max_score',
        'advice_text',
    ];

    protected $casts = [
        'min_score' => 'integer',
        'max_score' => 'integer',
    ];

    /**
     * Helper statis untuk mapping skor -> level.
     * Best practice: logic ringkas yang terkait langsung dengan entitas boleh di model.
     * (Kalau makin kompleks, pindahkan ke service class.)
     */
    public static function levelForScore(int $score): ?self
    {
        return static::query()
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();
    }

    public static function getByLevel(string $level): ?self
    {
        return static::query()
            ->where('level', $level)
            ->first();
    }

    /** Scope praktis */
    public function scopeLevel($q, string $level)
    {
        return $q->where('level', $level);
    }
}
