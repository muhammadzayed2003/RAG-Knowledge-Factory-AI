<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_sources', function (Blueprint $table) {
            $table->id();

            $table->string('url', 2048);

            $table->string('host');

            $table->unsignedInteger('page_count')
                ->default(0);

            $table->unsignedInteger('chunk_count')
                ->default(0);

            $table->string('status')
                ->default('pending');

            $table->text('error_message')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_sources');
    }
};