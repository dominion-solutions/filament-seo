<?php

use DominionSolutions\FilamentSeo\Support\SeoData;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seos', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->string('image_alt')->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->string('robots')->default(SeoData::ROBOTS_INDEX_FOLLOW);
            $table->string('type')->default(SeoData::TYPE_WEBSITE);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seos');
    }
};
