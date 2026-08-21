<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('privacy', ['public', 'private'])->default('public');
            $table->string('type'); // 'file', 'link', 'map', 'sheet', 'diary'
            
            // Filtros de búsqueda
            $table->string('game')->nullable()->index();
            $table->string('campaign')->nullable()->index();
            $table->string('author')->nullable()->index();
            $table->json('tags')->nullable();

            // Polimorfismo Eloquent
            $table->nullableMorphs('resourceable');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};