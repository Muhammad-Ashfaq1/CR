<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Log an activity event.
     *
     * @param  array{project_id?: int|null, event: string, description?: string, properties?: array<string, mixed>}  $attributes
     */
    public static function log(array $attributes, ?Model $subject = null): ActivityLog
    {
        return ActivityLog::create([
            'project_id' => $attributes['project_id'] ?? null,
            'user_id' => auth()->id(),
            'event' => $attributes['event'],
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $attributes['properties'] ?? null,
            'description' => $attributes['description'] ?? null,
        ]);
    }
}
