<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id('advance_id');
            $table->unsignedBigInteger('employee_id');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->enum('repayment_status', ['pending', 'partial', 'completed'])->default('pending');
            $table->timestamps();

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_advances');
    }
};
