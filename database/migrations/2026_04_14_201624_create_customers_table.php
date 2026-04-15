<?php

declare(strict_types=1);

use App\Enums\CustomerType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->index();
            $table->enum('type', CustomerType::cases())->default(CustomerType::B2B)->index();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->char('country_code', 2)->nullable()->index();
            $table->string('vat_id')->nullable();
            $table->string('tax_number')->nullable();
            $table->boolean('is_vat_exempt')->default(false);
            $table->text('vat_exemption_reason')->nullable();
            $table->json('billing_address')->nullable();
            $table->timestamps();
            $table->index('created_at');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
