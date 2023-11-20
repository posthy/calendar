<?php

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
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('recurrence', ['no_repeat', 'every_week', 'odd_weeks', 'even_weeks']);
            $table->string('weekday', 20)->nullable();
            $table->string('daytime', 15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};
