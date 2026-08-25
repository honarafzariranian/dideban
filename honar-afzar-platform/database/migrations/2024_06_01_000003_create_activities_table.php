<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('activities', function (Blueprint $table) {
            $table->id(); $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('activityable_type'); $table->unsignedBigInteger('activityable_id');
            $table->enum('type', ['call', 'meeting', 'task', 'note', 'email']);
            $table->string('subject'); $table->text('description')->nullable();
            $table->datetime('due_date')->nullable(); $table->datetime('completed_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
            $table->index(['activityable_type', 'activityable_id']);
            $table->index(['organization_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('activities'); }
};
