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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('level', [
                'emergency',
                'alert', 
                'critical',
                'error',
                'warning',
                'notice',
                'info',
                'debug'
            ])->index();
            $table->enum('category', [
                'authentication',
                'api',
                'database',
                'email',
                'system',
                'security',
                'performance',
                'user_action'
            ])->default('system')->index();
            $table->text('message');
            $table->json('context')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('request_id', 36)->nullable()->index();
            $table->float('execution_time')->nullable();
            $table->bigInteger('memory_usage')->nullable();
            $table->longText('stack_trace')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['level', 'category']);
            $table->index(['created_at', 'level']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
}; 