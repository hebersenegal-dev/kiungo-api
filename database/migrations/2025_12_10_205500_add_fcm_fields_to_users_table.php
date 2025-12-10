// database/migrations/add_fcm_fields_to_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 10)->default('fr')->after('email');
            }


            if (!Schema::hasColumn('users', 'fcm_token_updated_at')) {
                $table->timestamp('fcm_token_updated_at')->nullable()->after('fcm_token');
            }

            if (!Schema::hasColumn('users', 'device_type')) {
                $table->string('device_type', 20)->nullable()->after('fcm_token_updated_at');
            }

            if (!Schema::hasColumn('users', 'app_version')) {
                $table->string('app_version', 20)->nullable()->after('device_type');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove columns if they exist
            $columns = ['locale', 'fcm_token_updated_at', 'device_type', 'app_version'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
