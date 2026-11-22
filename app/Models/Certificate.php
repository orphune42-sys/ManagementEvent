<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $primaryKey = 'certificate_id';

    protected $fillable = [
        'registration_id',
        'certificate_number',
        'issued_at',
        'file_path',
        'status',
    ];

    public function registration()
    {
        return $this->belongsTo(
            Registration::class,
            'registration_id',
            'registration_id'
        );
    }
}
