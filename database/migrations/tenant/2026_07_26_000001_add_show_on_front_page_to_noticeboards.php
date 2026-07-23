<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('noticeboards', function (Blueprint $table) {
            if (! Schema::hasColumn('noticeboards', 'show_on_front_page')) {
                // When true, the notice is pinned as a prominent "out of office"
                // banner across the top of the dealer dashboard (e.g. extended
                // holiday closure), in addition to the normal notices list.
                $table->boolean('show_on_front_page')->default(false)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('noticeboards', function (Blueprint $table) {
            if (Schema::hasColumn('noticeboards', 'show_on_front_page')) {
                $table->dropColumn('show_on_front_page');
            }
        });
    }
};
