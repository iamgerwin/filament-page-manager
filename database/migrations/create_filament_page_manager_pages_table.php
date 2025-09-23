<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('filament-page-manager.tables.pages', 'fpm_pages');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(true)->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('template');
            $table->string('name');
            $table->json('slug')->nullable();
            $table->json('seo')->nullable();
            $table->json('data')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();

            $table->index(['template', 'active']);
            $table->index(['parent_id', 'sort_order']);
            $table->foreign('parent_id')->references('id')->on($table->getTable())->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('filament-page-manager.tables.pages', 'fpm_pages'));
    }
};
