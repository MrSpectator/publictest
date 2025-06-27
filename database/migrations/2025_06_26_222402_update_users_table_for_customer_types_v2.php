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
            // Add new fields for customer types
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone_number')->nullable()->after('email');
            $table->string('company_name')->nullable()->after('phone_number');
            $table->string('company_contact_person')->nullable()->after('company_name');
            $table->string('company_contact_number')->nullable()->after('company_contact_person');
            $table->string('company_url')->nullable()->after('company_contact_number');
            $table->string('company_address')->nullable()->after('company_url');
            $table->unsignedBigInteger('country_id')->nullable()->after('company_address');
            $table->unsignedBigInteger('state_id')->nullable()->after('country_id');
            $table->tinyInteger('type')->default(1)->comment('1=Individual, 2=Company')->after('state_id');

            // Drop the old name column since we now have first_name and last_name
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Recreate the old name column
            $table->string('name')->after('id');

            // Drop the new fields
            $table->dropColumn([
                'first_name', 'last_name', 'phone_number', 'company_name',
                'company_contact_person', 'company_contact_number', 'company_url',
                'company_address', 'country_id', 'state_id', 'type'
            ]);
        });
    }
};
