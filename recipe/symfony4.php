<?php

namespace Deployer;

require_once 'recipe/common.php';

const PATH = 'var/database/';
const CURRENT_PATH = '{{deploy_path}}/current/' . PATH;

task('database:backup', function () {
    createBackup();
});

task('database:download', function () {
    downloadBackup();
});

task('database:restore', function () {
    $file = getLastBackup();
    $db = getDatabase();
    replaceDatabase($db, CURRENT_PATH . $file);
});

task('database:pull', function () {
    $file = downloadBackup();
    $db = getDatabase(true);
    replaceDatabase($db, PATH . $file, true);
});

function getDatabase($local = false)
{
    if ($local) {
        $env = runLocally('cat .env');
    } else {
        $env = run('cd {{deploy_path}}/current && cat .env');
    }

    preg_match('/^DATABASE_URL=(.*?)$/m', $env, $url);
    $url = $url[1];

    preg_match('/\:\/\/(.*?)\:(.*?)@(.*?)\:(.*?)\/(.*?)$/', $url, $matches);
    list($matches, $user, $password, $host, $port, $name) = $matches;
    $credentials = "--host=\"{$host}\" --user=\"{$user}\" --password=\"{$password}\"";

    return (object)compact('user', 'password', 'host', 'port', 'name', 'credentials');
}

function replaceDatabase($db, $file, $local = false)
{
    $drop = "mysql -Nse 'show tables' -D {$db->name} {$db->credentials} | while read table; do echo \"SET FOREIGN_KEY_CHECKS = 0;DROP TABLE \`\$table\`;SET FOREIGN_KEY_CHECKS = 1;\"; done | mysql {$db->name} {$db->credentials}";
    $import = "mysql {$db->credentials} {$db->name} < {$file}";

    if ($local) {
        runLocally($drop);
        runLocally($import);
    } else {
        run($drop);
        run($import);
    }
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

function downloadBackup()
{
    runLocally('mkdir -p ' . PATH);

    $file = getLastBackup();
    download(CURRENT_PATH . $file, PATH . $file);

    return $file;
}