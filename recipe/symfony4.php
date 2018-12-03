<?php

namespace Deployer;

require_once 'recipe/common.php';

const PATH = 'var/database/';
const CURRENT_PATH = '{{deploy_path}}/current/' . PATH;

task('database:backup', function () {
    createBackup();
});

task('database:download', function () {
    $file = getLastBackup();
    download(CURRENT_PATH . $file, PATH . $file);
});

task('database:restore', function () {
    $file = getLastBackup();
    $file = CURRENT_PATH . $file;
    $db = getDatabase();

    run("mysql -Nse 'show tables' -D {$db->name} {$db->credentials} | while read table; do echo \"SET FOREIGN_KEY_CHECKS = 0;DROP TABLE \`\$table\`;SET FOREIGN_KEY_CHECKS = 1;\"; done | mysql {$db->name} {$db->credentials}");
    run("mysql {$db->credentials} {$db->name} < {$file}");
});

function getDatabase()
{
    $env = run('cd {{deploy_path}}/current && cat .env');
    preg_match('/^DATABASE_URL=(.*?)$/m', $env, $url);
    $url = $url[1];

    preg_match('/\:\/\/(.*?)\:(.*?)@(.*?)\:(.*?)\/(.*?)$/', $url, $matches);
    list($matches, $user, $password, $host, $port, $name) = $matches;
    $credentials = "--host=\"{$host}\" --user=\"{$user}\" --password=\"{$password}\"";

    return (object)compact('user', 'password', 'host', 'port', 'name', 'credentials');
}

function createBackup()
{
    run('mkdir -p ' . CURRENT_PATH);

    $db = getDatabase();
    $file = $db->name . '_' . date('ymdhis') . '.sql';
    $path = CURRENT_PATH . $file;

    run("mysqldump --single-transaction {$db->credentials} {$db->name} > {$path}");
}

function getLastBackup()
{
    $files = run('ls ' . CURRENT_PATH);
    $files = explode("\n", $files);
    $file = end($files);

    if (!$file) {
        createBackup();
    }

    return $file;
}