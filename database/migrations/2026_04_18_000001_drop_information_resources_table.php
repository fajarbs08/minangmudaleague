<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('information_resources');
    }

    public function down(): void
    {
        // Tabel ini sengaja tidak dipulihkan karena modul pusat informasi sudah dihapus total.
    }
};
