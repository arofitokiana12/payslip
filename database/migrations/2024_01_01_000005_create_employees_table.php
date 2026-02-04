<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id('employee_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('matricule')->unique();                 // Corrigé : STRING + UNIQUE
            $table->unsignedBigInteger('position_id')->nullable();
            $table->date('hire_date')->nullable();
            $table->enum('contract_type', ['CDI', 'CDD', 'stage', 'freelance', 'other'])->nullable();
            $table->date('contract_end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave', 'suspended'])->default('active');
            $table->boolean('active')->default(true);
            $table->decimal('base_salary', 12, 2)->default(0);    // Corrigé : DECIMAL au lieu de DOUBLE
            $table->unsignedBigInteger('company_id');

            // Foreign Keys
            $table->foreign('position_id')->references('position_id')->on('positions')->onDelete('set null');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
