# Deployer recipes

## Installation
First require with composer
```console
composer require emroni/deployer
```
Then include at the top of your `deploy.php` file
```php
require __DIR__ . '/vendor/emroni/deployer/recipe/symfony4.php';
```

## Tasks
### database:backup
*Creates a backup on the server.*
- Grabs the servers database info from `{{deploy_path}}/current/.env`
- Dumps the database to `{{deploy_path}}/current/var/database/[database]_[date][time].sql` 

### database:restore
*Restores the last backup to the servers database*
- Grabs the last backup
    - If it doesn't exist, it will make one (see [database:backup](#databasebackup))
- Drops all tables of the servers database
- Imports backup file

### database:download
*Downloads the last backup from the server*
- Grabs the last backup on the server
    - If it doesn't exist, it will make one (see [database:backup](#databasebackup))
- Downloads the backup file to `/var/database/[name].sql`

### database:pull
*Downloads the last backup from the server and imports locally*
- Grabs the last backup on the server
    - If it doesn't exist, it will make one (see [database:backup](#databasebackup))
- Downloads the backup file to `/var/database/[name].sql`
- Grabs the local database info from `/.env`
- Drops all tables
- Imports backup file