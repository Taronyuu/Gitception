<?php namespace Zandervdm\Gitception;

use Carbon\Carbon;
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

    /**
     * Gitception constructor.
     */
    public function __construct()
    {
        //Store all the config values in a local var, and decrypt the credentials.
        $this->config = config('gitception');
//        $this->config['email'] = Crypt::decrypt($this->config['email']);
//        $this->config['password'] = Crypt::decrypt($this->config['password']);

        //Create a new issue object for Bitbucket, and set our credentials.
        $this->bitbucket = new Issues();
        $this->bitbucket->setCredentials(
            new Basic($this->config['email'], $this->config['password'])
        );
    }

    /**
     * create function.
     * This function should be called from the Laravel exception handler. It will determine if the issue should be
     * reported, will parse the issue template and will create the issue. Also it will store the updated issue
     * in the cache so multiple issues aren't reported multiple times.
     *
     * @param $exception
     * @return void
     */
    public function create($exception)
    {
        //Get our exception details in a nice array
        $exceptionData = $this->getExceptionData($exception);

        if(!$this->shouldReportException($exceptionData)){
            return;
        }

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

        $this->storeExceptionInCache($exceptionData);
    }

    /**
     * shouldReportException function.
     * Will determine if the issue should be reported.
     *
     * @param $exceptionData
     * @return bool
     */
    private function shouldReportException($exceptionData)
    {
        //If test_run is set to true, don't report it.
        if($this->config['test_run']){
            return false;
        }

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

        $exception = $exceptionCache->where('class', $exceptionData['class'])
                                    ->where('file', $exceptionData['file'])
                                    ->first();

        if(!$exception){
            return true;
        }

        if($exception['timestamp'] < (Carbon::now()->timestamp - $this->config['sleep_time'])){
            return true;
        }

        return false;
    }

    /**
     * storeExceptionInCache function.
     * Updates the cache with a new exception or will update an existing exception.
     *
     * @param $exceptionData
     * @return void
     */
    private function storeExceptionInCache($exceptionData)
    {
        $exceptionCache = Cache::get('zandervdm.gitception.exceptions');

        if(!$exceptionCache){
            $exceptionCache = [];
        }

        $exceptionExists = false;
        foreach($exceptionCache as $key => $exception){
            if($exception['file'] == $exceptionData['file'] && $exception['class'] == $exceptionData['class']){
                $exceptionCache[$key]['timestamp'] = Carbon::now()->timestamp;
                $exceptionExists = true;
            }
        }

        if(!$exceptionExists){
            $exceptionCache[] = [
                'file' => $exceptionData['file'],
                'class' => $exceptionData['class'],
                'timestamp' => Carbon::now()->timestamp,
            ];
        }

        Cache::forever('zandervdm.gitception.exceptions', $exceptionCache);
        return;
    }

    /**
     * getExceptionData function.
     * Creates a nicely formated exception array
     *
     * @param $exception
     * @return array
     */
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

    /**
     * createMarkdownContentFromException function.
     * Returns the rendered exception view.
     *
     * @param $data
     * @return mixed
     */
    private function createMarkdownContentFromException($data)
    {
        if($this->config['custom_view']){
            return view($this->config['custom_view'], compact('data'))->render();
        }
        return view('gitception::exception-markdown', compact('data'))->render();
    }

    /**
     * createIssueTitleFromException function.
     * Returns the title for the issue
     *
     * @param $exceptionData
     * @return string
     */
    private function createIssueTitleFromException($exceptionData)
    {
        return "New exception [" . $exceptionData['method'] . "]" . $exceptionData['host'] . ': ' . $exceptionData['exception'];
    }
}