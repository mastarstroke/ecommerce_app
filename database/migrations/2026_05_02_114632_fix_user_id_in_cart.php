<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('carts')->whereNull('user_id')->update(['user_id' => null]);
        
        Schema::table('carts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('session_id');
        });
    }

    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['session_id']);
        });
    }
};