<?php

namespace Zandervdm\Gitception\Commands;

use Illuminate\Console\Command;

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
        //TODO: ADD CREDENTIALS COMMAND
    }
}
