<?php

namespace Zandervdm\Gitception;

use Illuminate\Support\Facades\Request;
use Bitbucket\API\Authentication\Basic;
use Bitbucket\API\Http\Listener\BasicAuthListener;
use Bitbucket\API\Repositories\Issues;
use Bitbucket\API\User;

class GitceptionClass
{
    private $config = [];
    private $bitbucket;

    public function __construct()
    {
        $this->config['email'] = config('gitception.email');
        $this->config['password'] = config('gitception.password');

        $this->bitbucket = new Issues();
        $this->bitbucket->setCredentials(
            new Basic($this->config['email'], $this->config['password'])
        );
    }

    public function create($exception)
    {
        $exceptionData = $this->getExceptionData($exception);
        $result = $this->bitbucket->create("Zandervdm", "gitception", [
            'title' => "New exception: " . $exceptionData['method'] . ': ' . $exceptionData['fullUrl'],
            'content' => $exceptionData['exception'],
            'kind' => 'bug',
            'priority' => 'major'
        ]);
        dd($result);
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
        $data['exegutor'] = [];
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
}