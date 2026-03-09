<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id('setting_id');
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // string, number, boolean, json
            $table->string('category'); // payroll, general, tax, etc.
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
