<?php

namespace Database\Factories;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    protected $model = StudentProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'student_id_number' => $this->faker->unique()->numerify('####-###'),
            'course' => $this->faker->randomElement([
                'Bachelor of Science in Information Technology',
                'Bachelor of Science in Computer Science',
                'Bachelor of Science in Computer Engineering',
            ]),
            'major' => $this->faker->optional()->randomElement([
                'Web Development',
                'Mobile Development',
                'Network Security',
                'Data Science',
            ]),
            'year_level' => $this->faker->numberBetween(1, 5),
            'section' => $this->faker->optional()->randomElement(['A', 'B', 'C', 'D']),
            'company_name' => $this->faker->optional()->company(),
            'company_address' => $this->faker->optional()->address(),
            'supervisor_name' => $this->faker->optional()->name(),
            'supervisor_contact' => $this->faker->optional()->phoneNumber(),
            'required_hours' => 486,
            'rendered_hours' => $this->faker->numberBetween(0, 200),
            'ojt_start_date' => $this->faker->optional()->date(),
            'ojt_end_date' => $this->faker->optional()->date(),
            'status' => $this->faker->randomElement(['pending', 'ongoing', 'completed', 'dropped']),
        ];
    }
}
