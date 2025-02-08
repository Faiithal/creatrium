<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['category' => 'Computer Programming']);
        Category::create(['category' => 'Information Technology']);
        Category::create(['category' => 'Digital Arts and Design']);
        Category::create(['category' => 'Automotive']);
        Category::create(['category' => 'Marine Electrical Training']);
        Category::create(['category' => 'Industrial Electrician']);
        Category::create(['category' => 'Science, Technology, Engineering, & Mathematics']);
    }
}
