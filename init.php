<?php defined('SYSPATH') or die('No direct script access.');

$GLOBALS['THRIFT_ROOT'] = dirname(__FILE__).'/vendor/phpcassa/thrift/';

require_once $GLOBALS['THRIFT_ROOT'].'packages/cassandra/Cassandra.php';
require_once $GLOBALS['THRIFT_ROOT'].'packages/cassandra/Cassandra.php';
require_once $GLOBALS['THRIFT_ROOT'].'packages/cassandra/cassandra_types.php';
require_once $GLOBALS['THRIFT_ROOT'].'transport/TSocket.php';
require_once $GLOBALS['THRIFT_ROOT'].'protocol/TBinaryProtocol.php';
require_once $GLOBALS['THRIFT_ROOT'].'transport/TFramedTransport.php';
require_once $GLOBALS['THRIFT_ROOT'].'transport/TBufferedTransport.php';

require_once Kohana::find_file('vendor', 'phpcassa/phpcassa');
require_once Kohana::find_file('vendor', 'phpcassa/uuid');