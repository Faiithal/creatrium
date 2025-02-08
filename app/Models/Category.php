<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $timestamps = false;
    public function projects(){
        return $this->belongsToMany(Project::class, 'category_project');
        
    }
    use HasFactory;
}
