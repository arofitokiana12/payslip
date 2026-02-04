<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime', function (Blueprint $table) {
            $table->id('overtime_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->decimal('hours', 5, 2);                      // Corrigé : DECIMAL pour supporter 1.5h etc.

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime');
    }
};
