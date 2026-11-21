<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $primaryKey = 'id_event';

    protected $fillable = [
        'title',
        'description',
        'start_at',
        'end_at',
        'location',
        'quota',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
        'quota'    => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'id_event', 'id_event');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_event', 'id_event');
    }
}
