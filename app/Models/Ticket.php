<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    public const STATUS_PENDIENTE = 'pendiente';
    public const STATUS_ASIGNADO_TI = 'asignado_ti';
    public const STATUS_EN_CURSO = 'en_curso';
    public const STATUS_LEVANTADO = 'levantado';
    public const STATUS_TESTING = 'testing'; 

    public const STATUSES = [
        self::STATUS_PENDIENTE   => 'Pendiente',
        self::STATUS_ASIGNADO_TI => 'Asignado a TI',
        self::STATUS_EN_CURSO    => 'En curso',
        self::STATUS_LEVANTADO   => 'Levantado',
        self::STATUS_TESTING     => 'Testing (Conforme)',
    ];

    public const TRANSITIONS = [
        self::STATUS_PENDIENTE   => [self::STATUS_ASIGNADO_TI],
        self::STATUS_ASIGNADO_TI => [self::STATUS_EN_CURSO],
        self::STATUS_EN_CURSO    => [self::STATUS_LEVANTADO],
        self::STATUS_LEVANTADO   => [self::STATUS_TESTING],
        self::STATUS_TESTING     => [self::STATUS_EN_CURSO], 
    ];

    protected $fillable = [
        'user_id',
        'issue_type',
        'module',
        'affected_user',
        'description',
        'attachment',
        'status',
        'assigned_to',
        'priority',
        'due_date',
        'started_at',
        'resolved_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected $appends = ['sla_color'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function statusHistories()
    {
        return $this->hasMany(TicketStatusHistory::class)->orderBy('created_at');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function getSlaColorAttribute(): ?string
    {
        if (!$this->due_date || $this->status === self::STATUS_TESTING) {
            return null;
        }

        $now = now();
        if ($now->greaterThan($this->due_date)) {
            return 'red';
        }

        $totalWindow = $this->created_at->diffInMinutes($this->due_date);
        $remaining = $now->diffInMinutes($this->due_date);
        
        if ($totalWindow > 0 && ($remaining / $totalWindow) < 0.25) {
            return 'yellow';
        }

        return 'green';
    }
}