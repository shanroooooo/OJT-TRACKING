<?php

namespace Database\Factories;

use App\Models\Log;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Log>
 */
class LogFactory extends Factory
{
    protected $model = Log::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $timeIn = $this->faker->dateTimeBetween('-30 days', 'now');
        $timeOut = $this->faker->optional(0.8)->dateTimeBetween($timeIn, '+8 hours');
        
        $hoursRendered = 0;
        $minutesRendered = 0;
        
        if ($timeOut) {
            $carbonTimeIn = \Carbon\Carbon::instance($timeIn);
            $carbonTimeOut = \Carbon\Carbon::instance($timeOut);
            $minutesRendered = $carbonTimeIn->diffInMinutes($carbonTimeOut);
            $hoursRendered = round($minutesRendered / 60, 2);
        }

        return [
            'user_id' => User::factory()->student(),
            'student_profile_id' => StudentProfile::factory(),
            'log_date' => $timeIn->format('Y-m-d'),
            'time_in' => $timeIn->format('H:i:s'),
            'time_out' => $timeOut ? $timeOut->format('H:i:s') : null,
            'hours_rendered' => $hoursRendered,
            'minutes_rendered' => $minutesRendered,
            'activities_done' => $this->faker->sentence(10),
            'remarks' => $this->faker->optional()->sentence(5),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'reviewed_by' => $this->faker->optional()->randomElement([User::factory()->supervisor(), null]),
            'reviewed_at' => $this->faker->optional()->dateTime(),
            'review_notes' => $this->faker->optional()->sentence(3),
        ];
    }
}
