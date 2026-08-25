<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name'); $table->string('code')->nullable();
            $table->string('company_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable(); $table->string('phone')->nullable();
            $table->string('mobile')->nullable(); $table->text('address')->nullable();
            $table->string('city')->nullable(); $table->string('national_id')->nullable();
            $table->string('tax_number')->nullable();
            $table->decimal('credit_limit', 15, 3)->default(0);
            $table->decimal('current_balance', 15, 3)->default(0);
            $table->enum('status', ['lead', 'prospect', 'active', 'inactive'])->default('lead');
            $table->integer('score')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps(); $table->softDeletes();
            $table->unique(['organization_id', 'code']);
            $table->index(['organization_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('customers'); }
};
