<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';

    protected $primaryKey = 'id_event';

    protected $fillable = [
        'nama_event',
        'deskripsi',
        'tanggal_event',
        'lokasi',
        'kuota_peserta',
        'status_event',
    ];
}
