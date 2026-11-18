<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $primaryKey = 'id_tiket';

    protected $fillable = [
        'id_event',
        'nama_tiket',
        'harga',
        'kuota_tiket',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }
}