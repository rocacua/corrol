<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_files', function (Blueprint $table) {
            $table->id();
            $table->text('file_path_or_url'); // URL de B2 o enlace externo
            $table->boolean('is_external')->default(false); // True si es solo enlace, False si se subió a B2
            $table->string('file_type')->nullable(); // 'image', 'pdf', 'document', 'audio', 'video', 'zip'
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_in_bytes')->nullable();
            $table->json('metadata')->nullable(); // Guardará árbol ZIP, resolución imagen, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_files');
    }
};