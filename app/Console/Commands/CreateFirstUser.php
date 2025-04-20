<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateFirstUser extends Command
{
    protected $signature = 'app:create-first-user {name=Admin} {email=admin@gmail.com} {password=secret}';
    protected $description = 'Create the first tenant and admin user';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Create tenant
        $tenant = Tenant::create([
            'name' => $name . "'s Organization",
            'email' => $email,
            'currency' => 'IDR',
            'is_active' => true
        ]);

        $this->info("Tenant created: {$tenant->name}");

        // Create admin user
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->info("Admin user created: {$user->email}");

        return Command::SUCCESS;
    }
}
