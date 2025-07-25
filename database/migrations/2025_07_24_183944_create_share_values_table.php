<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_values', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 10);   // e.g. USD, THB
            $table->decimal('amount', 15, 2); // e.g. 1.00, 0.10
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_values');
    }
};
