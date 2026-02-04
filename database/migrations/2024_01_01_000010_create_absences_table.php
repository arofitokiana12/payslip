<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id('absence_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('absence_date');
            $table->unsignedBigInteger('leave_id')->nullable();          // Corrigé : FK vers leaves
            $table->enum('leave_type', ['annual', 'sick', 'maternity', 'unpaid', 'other'])->nullable();
            $table->enum('reason', ['illness', 'personal', 'emergency', 'no_reason'])->nullable();
            $table->text('note')->nullable();

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('leave_id')->references('leave_id')->on('leaves')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
