<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    public function profile(){
        return $this->hasOne(related: Profile::class);
    }
    public function projects(){
        return $this->hasMany(Project::class, 'user_id');
    }
    public function liked_projects() {
        return $this->belongsToMany(Project::class, 'likes')->withPivot('created_at');
    }
    public function favorited_projects() {
        return $this->belongsToMany(Project::class,'favorites')->withPivot('created_at');
    }
    
    public function commented_projects() {
        return $this->belongsToMany(project::class,'comments')->withPivot('created_at', 'content');
    }
    
    public function viewed_projects() {
        return $this->belongsToMany(project::class,'view_history')->withPivot('created_at');
    }

    public function course() {
        return $this->belongsToMany(Course::class);
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
