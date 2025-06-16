<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('user_data_exports', function (Blueprint $table) {
                $table->date('valid_until')->type('datetime')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
    }
};
