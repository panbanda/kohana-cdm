# Kohana Cassandra Data Model

A module for managing cassandra (http://cassandra.apache.org/) communication inside of Kohana (v3.0+).  It is very similar to ORM in how it is constructed.

## Quick Start

This is a wrapper module to avoid entering your private key and and access key each time you call methods.  It also contains a helper for linking to assets inside of your S3 buckets.  It supports streaming URLs if your bucket is streaming.  Please see the config file.

### Modify the Config File

Move config/cdm.php to your application config directory and modify your Amazon security information as Cassandra indicates.

### Code Away!

Example of a model:

    class Model_User extends CDM {
        
		protected $_column_family = 'Users';
		
		protected $_is_supercolumn = TRUE;
		
		protected $_object = array
		(
			'firstname' 	=> NULL,
			'lastname'		=> NULL,
		);
        
    }
	
## Methods of CDM

There are some differences in CDM in comparison to ORM that cannot change due to the actual purpose of a non-relational structure (NoSQL) versus relational (MySQL).  In light of that, here are some new methods:

	// Getting a single user

	$key = 21837191; // User "primary key" if you will
	$user = CDM::factory('user')->get($key);
	
	// Getting multiple users:
	
	$keys = array( 1, 2, 3 ); // User's primary keys
	$users = CDM::factory('user')->get($keys);
	
	// Setting and updating data for user 1
	
	$user = CDM::factory('user', 1);
	$user->values(array
	(
		'firstname' => 'Jonathan',
		'lastname'	=> 'Reyes',
	))->save();