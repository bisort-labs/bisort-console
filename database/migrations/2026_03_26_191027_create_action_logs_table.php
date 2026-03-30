<?php

use App\Enums\ActionLogType;
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
        Schema::create('action_logs', function (Blueprint $table): void {
            $table->id();
            $table->enum('type', ActionLogType::cases())->default(ActionLogType::System);
            $table->string('title');
            $table->text('body')->nullable();
            $table->morphs('actionable');
            $table->foreignIdFor(User::class, 'actor_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('happened_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_logs');
    }
};
