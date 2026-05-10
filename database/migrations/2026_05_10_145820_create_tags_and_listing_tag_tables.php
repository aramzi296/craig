<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
        });

        Schema::create('listing_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Clone data from categories to tags
        DB::statement('INSERT INTO tags (id, name, slug, icon, sort_order, is_approved, created_at, updated_at) SELECT id, name, slug, icon, sort_order, is_approved, created_at, updated_at FROM categories');

        // Clone data from category_listing to listing_tag
        DB::statement('INSERT INTO listing_tag (tag_id, listing_id, created_at, updated_at) SELECT category_id, listing_id, created_at, updated_at FROM category_listing');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_tag');
        Schema::dropIfExists('tags');
    }
};
