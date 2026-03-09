<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payslip_items', function (Blueprint $table) {
            $table->id('payslip_item_id');
            $table->unsignedBigInteger('payslip_id');
            $table->enum('item_type', ['earning', 'deduction', 'tax']);
            $table->string('item_name');
            $table->decimal('amount', 12, 2);

            $table->foreign('payslip_id')->references('payslip_id')->on('payslips')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payslip_items');
    }
};
