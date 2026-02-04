<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id('payslip_id');
            $table->unsignedBigInteger('employee_id');
            $table->integer('period_month');
            $table->integer('period_year');
            $table->decimal('gross_amount', 12, 2)->default(0);       // Salaire brut
            $table->decimal('total_deductions', 12, 2)->default(0);   // Total des déductions
            $table->decimal('net_amount', 12, 2)->default(0);         // Salaire net
            $table->enum('status', ['draft', 'validated', 'paid'])->default('draft');

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');

            // Contrainte : un seul payslip par employé par mois/année
            $table->unique(['employee_id', 'period_month', 'period_year']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
