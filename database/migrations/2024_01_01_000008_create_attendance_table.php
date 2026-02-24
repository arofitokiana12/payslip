<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id('attendance_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave'])->default('present');

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->unique(['employee_id', 'date']); // Un seul enregistrement par employé par jour
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
