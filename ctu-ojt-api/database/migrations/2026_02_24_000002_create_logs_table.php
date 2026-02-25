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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();

            // Who submitted the log
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Which student profile this log belongs to
            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Daily log details
            $table->date('log_date');
            $table->time('time_in');
            $table->time('time_out')->nullable();
            $table->decimal('hours_rendered', 5, 2)->default(0.00); // e.g. 7.50 hrs
            $table->unsignedSmallInteger('minutes_rendered')->default(0); // raw minutes for easy summing

            // Activities & remarks
            $table->text('activities_done');
            $table->text('remarks')->nullable();

            // Approval workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Who approved/rejected and when
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            $table->timestamps();

            // Prevent duplicate log entries per student per date
            $table->unique(['student_profile_id', 'log_date']);

            // Indexes for common queries
            $table->index(['user_id', 'log_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
