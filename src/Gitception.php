<?php namespace Zandervdm\Gitception;

use Illuminate\Support\Facades\Request;
use Bitbucket\API\Authentication\Basic;
use Bitbucket\API\Http\Listener\BasicAuthListener;
use Bitbucket\API\Repositories\Issues;
use Bitbucket\API\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class Gitception
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
        $title = $this->createIssueTitleFromException($exceptionData);
        $content = $this->createMarkdownContentFromException($exceptionData);
        $result = $this->bitbucket->create("Zandervdm", "gitception", [
            'title' => $title,
            'content' => $content,
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