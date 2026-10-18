<?php

namespace App\Models;

use Database\Factories\OtpFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $expires_at
 */
class Otp extends Model
{
    /** @use HasFactory<OtpFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'otps';

    protected $fillable = [
        'login_id',
        'otp',
        'expires_at',
    ];

    protected $hidden = [
        'otp',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
