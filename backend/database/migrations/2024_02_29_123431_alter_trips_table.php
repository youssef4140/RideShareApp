<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            // Drop the existing boolean columns
            $table->dropColumn(['is_accepted', 'is_started', 'is_complete']);
            
            // Add a new enum column called 'status'
            $table->enum('status', ['pending', 'accepted', 'started', 'completed'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            // Drop the 'status' enum column
            $table->dropColumn('status');
            
            // Re-add the boolean columns
            $table->boolean('is_accepted')->default(false);
            $table->boolean('is_started')->default(false);
            $table->boolean('is_complete')->default(false);
        });
    }
};
