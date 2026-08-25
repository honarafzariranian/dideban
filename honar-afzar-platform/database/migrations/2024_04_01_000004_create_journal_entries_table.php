<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accounting_period_id')->constrained()->cascadeOnDelete();
            $table->string('entry_number'); // شماره سند
            $table->date('entry_date');
            $table->string('reference_type')->nullable(); // نوع مرجع
            $table->unsignedBigInteger('reference_id')->nullable(); // شماره مرجع
            $table->enum('type', ['general', 'receipt', 'payment', 'journal', 'opening', 'closing']); // نوع سند
            $table->text('description'); // شرح سند
            $table->decimal('total_debit', 15, 3)->default(0);
            $table->decimal('total_credit', 15, 3)->default(0);
            $table->enum('status', ['draft', 'pending', 'approved', 'posted', 'rejected', 'reversed']);
            $table->boolean('is_balanced')->default(false); // آیا سند موازنه است
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['organization_id', 'entry_number']);
            $table->index(['organization_id', 'entry_date']);
            $table->index(['organization_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
