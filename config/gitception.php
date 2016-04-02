<?php

return [

    /**
     * This is your encrypted Bitbucket email taken from your environment file.
     * You can generate your encrypted email by using the 'gitception:credentials' command.
     */
    'email' => env("GITCEPTION_EMAIL", ""),

    /**
     * This is your encrypted Bitbucket password taken from your environment file.
     * You can generate your encrypted password by using the 'gitception:credentials' command.
     */
    'password' => env("GITCEPTION_PASSWORD", ""),

    /**
     * How many minutes do issues need to be ignored before they are reported again?
     * The cache is used to store these credentials. So no Database connection needed.
     */
    'sleep_time' => 60,

    /**
     * If you ever decided the default template isn't okay for you, copy the excisting view and change it to your needs.
     * After that change the path and name here.
     * e.g. templates.git-markdown, and store it in /resources/views/templates/git-markdown.blade.php
     */
    'custom_view' => "",

    /**
     * The default type the issue needs to receive on creation.
     * Available options: bug, enhancement, proposal, task
     */
    'default_issue_type' => "bug",

    /**
     * The default priority the issue needs to receive on creation.
     * Available options: trivial, minor, major, critical, blocker
     */
    'default_issue_priority' => 'major',

    /**
     * A list of all exceptions that needs to be ignored, these won't be reported
     */
    'ignore' => [
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
    ],
];