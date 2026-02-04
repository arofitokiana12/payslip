<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deduction_settings', function (Blueprint $table) {
            $table->id('deduction_setting_id');
            $table->string('deduction_setting_name');
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');   // Type de déduction
            $table->decimal('amount', 10, 2)->default(0);                            // Montant ou pourcentage
            $table->unsignedBigInteger('company_id')->nullable();                    // Liée à une entreprise
            $table->boolean('active')->default(true);

            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deduction_settings');
    }
};
