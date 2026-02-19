<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'photo_type',
        'photo_path',
        'latitude',
        'longitude',
        'address',
        'photo_taken_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'photo_taken_at' => 'datetime',
        ];
    }

    // Relasi
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Append photo_url ke JSON
    protected $appends = ['photo_url'];

    // Accessor untuk URL foto yang bisa diakses publik
    public function getPhotoUrlAttribute()
    {
        if (!$this->photo_path) {
            return null;
        }

        return asset('storage/' . $this->photo_path);
    }
}