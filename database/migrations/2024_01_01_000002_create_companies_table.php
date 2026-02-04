<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id('company_id');
            $table->string('company_name');
            $table->date('date_creation')->nullable();
            $table->string('nif')->nullable();                  // Corrigé : STRING au lieu de INT
            $table->string('stat')->nullable();                 // Corrigé : STRING au lieu de INT
            $table->string('rcs')->nullable();                  // Corrigé : STRING au lieu de INT
            $table->string('logo')->nullable();
            $table->text('adress')->nullable();
            $table->string('email')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
