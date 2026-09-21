<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip')->unique()->nullable();
            $table->string('role')->default('staff');
            $table->string('shift')->nullable();
            $table->string('gudang')->nullable();
            $table->date('join_date')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nip',
                'role',
                'shift',
                'gudang',
                'join_date',
                'last_login_at',
                'is_active',
            ]);
        });
    }
};