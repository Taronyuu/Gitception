<?php

namespace Zandervdm\Gitception\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class GitceptionGenerateCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gitception:credentials';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate your credentials for using in Gitception';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('This command will ask you for 2 values, your Git email address and password.');
        $this->info('These values are needed because to create an issue we need to connect to the API');
        $this->info('These values won\'t be stored plaintext! They both will be encrypted using your secret key and returned to you.');

        $email = $this->ask("Your Git email address");
        $password = $this->secret("Your git password");

        $this->info('Add these 2 values to your environment file and you are done.');
        $this->line("GITCEPTION_EMAIL=" . Crypt::encrypt($email));
        $this->line('');
        $this->line('GITCEPTION_PASSWORD=' . Crypt::encrypt($password));
    }
}
