<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Remove duplicate carts - keep only the most recent one per user
        $duplicateUsers = DB::table('carts')
            ->select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');
            
        foreach ($duplicateUsers as $userId) {
            $carts = DB::table('carts')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
                
            $keepCart = $carts->first();
            $deleteCarts = $carts->slice(1);
            
            foreach ($deleteCarts as $cart) {
                // Move items to kept cart
                DB::table('cart_items')
                    ->where('cart_id', $cart->id)
                    ->update(['cart_id' => $keepCart->id]);
                    
                // Delete empty cart
                DB::table('carts')->where('id', $cart->id)->delete();
            }
        }
        
        // Remove duplicate session carts
        $duplicateSessions = DB::table('carts')
            ->select('session_id')
            ->whereNotNull('session_id')
            ->whereNull('user_id')
            ->groupBy('session_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('session_id');
            
        foreach ($duplicateSessions as $sessionId) {
            $carts = DB::table('carts')
                ->where('session_id', $sessionId)
                ->whereNull('user_id')
                ->orderBy('created_at', 'desc')
                ->get();
                
            $keepCart = $carts->first();
            $deleteCarts = $carts->slice(1);
            
            foreach ($deleteCarts as $cart) {
                DB::table('cart_items')
                    ->where('cart_id', $cart->id)
                    ->update(['cart_id' => $keepCart->id]);
                    
                DB::table('carts')->where('id', $cart->id)->delete();
            }
        }
        
        // Add unique constraint to prevent future duplicates
        Schema::table('carts', function (Blueprint $table) {
            $table->unique('user_id')->whereNotNull('user_id');
            $table->unique('session_id')->whereNotNull('session_id');
        });
    }

    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropUnique(['session_id']);
        });
    }
};