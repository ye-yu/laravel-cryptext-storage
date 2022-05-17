<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('key_slots', function (Blueprint $table) {
            $table->id();
            $table->string('slot0')->nullable();
            $table->string('slot1')->nullable();
            $table->string('slot2')->nullable();
            $table->string('slot3')->nullable();
            $table->string('slot4')->nullable();
            $table->string('slot5')->nullable();
            $table->string('slot6')->nullable();
            $table->string('slot7')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('key_slots');
    }
};
