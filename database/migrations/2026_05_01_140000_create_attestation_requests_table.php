<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attestation_requests', function (Blueprint $table) {
            $table->id();

            // Identité
            $table->string('last_name');
            $table->string('first_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();

            // Cours / niveau (saisis librement par l'étudiant)
            $table->string('group_name'); // texte libre, à matcher manuellement
            $table->string('level', 5);   // A1, A2, B1, B2

            // Langue souhaitée du document
            $table->string('language', 10)->default('de_fr'); // de_fr | de | fr | en

            // Workflow
            $table->enum('status', ['pending', 'accepted', 'refused'])->default('pending');
            $table->text('refusal_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('attestation_id')->nullable()->constrained('attestations')->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attestation_requests');
    }
};
