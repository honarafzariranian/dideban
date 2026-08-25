<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->string('code'); // کد حساب
            $table->string('name'); // نام حساب
            $table->string('name_fa'); // نام فارسی
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']); // نوع حساب
            $table->enum('subtype', ['current_asset', 'fixed_asset', 'current_liability', 'long_term_liability', 'equity', 'revenue', 'cost_of_goods', 'operating_expense', 'non_operating'])->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_group')->default(false); // آیا گروه حساب است
            $table->boolean('is_leaf')->default(true); // آیا حساب پایانی است
            $table->boolean('is_active')->default(true);
            $table->decimal('opening_balance', 15, 3)->default(0);
            $table->string('currency', 3)->default('IRR');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['organization_id', 'code']);
            $table->index(['organization_id', 'type']);
            $table->index(['organization_id', 'parent_id']);
            $table->index(['organization_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
