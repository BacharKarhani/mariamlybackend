<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'subscribed_at',
        'unsubscribed_at'
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime'
    ];

    // Scope for active subscriptions (not unsubscribed)
    public function scopeActive($query)
    {
        return $query->whereNull('unsubscribed_at');
    }

    // Scope for inactive subscriptions (unsubscribed)
    public function scopeInactive($query)
    {
        return $query->whereNotNull('unsubscribed_at');
    }

    // Method to unsubscribe
    public function unsubscribe()
    {
        $this->update([
            'unsubscribed_at' => now()
        ]);
    }

    // Method to resubscribe
    public function resubscribe()
    {
        $this->update([
            'unsubscribed_at' => null
        ]);
    }

    // Check if subscription is active
    public function isActive()
    {
        return is_null($this->unsubscribed_at);
    }
}
