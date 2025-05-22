<?php

use App\Models\Counter;
use App\Models\Ticket;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->text('remarks')->nullable();
            $table->foreignIdFor(Ticket::class)->unique()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Counter::class)->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class)->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
