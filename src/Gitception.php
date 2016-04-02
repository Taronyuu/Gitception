<?php namespace Zandervdm\Gitception;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Request;
use Bitbucket\API\Authentication\Basic;
use Bitbucket\API\Http\Listener\BasicAuthListener;
use Bitbucket\API\Repositories\Issues;
use Bitbucket\API\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Gitception
{
    private $config = [];
    private $bitbucket;

    public function __construct()
    {
        //Store all the config values in a local var, and decrypt the credentials.
        $this->config = config('gitception');
        $this->config['email'] = Crypt::decrypt($this->config['email']);
        $this->config['password'] = Crypt::decrypt($this->config['password']);

        //Create a new issue object for Bitbucket, and set our credentials.
        $this->bitbucket = new Issues();
        $this->bitbucket->setCredentials(
            new Basic($this->config['email'], $this->config['password'])
        );
    }

    public function create($exception)
    {
        //Get our exception details in a nice array
        $exceptionData = $this->getExceptionData($exception);

        if(!$this->shouldReportException($exceptionData)){
            return;
        }
        $exceptionCache = Cache::get('zandervdm.gitception.exceptions');


        //Get our title and content
        $title = $this->createIssueTitleFromException($exceptionData);
        $content = $this->createMarkdownContentFromException($exceptionData);

        //Create the final issue in Bitbucket
        $result = $this->bitbucket->create("Zandervdm", "gitception", [
            'title' => $title,
            'content' => $content,
            'kind' => $this->config['default_issue_type'],
            'priority' => $this->config['default_issue_priority'],
        ]);


    }

    private function shouldReportException($exceptionData)
    {
        //This exception is in the 'ignore' config, so ignore it.
        if(in_array($exceptionData['class'], $this->config['ignore'])){
            return false;
        }

        //Now we are checking if a previous previous exception of this kind exists, and if it does,
        //what was the last time it was reported.
        $exceptionCache = collect(Cache::get('zandervdm.gitception.exceptions'));
        if($exceptionCache->count() == 0){
            return true;
        }

        
    }

    private function getExceptionData($exception)
    {
        $data = [];
        $data['host'] = Request::server('SERVER_NAME');
        $data['method'] = Request::method();
        $data['fullUrl'] = Request::fullUrl();
        $data['exception'] = $exception->getMessage();
        $data['error'] = $exception->getTraceAsString();
        $data['line'] = $exception->getLine();
        $data['file'] = $exception->getFile();
        $data['class'] = get_class($exception);
        $data['storage'] = array(
            'SERVER' => Request::server(),
            'GET' => Request::query(),
            'POST' => $_POST,
            'FILE' => Request::file(),
            'OLD' => Request::hasSession() ? Request::old() : [],
            'COOKIE' => Request::cookie(),
            'SESSION' => Request::hasSession() ? Session::all() : [],
            'HEADERS' => Request::header(),
        );
        $data['storage'] = array_filter($data['storage']);
        $count = 15;
        $lines = file($data['file']);
        $data['exegutor'] = [];
        for ($i = -1 * abs($count); $i <= abs($count); $i++) {
            $data['exegutor'][] = $data['line'];
        }
        $data['exegutor'] = array_filter($data['exegutor']);
        // to make symfony exception more readable
        if ($data['class'] == 'Symfony\Component\Debug\Exception\FatalErrorException') {
            preg_match("~^(.+)' in ~", $data['exception'], $matches);
            if (isset($matches[1])) {
                $data['exception'] = $matches[1];
            }
        }
        return $data;
    }

    private function createMarkdownContentFromException($data)
    {
        return view('gitception::exception-markdown', compact('data'))->render();
    }

    private function createIssueTitleFromException($exceptionData)
    {
        return "New exception [" . $exceptionData['method'] . "]" . $exceptionData['host'] . ': ' . $exceptionData['exception'];
    }
}