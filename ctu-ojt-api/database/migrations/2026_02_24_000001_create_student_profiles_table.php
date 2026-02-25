<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();

            // Foreign key — one profile per student user
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Student identification
            $table->string('student_id_number')->unique()->comment('School-issued student ID');
            $table->string('course');
            $table->string('major')->nullable();
            $table->unsignedTinyInteger('year_level'); // 1–5
            $table->string('section')->nullable();

            // OJT details
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_contact')->nullable();

            // Hours tracking
            $table->unsignedSmallInteger('required_hours')->default(486);
            $table->decimal('rendered_hours', 7, 2)->default(0.00); // accumulates from logs

            // OJT period
            $table->date('ojt_start_date')->nullable();
            $table->date('ojt_end_date')->nullable();

            // Status
            $table->enum('status', ['pending', 'ongoing', 'completed', 'dropped'])
                ->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
