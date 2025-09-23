<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('filament-page-manager.tables.regions', 'fpm_regions');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('template');
            $table->string('name')->unique();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index('template');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('filament-page-manager.tables.regions', 'fpm_regions'));
    }
};
