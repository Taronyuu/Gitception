# Gitception
#### Log exceptions to Bitbucket (and soon Github) as automated issues

### What is it?
In short, this Laravel package will log exceptions to your Git account. (ATM only Bitbucket is supported. Github is in development) In the issue the origin of the exception, stack trace and other data will be added.

After this you can solve the issue, push your changes and close the issue. Its great for closed source projects that are added to Git.

### Features
- Log exceptions to Bitbucket
- Get all needed details in the issue
- Bitbucket supported (Github is being worked on)
- Set an sleep time, to prevent 1 exception to be reported multiple times
- Don't like the default view? Use your own!
- Set your default issue type
- Set your default issue priority
- Set your environments that should be reported
- Set exceptions that should be ignored

### Installation
Add this package to your install using
```
composer require zandervdm/gitception
```

After that add the service provider to `/config/app.php`, recommendation is to make it your first
```
Zandervdm\Gitception\GitceptionServiceProvider::class
```

Now you can publish the config file using
```
php artisan vendor:publish --provider="Zandervdm\Gitception\GitceptionServiceProvider"
```

Bitbucket requires us to specify our E-mail and password, because we don't want those to be stored plain text we are going to encrypt them. You can use the `gitception:credentials` command to generate those. Just follow the step and after that copy the 2 var's including the encryption string to your env file.
```
php artisan gitception:credentials
```

After you copied those 2 lines we'll continue to the config file located at `/config/gitception.php`. Change `git_username` and `git_repository`. The rest is all optional and can be left alone if you like it. However for production you should change `test_run` to `false` if you want to report exceptions.

Last thing we need to do is add our report command to the Laravel exception handler. By default this is located in `/app/Exceptions/Handler.php'`. Search for the `report(Exception $e)` function and add `\Gitception::create($e);`
```
 /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        \Gitception::create($e);
        parent::report($e);
    }
```

### Too many issues!
Because it is possible that something went wrong and you are left with tens of issues I added a command that will resolve this for you. Just use the `gitception:reset` command, it will ask you a few questions and it will ***remove or resolve*** all the needed issues.
```
php artisan gitception:reset
```

### To-do
- ***Add Github support***
- Optimize the `storeExceptionInCache($exceptionData)` function
- Code cleanup
