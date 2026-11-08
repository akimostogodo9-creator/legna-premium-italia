<?php

use App\Enums\ContactStatus;
use App\Enums\ContactSubject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Étape 4 §7.1 : demandes de renseignements uniquement (R-CONT-01/02) — aucune IP stockée
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 191)->index();
            $table->string('phone', 30)->nullable();
            $table->enum('subject', ContactSubject::values());
            $table->text('message');
            $table->timestamp('privacy_accepted_at');
            $table->enum('status', ContactStatus::values())->default(ContactStatus::New->value)->index();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
