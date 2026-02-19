<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'lo_number',
        'spbu_number',
        'ship_to',
        'quantity',
        'product_type',
        'distance_km',
        'status',
        'driver_id',
        'driver_name',
        'karnet_number',
        'taken_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'distance_km' => 'decimal:2',
            'taken_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // Relasi ke User (driver)
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    // Relasi ke DeliveryPhoto
    public function deliveryPhotos()
    {
        return $this->hasMany(DeliveryPhoto::class);
    }

    // Relasi khusus foto checkin
    public function checkinPhotos()
    {
        return $this->hasMany(DeliveryPhoto::class)->where('photo_type', 'checkin');
    }

    // Relasi khusus foto checkout
    public function checkoutPhotos()
    {
        return $this->hasMany(DeliveryPhoto::class)->where('photo_type', 'checkout');
    }

    // Query scope
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Append data tambahan ke JSON
    protected $appends = ['driver_info', 'photo_urls'];

    // Accessor untuk info driver lengkap
    public function getDriverInfoAttribute()
    {
        if (!$this->driver_id) {
            return null;
        }

        // Kalau relasi driver belum di-load, load sekarang
        if (!$this->relationLoaded('driver')) {
            $this->load('driver');
        }

        $driver = $this->driver;
        if (!$driver) {
            return null;
        }

        return [
            'id' => $driver->id,
            'name' => $driver->name,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'employee_id' => $driver->employee_id,
            'profile_photo' => $driver->profile_photo 
                ? asset('storage/' . $driver->profile_photo) 
                : null,
        ];
    }

    // Accessor untuk URL foto checkin dan checkout
    public function getPhotoUrlsAttribute()
    {
        // Kalau relasi deliveryPhotos belum di-load, load sekarang
        if (!$this->relationLoaded('deliveryPhotos')) {
            $this->load('deliveryPhotos');
        }

        $photos = $this->deliveryPhotos;

        $checkinPhotos = $photos->where('photo_type', 'checkin')->map(function($photo) {
            return [
                'id' => $photo->id,
                'url' => asset('storage/' . $photo->photo_path),
                'latitude' => $photo->latitude,
                'longitude' => $photo->longitude,
                'address' => $photo->address,
                'taken_at' => $photo->photo_taken_at?->format('Y-m-d H:i:s'),
            ];
        })->values();

        $checkoutPhotos = $photos->where('photo_type', 'checkout')->map(function($photo) {
            return [
                'id' => $photo->id,
                'url' => asset('storage/' . $photo->photo_path),
                'latitude' => $photo->latitude,
                'longitude' => $photo->longitude,
                'address' => $photo->address,
                'taken_at' => $photo->photo_taken_at?->format('Y-m-d H:i:s'),
            ];
        })->values();

        return [
            'checkin' => $checkinPhotos,
            'checkout' => $checkoutPhotos,
        ];
    }
}