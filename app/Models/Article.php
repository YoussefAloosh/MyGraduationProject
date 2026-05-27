<?php

namespace App\Models;

use App\Traits\MangesCloudinaryFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Article extends Model
{
    use MangesCloudinaryFiles;
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'cover_image',
        'cover_image_public_id',
        'status',
        'published_at',
    ];
    protected $casts = [
        'published_at' => 'datetime',
    ];

    // Auto-generate slug from title
    protected static function booted()
    {
        static::creating(function ($article) {
            $article->slug = Str::slug($article->title) . '-' . uniqid();
        });
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactionable');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}