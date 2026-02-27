<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class StudentFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a student user
        $this->student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);
        
        Sanctum::actingAs($this->student, ['student']);
    }

    public function test_student_can_create_profile()
    {
        $profileData = [
            'student_id_number' => '2023-001',
            'course' => 'Bachelor of Science in Information Technology',
            'major' => 'Web Development',
            'year_level' => 3,
            'section' => 'A',
            'company_name' => 'Tech Company Inc.',
            'company_address' => '123 Tech Street, City',
            'supervisor_name' => 'John Supervisor',
            'supervisor_contact' => '09123456789',
            'required_hours' => 486,
            'ojt_start_date' => now()->toDateString(),
            'ojt_end_date' => now()->addMonths(3)->toDateString(),
        ];

        $response = $this->postJson('/api/student/profile', $profileData);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $this->student->id,
            'student_id_number' => '2023-001',
            'course' => 'Bachelor of Science in Information Technology',
        ]);
    }

    public function test_student_cannot_create_duplicate_profile()
    {
        // Create first profile
        StudentProfile::factory()->create(['user_id' => $this->student->id]);

        $profileData = [
            'student_id_number' => '2023-002',
            'course' => 'Bachelor of Science in Computer Science',
            'year_level' => 3,
        ];

        $response = $this->postJson('/api/student/profile', $profileData);

        $response->assertStatus(400)
                ->assertJson(['message' => 'You already have a student profile.']);
    }

    public function test_student_can_view_profile()
    {
        $profile = StudentProfile::factory()->create(['user_id' => $this->student->id]);

        $response = $this->getJson('/api/student/profile');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'profile',
                    'progress' => [
                        'required_hours',
                        'rendered_hours',
                        'remaining_hours',
                        'completion_percentage',
                    ]
                ]);
    }

    public function test_student_can_time_in()
    {
        // Create profile first
        StudentProfile::factory()->create(['user_id' => $this->student->id]);

        $timeInData = [
            'activities_done' => 'Working on Laravel project development',
            'remarks' => 'Progress good',
        ];

        $response = $this->postJson('/api/student/time-in', $timeInData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('logs', [
            'user_id' => $this->student->id,
            'activities_done' => 'Working on Laravel project development',
        ]);
    }

    public function test_student_cannot_time_in_without_profile()
    {
        $timeInData = [
            'activities_done' => 'Working on Laravel project development',
        ];

        $response = $this->postJson('/api/student/time-in', $timeInData);

        $response->assertStatus(404)
                ->assertJson(['message' => 'Student profile not found. Please complete your profile first.']);
    }

    public function test_student_can_view_today_log()
    {
        $profile = StudentProfile::factory()->create(['user_id' => $this->student->id]);

        $response = $this->getJson('/api/student/today');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'log',
                    'can_time_in',
                    'can_time_out',
                ]);
    }

    public function test_student_can_view_logs()
    {
        $profile = StudentProfile::factory()->create(['user_id' => $this->student->id]);

        $response = $this->getJson('/api/student/logs');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'logs' => [
                        'data',
                        'current_page',
                        'per_page',
                        'total',
                    ],
                    'summary' => [
                        'total_logs',
                        'total_hours',
                        'required_hours',
                        'remaining_hours',
                        'completion_percentage',
                    ],
                ]);
    }

    public function test_non_student_cannot_access_student_routes()
    {
        // Create supervisor user
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        Sanctum::actingAs($supervisor, ['supervisor']);

        $response = $this->getJson('/api/student/profile');

        $response->assertStatus(403);
    }

    public function test_student_can_view_profile_summary()
    {
        $profile = StudentProfile::factory()->create(['user_id' => $this->student->id]);

        $response = $this->getJson('/api/student/profile/summary');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'profile',
                    'progress',
                    'log_statistics',
                    'recent_logs',
                ]);
    }
}
