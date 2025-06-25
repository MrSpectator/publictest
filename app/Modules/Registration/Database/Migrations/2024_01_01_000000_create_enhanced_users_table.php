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
        Schema::table('users', function (Blueprint $table) {
            // Add new fields to existing users table
            $table->string('username', 50)->unique()->nullable()->after('email');
            $table->string('phone', 20)->unique()->nullable()->after('username');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->enum('gender', [
                'male',
                'female', 
                'other',
                'prefer_not_to_say'
            ])->nullable()->after('date_of_birth');
            $table->string('profile_picture')->nullable()->after('gender');
            $table->text('bio')->nullable()->after('profile_picture');
            $table->string('website')->nullable()->after('bio');
            $table->string('location')->nullable()->after('website');
            $table->string('timezone', 50)->nullable()->after('location');
            $table->string('language', 10)->nullable()->after('timezone');
            $table->boolean('is_active')->default(true)->after('language');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->timestamp('last_login_at')->nullable()->after('phone_verified_at');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->string('registration_ip', 45)->nullable()->after('last_login_ip');
            $table->enum('registration_source', [
                'web',
                'mobile',
                'api',
                'social',
                'invite'
            ])->default('web')->after('registration_ip');
            $table->json('preferences')->nullable()->after('registration_source');
            $table->json('metadata')->nullable()->after('preferences');
            $table->softDeletes()->after('updated_at');
            
            // Add indexes for better performance
            $table->index(['username']);
            $table->index(['phone']);
            $table->index(['is_active']);
            $table->index(['email_verified_at']);
            $table->index(['registration_source']);
            $table->index(['created_at']);
            $table->index(['last_login_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove all added columns
            $table->dropIndex(['username']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['email_verified_at']);
            $table->dropIndex(['registration_source']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['last_login_at']);
            
            $table->dropColumn([
                'username',
                'phone',
                'date_of_birth',
                'gender',
                'profile_picture',
                'bio',
                'website',
                'location',
                'timezone',
                'language',
                'is_active',
                'phone_verified_at',
                'last_login_at',
                'last_login_ip',
                'registration_ip',
                'registration_source',
                'preferences',
                'metadata',
                'deleted_at'
            ]);
        });
    }
}; 