<?php

namespace Deployer;

require_once 'recipe/common.php';

const PATH = '{{deploy_path}}/current/var/database';

task('database:backup', function () {
    run('mkdir -p ' . PATH);

    $env = run('cd {{deploy_path}}/current && cat .env');
    preg_match('/^DATABASE_URL=(.*?)$/m', $env, $url);
    $url = $url[1];

    preg_match('/\:\/\/(.*?)\:(.*?)@(.*?)\:(.*?)\/(.*?)$/', $url, $matches);
    list($matches, $user, $password, $host, $port, $name) = $matches;

    $file = $name . '_' . date('ymdhis') . '.sql';
    $path = PATH . "/{$file}";

    run("mysqldump --single-transaction --host=\"{$host}\" --user=\"{$user}\" --password=\"{$password}\" {$name} > {$path}");
});