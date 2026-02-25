<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id_number',
        'course',
        'major',
        'year_level',
        'section',
        'company_name',
        'company_address',
        'supervisor_name',
        'supervisor_contact',
        'required_hours',
        'rendered_hours',
        'ojt_start_date',
        'ojt_end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'ojt_start_date' => 'date',
            'ojt_end_date' => 'date',
            'rendered_hours' => 'float',
            'required_hours' => 'integer',
        ];
    }

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /** Remaining hours before OJT completion */
    public function getRemainingHoursAttribute(): float
    {
        return max(0, $this->required_hours - $this->rendered_hours);
    }

    /** Percentage completion */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->required_hours <= 0)
            return 0;
        return min(100, round(($this->rendered_hours / $this->required_hours) * 100, 2));
    }
}
