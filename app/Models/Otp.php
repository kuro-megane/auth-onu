<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    /** @use HasFactory<\Database\Factories\OtpFactory> */
    use HasFactory;

    public $timestamps = false;
    protected $table = 'otps';

    protected $fillable = [
        'login_id',
        'otp',
        'expires_at',
    ];

    protected $hidden = [
        'otp'
    ];
}
