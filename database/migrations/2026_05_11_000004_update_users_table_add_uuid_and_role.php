<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql' && Schema::hasColumn('users', 'id')) {
            $type = Schema::getColumnType('users', 'id');

            if ($type === 'integer' || $type === 'bigint') {
                Schema::table('users', function (Blueprint $table) {
                    $table->uuid('uuid')->nullable()->first();
                });

                DB::table('users')->orderBy('id')->each(function ($user) {
                    DB::table('users')->where('id', $user->id)->update(['uuid' => (string) Str::uuid()]);
                });

                Schema::table('users', function (Blueprint $table) {
                    $table->uuid('uuid')->nullable(false)->change();
                });

                Schema::table('users', function (Blueprint $table) {
                    $table->dropPrimary('users_pkey');
                    $table->dropColumn('id');
                });

                Schema::table('users', function (Blueprint $table) {
                    $table->renameColumn('uuid', 'id');
                    $table->primary('id');
                });
            }
        }

        if (!Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('role_id')->nullable()->after('id');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
