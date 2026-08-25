<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('correspondences', function (Blueprint $table) {
            $table->id(); $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['incoming', 'outgoing', 'internal']);
            $table->string('reference_number'); $table->date('date');
            $table->string('subject'); $table->text('body')->nullable();
            $table->string('sender_name')->nullable(); $table->string('recipient_name')->nullable();
            $table->foreignId('from_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('to_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['draft', 'pending', 'approved', 'sent', 'received', 'archived', 'rejected']);
            $table->date('deadline')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps(); $table->softDeletes();
            $table->unique(['organization_id', 'reference_number']);
            $table->index(['organization_id', 'type']);
            $table->index(['organization_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('correspondences'); }
};
