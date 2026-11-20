<?php

namespace App\Console\Commands;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('certainty:create-admin {email : The administrator\'s email address} {--name= : Display name} {--promote : Make an existing user an administrator instead}')]
#[Description('Create an administrator account, or promote an existing user')]
class CreateAdminUser extends Command
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Execute the console command. Registration can never create an admin
     * (the role is not fillable), so this is the supported way in production.
     */
    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $existing = User::query()->where('email', $email)->first();

        if ($this->option('promote')) {
            if ($existing === null) {
                $this->error("No user with the email {$email}.");

                return self::FAILURE;
            }

            $existing->forceFill(['role' => UserRole::Admin])->save();
            $this->info("{$email} is now an administrator.");

            return self::SUCCESS;
        }

        $name = $this->option('name') ?: text('Name', required: true);
        $password = password('Password', required: true, hint: 'At least 8 characters.');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password, 'password_confirmation' => $password],
            [...$this->profileRules(), 'password' => $this->passwordRules()],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = User::create(['name' => $name, 'email' => $email, 'password' => $password]);
        $user->forceFill(['role' => UserRole::Admin, 'email_verified_at' => now()])->save();

        $this->info("Administrator {$email} created.");

        return self::SUCCESS;
    }
}
