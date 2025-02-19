<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rosters', function (Blueprint $table) {
            $table->id();
            $table->string('source_file');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roster_id')->constrained();
            $table->enum('event_type', ['DO', 'SBY', 'FLT', 'CI', 'CO', 'UNK']);
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('location', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('flight_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('flight_number', 10);
            $table->string('departure_airport', 10);
            $table->string('arrival_airport', 10);
            $table->timestamp('std')->nullable();
            $table->timestamp('sta')->nullable();
            $table->string('aircraft_reg', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('checkin_checkout_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('linked_flight_id')->constrained('flight_events')->onDelete('cascade');
            $table->string('airport_code', 10);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkin_checkout_events');  // reverse order of creation due to foreign key constraints
        Schema::dropIfExists('flight_events');
        Schema::dropIfExists('events');
        Schema::dropIfExists('rosters');
    }
};
