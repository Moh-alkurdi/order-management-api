<?php

namespace App\Domains\AuditLog\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'user_id'
    ];

    // تحويل المصفوفات تلقائياً إلى JSON عند الحفظ والعكس
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}