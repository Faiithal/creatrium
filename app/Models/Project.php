<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user_likes() {
        return $this->belongsToMany(User::class,'likes')->withPivot('created_at');
    }

    public function user_favorites() {
        return $this->belongsToMany(User::class,'favorites')->withPivot('created_at');
    }

    public function user_comments() {
        return $this->belongsToMany(User::class,'comments')->withPivot('created_at', 'content');
    }

    protected $guarded = [];

}
