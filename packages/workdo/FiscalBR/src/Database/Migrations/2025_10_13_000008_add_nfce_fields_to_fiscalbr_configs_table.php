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
            if (!Schema::hasColumn('fiscalbr_configs', 'serie_nfce')) {
                $table->string('serie_nfce', 3)->default('1');
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'ultimo_numero_nfce')) {
                $table->integer('ultimo_numero_nfce')->default(0);
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'csc')) {
                $table->string('csc', 100)->nullable();
            }
            if (!Schema::hasColumn('fiscalbr_configs', 'csc_id')) {
                $table->string('csc_id', 10)->default('1');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fiscalbr_configs', function (Blueprint $table) {
            if (Schema::hasColumn('fiscalbr_configs', 'serie_nfce')) {
                $table->dropColumn('serie_nfce');
            }
            if (Schema::hasColumn('fiscalbr_configs', 'ultimo_numero_nfce')) {
                $table->dropColumn('ultimo_numero_nfce');
            }
            if (Schema::hasColumn('fiscalbr_configs', 'csc')) {
                $table->dropColumn('csc');
            }
            if (Schema::hasColumn('fiscalbr_configs', 'csc_id')) {
                $table->dropColumn('csc_id');
            }
        });
    }
};

