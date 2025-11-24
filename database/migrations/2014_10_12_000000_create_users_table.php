<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            // Basic info
            $table->string('name');                    // Full name
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Profile fields
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->text('address')->nullable();
            $table->text('bio')->nullable();

            // Tokens
            $table->rememberToken();
            $table->timestamps();
        });

        // Create default admin user
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@glsteam.com',
            'password' => Hash::make('12345678'),
            'phone'    => null,
            'address'  => null,
            'location' => null,
            'bio'      => null,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
