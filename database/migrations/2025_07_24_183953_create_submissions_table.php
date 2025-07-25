<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('full_name');
            $table->string('email');
            $table->string('company_name');
            $table->string('alternative_company_name')->nullable();
            $table->foreignId('company_designation_id')->nullable()->constrained('company_designations')->onDelete('set null');
            $table->foreignId('jurisdiction_of_operation_id')->nullable()->constrained('countries')->onDelete('set null');

            $table->json('target_jurisdictions')->nullable();

            $table->integer('number_of_shares')->nullable();
            $table->boolean('are_all_shares_issued')->default(false);
            $table->integer('number_of_issued_shares')->nullable();
            $table->foreignId('share_value_id')->nullable()->constrained('share_values')->onDelete('set null');

            $table->json('shareholders')->nullable();
            $table->json('beneficial_owners')->nullable();
            $table->json('directors')->nullable();

            $table->string('status')->default('pending'); // optional: pending/approved/rejected/etc.

            $table->timestamp('submitted_at')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
