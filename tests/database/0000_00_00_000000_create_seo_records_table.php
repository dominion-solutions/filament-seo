<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A stand-in for whatever model the host application describes.
     *
     * The package's tests need a real table to hang a morph relation off, but
     * nothing about it should be SEO specific.
     */
    public function up(): void
    {
        Schema::create('seo_records', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('tagline')->nullable();
            $table->string('screenshot_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_records');
    }
};
