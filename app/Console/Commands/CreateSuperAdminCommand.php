<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Silber\Bouncer\BouncerFacade;

#[Signature('app:create-super-admin
    {--email= : Email address of the super-admin}
    {--first-name= : First name (required when creating a new account)}
    {--last-name= : Last name (required when creating a new account)}
    {--password= : Password (minimum 8 characters when creating a new account)}')]
#[Description('Create a new super-admin account or promote an existing user to super-admin')]
class CreateSuperAdminCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $emailData = $this->validateInput(
            ['email' => $this->option('email') ?? $this->ask('Email address')],
            ['email' => ['required', 'string', 'email', 'max:255']],
        );

        if ($emailData === null) {
            return self::FAILURE;
        }

        $email = $emailData['email'];
        $existingUser = User::where('email', $email)->first();

        if ($existingUser !== null) {
            $existingUser->assign(['admin', 'super-admin']);
            BouncerFacade::refreshFor($existingUser);

            $this->info("User [{$existingUser->email}] has been granted super-admin access.");

            return self::SUCCESS;
        }

        $validated = $this->validateInput(
            [
                'first_name' => $this->option('first-name') ?? $this->ask('First name'),
                'last_name' => $this->option('last-name') ?? $this->ask('Last name'),
                'email' => $email,
                'password' => $this->option('password') ?? $this->secret('Password'),
            ],
            [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ],
        );

        if ($validated === null) {
            return self::FAILURE;
        }

        $admin = Admin::create($validated);
        $admin->assign('super-admin');
        BouncerFacade::refreshFor($admin);

        $this->info("Super-admin [{$admin->email}] created successfully.");

        return self::SUCCESS;
    }

    /**
     * Validate input attributes and output any validation errors to the console.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>|null
     */
    private function validateInput(array $data, array $rules): ?array
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return null;
        }

        return $validator->validated();
    }
}
