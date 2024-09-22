<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plannings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medecin_id');
            //$table->unsignedBigInteger('creneau_id');
            $table->string('datePlanning'); // exemple: 'lundi', 'mardi'
            $table->timestamps();

            // Foreign key
            $table->foreign('medecin_id')->references('id')->on('users')->onDelete('cascade');
            //$table->foreign('creneau_id')->references('id')->on('creneaus')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plannings');
    }
};
