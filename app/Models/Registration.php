<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model {
    protected $table = 'registrations';
    protected $primaryKey = 'registration_id';

    protected $fillable = [
        'user_id',
        'id_event',
        'registration_code',
        'registration_date',
        'registration_status',
        'payment_status',
        'total_payment'
    ];

    public function user() {
        return $this->belongsTo(
            User::class,'user_id', 'user_id'
        );
    }

    public function event() {
        return $this->belongsTo(
            Event::class, 'id_event', 'id_event'
        );
    }
}