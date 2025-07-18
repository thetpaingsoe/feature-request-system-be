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
        Schema::create('feature_requests', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key

            $table->string('title', 500); // String column for the title
            $table->text('description'); // Text column for the description

            $table->string('email', 255); // String column for the submitter's email

            // Datetime column for submission timestamp, automatically set on creation
            $table->timestamp('submitted_at')->useCurrent();

            // Enum column for status with default value
            $table->enum('status', ['pending', 'reviewed', 'approved', 'rejected'])->default('pending');

            $table->text('note')->nullable();

            $table->timestamps(); // Adds `created_at` and `updated_at` columns automatically
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_requests');
    }
};
