<?php

namespace Zandervdm\Gitception\Commands;

use Bitbucket\API\Authentication\Basic;
use Bitbucket\API\Repositories\Issues;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class GitceptionResetIssues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gitception:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all issues created by this plugin';

    private $bitbucket;
    private $config;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->config = config('gitception');

        if(!$this->config['email'] || !$this->config['password']){
            return;
        }

        $email = Crypt::decrypt($this->config['email']);
        $password = Crypt::decrypt($this->config['password']);

        $this->bitbucket = new Issues();
        $this->bitbucket->setCredentials(new Basic($email, $password));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("This command will search for issues with a title that contains 'New exception [', because we don't
        permanently store anything local this is the only way we can check for this. You have been warned.");
        $status = $this->choice("What status do you want to reset?", ['quit', 'all', 'new', 'open', 'resolved', 'on hold', 'invalid', 'duplicate', 'wontfix'], true);
        if($status == "quit"){
            return;
        }

        $action = $this->choice("Do you want to delete or resolve the issues?", ['quit', 'resolve', 'delete'], true);
        if($action == "quit"){
            return;
        }

        $this->warn("THIS WILL DELETE OR CLOSE MULTIPLE ISSUES, DELETING ISSUES CANNOT BE REVERSED.");
        if(!$this->confirm("Do you wish to continue? [y|N]")){
            return;
        }

        $issues = $this->getIssuesByStatusAndSearch($status);

        $count = 0;
        $issueArray = json_decode($issues->getContent())->issues;
        foreach($issueArray as $issue){
            $issueId = (int)$issue->local_id;
            if($action == "resolve"){
                $this->markIssueAsResolvedByIssueId($issueId);
                $count++;
            }elseif($action == "delete"){
                $this->deleteIssueByIssueId($issueId);
                $count++;
            }
        }

        $this->info($count . " issue(s) have been changed.");
    }

    private function getIssuesByStatusAndSearch($status)
    {
        return $this->bitbucket->all($this->config['git_username'], $this->config['git_repository'], [
            'limit' => 50,
            'start' => 0,
            'search' => 'New exception [',
            'status' => $status,
        ]);
    }

    private function markIssueAsResolvedByIssueId($issueId)
    {
        $this->bitbucket->update($this->config['git_username'], $this->config['git_repository'], $issueId, [
            'status' => 'resolved'
        ]);
    }

    private function deleteIssueByIssueId($issueId)
    {
        $this->bitbucket->delete($this->config['git_username'], $this->config['git_repository'], $issueId);
    }
}
