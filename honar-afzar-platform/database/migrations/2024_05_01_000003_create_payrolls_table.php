<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('payroll_number');
            $table->string('title');
            $table->integer('month'); // 1-12
            $table->integer('year');
            $table->date('pay_date');
            $table->decimal('total_base_salary', 15, 3)->default(0);
            $table->decimal('total_allowances', 15, 3)->default(0);
            $table->decimal('total_deductions', 15, 3)->default(0);
            $table->decimal('total_insurance', 15, 3)->default(0);
            $table->decimal('total_tax', 15, 3)->default(0);
            $table->decimal('total_net_pay', 15, 3)->default(0);
            $table->integer('employee_count')->default(0);
            $table->enum('status', ['draft', 'pending', 'approved', 'paid', 'cancelled']);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['organization_id', 'payroll_number']);
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
