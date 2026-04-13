<?php

declare(strict_types=1);

use App\Enums\DealStage;
use App\Models\ClientProject;
use App\Models\Lead;
use App\Models\User;
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
        Schema::create('deals', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Lead::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ClientProject::class, 'project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->enum('stage', DealStage::cases())->default(DealStage::New)->index();
            $table->unsignedBigInteger('expected_value_cents')->default(0);
            $table->char('currency', 3)->default('EUR');
            $table->unsignedSmallInteger('probability')->nullable();
            $table->date('close_date')->nullable()->index();
            $table->string('lost_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignIdFor(User::class, 'owner_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
