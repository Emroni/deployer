<?php

namespace Deployer;

require_once 'recipe/common.php';

const PATH = 'var/database/';
const CURRENT_PATH = '{{deploy_path}}/current/' . PATH;

task('database:backup', function () {
    run('mkdir -p ' . CURRENT_PATH);

    $env = run('cd {{deploy_path}}/current && cat .env');
    preg_match('/^DATABASE_URL=(.*?)$/m', $env, $url);
    $url = $url[1];

    preg_match('/\:\/\/(.*?)\:(.*?)@(.*?)\:(.*?)\/(.*?)$/', $url, $matches);
    list($matches, $user, $password, $host, $port, $name) = $matches;

    $file = $name . '_' . date('ymdhis') . '.sql';
    $path = CURRENT_PATH . $file;

    run("mysqldump --single-transaction --host=\"{$host}\" --user=\"{$user}\" --password=\"{$password}\" {$name} > {$path}");
});

task('database:download', function () {
    $files = run('ls ' . CURRENT_PATH);
    $files = explode("\n", $files);
    $file = end($files);
    download(CURRENT_PATH . $file, PATH . $file);
});