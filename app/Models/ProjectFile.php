<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    public $timestamps = false;

    protected $fillable = ['project_id', 'filename', 'path', 'size', 'uploaded_by', 'uploaded_at'];

    protected $casts = ['uploaded_at' => 'datetime'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
