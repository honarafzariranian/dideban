<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['receipt', 'payment']); // نوع پرداخت
            $table->string('payment_number');
            $table->date('payment_date');
            $table->string('payee_type')->nullable(); // customer, supplier
            $table->unsignedBigInteger('payee_id')->nullable();
            $table->string('payee_name');
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 15, 3);
            $table->string('payment_method'); // cash, check, bank_transfer, card
            $table->string('reference_number')->nullable(); // شماره مرجع چک/انتقال
            $table->string('check_number')->nullable();
            $table->date('check_date')->nullable();
            $table->string('tracking_number')->nullable(); // شماره پیگیری
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'completed', 'bounced', 'cancelled']);
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['organization_id', 'payment_number']);
            $table->index(['organization_id', 'type']);
            $table->index(['organization_id', 'status']);
            $table->index(['payee_type', 'payee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
