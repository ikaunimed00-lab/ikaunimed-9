<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetUserPassword extends Command
{
    protected $signature = 'user:set-password {email}';

    protected $description = 'Set a user password by email (prompts securely)';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("User with email {$email} not found.");
            return self::FAILURE;
        }

        $password = $this->secret('New password');
        if (! is_string($password) || trim($password) === '') {
            $this->error('Password cannot be empty.');
            return self::FAILURE;
        }

        $passwordConfirmation = $this->secret('Confirm new password');
        if ($passwordConfirmation !== $password) {
            $this->error('Password confirmation does not match.');
            return self::FAILURE;
        }

        $user->forceFill([
            'password' => Hash::make($password),
        ])->save();

        $this->info("Password updated for {$user->email}.");

        return self::SUCCESS;
    }
}

