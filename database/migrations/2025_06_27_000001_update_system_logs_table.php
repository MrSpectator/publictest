<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('system_logs', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->uuid('request_id')->nullable();
            $table->float('execution_time')->nullable();
            $table->integer('memory_usage')->nullable();
            $table->json('metadata')->nullable();
            $table->text('stack_trace')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('system_logs', function (Blueprint $table) {
            $table->dropColumn([
                'category', 'user_id', 'ip_address', 'user_agent', 'request_id',
                'execution_time', 'memory_usage', 'metadata', 'stack_trace'
            ]);
        });
    }
}; 