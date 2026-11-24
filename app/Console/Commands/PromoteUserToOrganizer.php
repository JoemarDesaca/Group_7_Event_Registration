<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('events:make-organizer {email : Email address of an existing user}')]
#[Description('Grant event organizer access to an existing attendee account.')]
class PromoteUserToOrganizer extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No account exists for that email address.');

            return self::FAILURE;
        }

        $user->forceFill(['role' => 'organizer'])->save();
        $this->info("Organizer access granted to {$user->email}.");

        return self::SUCCESS;
    }
}
