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
        Schema::table('accessibility_widgets', function (Blueprint $blueprint) {
            $blueprint->string('position')->default('bottom-right')->after('is_active');
            $blueprint->string('logo')->nullable()->after('position');
            $blueprint->string('icon_trigger')->nullable()->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accessibility_widgets', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['position', 'logo', 'icon_trigger']);
        });
    }
};
