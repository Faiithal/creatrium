<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Course::create(['course' => 'Industrial Automation and Mechatronics Technology (IAMT)']);
        Course::create(['course' => 'Industrial Electrical Technology (IET)']);
        Course::create(['course' => 'Digital Arts & Design (DAD)']);
        Course::create(['course' => 'Information Technology (IT)']);
        Course::create(['course' => 'Network System and Security Administration (NSSA)']);
        Course::create(['course' => 'Science, Technology, Engineering, Mathematics (STEM)']);
        Course::create(['course' => 'Computer Programming']);
        Course::create(['course' => 'Automotive']);
    }
}
