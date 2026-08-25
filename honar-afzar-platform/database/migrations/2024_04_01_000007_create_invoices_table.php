<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['sales', 'purchase']); // نوع فاکتور
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('reference_type')->nullable(); // customer, supplier
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_name'); // نام مشتری/تأمین‌کننده
            $table->decimal('subtotal', 15, 3)->default(0);
            $table->decimal('tax_amount', 15, 3)->default(0);
            $table->decimal('discount_amount', 15, 3)->default(0);
            $table->decimal('total_amount', 15, 3)->default(0);
            $table->decimal('paid_amount', 15, 3)->default(0);
            $table->decimal('remaining_amount', 15, 3)->default(0);
            $table->enum('status', ['draft', 'pending', 'approved', 'paid', 'partial', 'overdue', 'cancelled']);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['organization_id', 'invoice_number']);
            $table->index(['organization_id', 'type']);
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'due_date']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
