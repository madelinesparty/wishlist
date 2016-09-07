# Wish List Application for madelinesparty.com

## Database Configuration

The wish list application can be configured to use an existing database or
have its own database. Create the necessary database schema by executing
`admin/madelines_party.sql` and `admin/settings.sql` in the database.

Create a database user for the wish list application. The user must have
select, insert, update, and delete access to the database. The application
user should not have privileges to modify the schema or other administrative
tasks.

## Application Configuration

Create a file `common/config.php`. Define `$mp_root_path` as shown here. The
path must be the full URL path not including the host.
```php
<?php
// Set $mp_root_path to base path of the wish list feature
$mp_root_path='/wishlist';
?>
```
Create a file `common/dbparams.php`. Define `$mp_db_dsn` to be the PDO DSN
for the database containing the wish list data. Define `$mp_db_username` to
be a database user created for the wish list application. Define
`$mp_db_password` to be the password for the wish list database user.
```php
<?php
$mp_db_dsn = 'mysql:dbname=madelines_party;host=database-host-name';
$mp_db_username = 'username';
$mp_db_password = 'password';
?>
```

## Web Server Configuration

The web server must be configured to prevent serving the content of the
`common/` directory. If this directory is not protected, the database
configuration will be exposed.

The web server must be configured to enforce password authentication for
the `admin/` directory. If this directory is not protected, the admin
interface will be open to abuse.

## License

The Wish List Application for madelinesparty.com is licensed under GPL version 2.
Full text found at https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt.