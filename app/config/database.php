<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	    'default' => array(
            'type'       => 'pdo',
            'connection' => array(
                /**
                 * The following options are available for PDO:
                 *
                 * string   dsn         Data Source Name
                 * string   username    database username
                 * string   password    database password
                 * boolean  persistent  use persistent connections?
                 */
                'dsn'        => 'mysql:host=mysql;dbname=npppm',
                'username'   => 'npppm_user',
                'password'   => 'npppm',
                'persistent' => TRUE,
            ),
        )
);

