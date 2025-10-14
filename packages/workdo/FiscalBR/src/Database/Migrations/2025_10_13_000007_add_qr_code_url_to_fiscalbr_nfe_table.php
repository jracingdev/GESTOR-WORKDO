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
        Schema::table('fiscalbr_nfe', function (Blueprint $table) {
            $table->text('qr_code_url')->nullable()->after('xml_cancelamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fiscalbr_nfe', function (Blueprint $table) {
            $table->dropColumn('qr_code_url');
        });
    }
};

