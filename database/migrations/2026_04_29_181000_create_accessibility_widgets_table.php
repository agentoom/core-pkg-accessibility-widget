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
        Schema::create('accessibility_widgets', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $blueprint->string('name');
            $blueprint->string('slug')->unique();
            $blueprint->boolean('is_active')->default(true);
            $blueprint->json('settings')->nullable();
            $blueprint->json('allowed_domains')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessibility_widgets');
    }
};
