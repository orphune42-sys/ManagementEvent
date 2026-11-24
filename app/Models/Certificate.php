<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Registration;

class Certificate extends Model
{
    protected $primaryKey = 'certificate_id';

    protected $fillable = [
        'user_id',
        'registration_id',
        'recipient_name',
        'certificate_number',
        'issued_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function registration()
    {
        return $this->belongsTo(
            Registration::class,
            'registration_id',
            'registration_id'
        );
    }
}
