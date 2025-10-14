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
        Schema::table('fiscalbr_configs', function (Blueprint $table) {
            $table->string('serie_nfce', 3)->default('1')->after('serie_nfe');
            $table->integer('ultimo_numero_nfce')->default(0)->after('ultimo_numero_nfe');
            $table->string('csc', 100)->nullable()->after('ultimo_numero_nfce');
            $table->string('csc_id', 10)->default('1')->after('csc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fiscalbr_configs', function (Blueprint $table) {
            $table->dropColumn(['serie_nfce', 'ultimo_numero_nfce', 'csc', 'csc_id']);
        });
    }
};

