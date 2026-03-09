<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tax_brackets', function (Blueprint $table) {
            $table->id('bracket_id');
            $table->decimal('min_salary', 12, 2);
            $table->decimal('max_salary', 12, 2)->nullable();
            $table->decimal('tax_rate', 5, 2); // En pourcentage (ex: 5.00 = 5%)
            $table->decimal('fixed_amount', 12, 2)->default(0);
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tax_brackets');
    }
};
