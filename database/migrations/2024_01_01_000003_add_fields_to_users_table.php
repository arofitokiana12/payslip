<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * NOTE : Puisque l'authentification fonctionne déjà avec Laravel,
     * la table `users` existe probablement déjà avec la convention Laravel (id).
     * Cette migration MODIFIE la table existante en ajoutant les colonnes manquantes.
     * 
     * Si la table users n'existe pas encore, remplacez par Schema::create.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter les colonnes manquantes si elles n'existent pas
            if (!Schema::hasColumn('users', 'user_name')) {
                $table->string('user_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable();
                $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'active')) {
                $table->boolean('active')->default(true);
            }
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable();
                $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignIfExists(['role_id']);
            $table->dropForeignIfExists(['company_id']);
            $table->dropColumnIfExists(['user_name', 'role_id', 'active', 'company_id']);
        });
    }
};
