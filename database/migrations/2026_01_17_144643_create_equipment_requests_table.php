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
        Schema::create('equipment_requests', function (Blueprint $table) {
            $table->id();

            // HR fills these fields
            $table->string('employee_name');
            $table->string('department');
            $table->enum('position', ['employe', 'apprenti', 'stagiaire']);

            // IT Manager fills these fields
            $table->text('equipment_description')->nullable();
            $table->date('deadline')->nullable();

            // Status tracking
            $table->enum('status', ['en_attente', 'en_cours', 'termine'])->default('en_attente');

            // Relationships
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // HR user
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // Technician

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_requests');
    }
};
