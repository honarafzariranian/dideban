<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_salary', 15, 3)->default(0);
            $table->decimal('allowances', 15, 3)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_amount', 15, 3)->default(0);
            $table->decimal('bonus', 15, 3)->default(0);
            $table->decimal('leave_deduction', 15, 3)->default(0);
            $table->decimal('insurance_ee', 15, 3)->default(0); // Employee share
            $table->decimal('insurance_er', 15, 3)->default(0); // Employer share
            $table->decimal('tax', 15, 3)->default(0);
            $table->decimal('other_deductions', 15, 3)->default(0);
            $table->decimal('gross_pay', 15, 3)->default(0);
            $table->decimal('net_pay', 15, 3)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['payroll_id']);
            $table->index(['employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
