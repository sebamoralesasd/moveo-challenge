<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('external_hash')->unique();
            $table->string('external_id');
            $table->integer('guest_count');
            $table->string('sector');
            // TODO:shouldnt we use default timestamp?
            // $table->timestamp('redeemed_at');

            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
