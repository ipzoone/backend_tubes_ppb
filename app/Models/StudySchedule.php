<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudySchedule extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description',
        'day_of_week', 'start_time', 'end_time', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}