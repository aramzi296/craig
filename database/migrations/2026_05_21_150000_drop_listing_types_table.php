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
        Schema::table('listings', function (Blueprint $table) {
            // Drop foreign key first if it exists (using array notation drops foreign key constraint safely)
            $table->dropForeign(['listing_type_id']);
            $table->dropColumn('listing_type_id');
        });

        Schema::dropIfExists('listing_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('listing_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->foreignId('listing_type_id')
                ->nullable()
                ->after('category_id')
                ->constrained()
                ->onDelete('set null');
        });
    }
};
