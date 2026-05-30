<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\User;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'generate:api-token {email : Admin user e‑mail} {--name=python-bot : Token name}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a Sanctum personal‑access token for a given admin e‑mail (creates user if missing)';

    public function handle()
    {
        $email = $this->argument('email');
        $name  = $this->option('name');

        $user = User::firstOrCreate([
            'email' => $email,
        ], [
            'name' => 'Admin',
            'password' => bcrypt(Str::random(12)), // random password – you can change later
        ]);

        $plainToken = $user->createToken($name, ['*'])->plainTextToken;
        $this->info($plainToken);
        return 0;
    }
}
?>