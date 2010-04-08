<?php

return array
(
	// Values can be set in model
	'default' => array
	(
		'keyspace' => 'Keyspace1'
	),

	// Thrift vendor files
	'thrift' => MODPATH . 'cassandra/vendor/phpcassa/thrift',

	// Server connection
	'connection' => array
	(
		'servers' => array
		(
			array 
			(
				'host' 	=> '127.0.0.1',
				'port' 	=> 9160,
			),
		)
	)
);