<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_maps', function (Blueprint $table) {
            $table->id();
            $table->text('map_image_url'); // URL de la imagen del mapa
            $table->json('markers')->nullable(); // Array JSON de pines [{x, y, label, description}]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_maps');
    }
};