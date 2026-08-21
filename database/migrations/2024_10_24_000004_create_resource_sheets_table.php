<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_sheets', function (Blueprint $table) {
            $table->id();
            $table->json('content'); // JSON estructurado con los bloques de Editor.js
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_sheets');
    }
};