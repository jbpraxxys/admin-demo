<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name', 'client_name', 'demo_password', 'created_by', 'status',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function getShareableLinkAttribute(): string
    {
        return config('app.url') . '/projects/' . $this->slug . '/index.html';
    }
}
