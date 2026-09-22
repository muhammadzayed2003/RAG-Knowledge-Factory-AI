<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_api_keys', function (Blueprint $table) {
            $table->id();

            $table->string('name')->default('External Chat API');

            $table->string('key_prefix');

            $table->string('key_hash')->unique();

            $table->boolean('is_active')->default(true);

            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_api_keys');
    }
};