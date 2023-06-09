<?php

use App\Objects\MessageTypes;
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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->enum('type', MessageTypes::keys())->default(MessageTypes::chat());
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('room_id')->index();
            $table->timestamp('created_at')->index();
            $table->index(['created_at', 'room_id']);
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
