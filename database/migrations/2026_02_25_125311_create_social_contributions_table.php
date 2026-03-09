<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('social_contributions', function (Blueprint $table) {
            $table->id('contribution_id');
            $table->string('name'); // CNaPS, OSTIE, etc.
            $table->string('code')->unique(); // cnaps, ostie
            $table->decimal('employee_rate', 5, 2); // Taux employé (en %)
            $table->decimal('employer_rate', 5, 2); // Taux employeur (en %)
            $table->decimal('ceiling', 12, 2)->nullable(); // Plafond de cotisation
            $table->boolean('active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('social_contributions');
    }
};
