<?php
/*
 *  E_STRICT error reporting must be disabled due to incompatibile method signature with the original \MongoCollection class
 */
$oldErrorLevel = error_reporting();
error_reporting($oldErrorLevel & ~E_STRICT);
include '_MongoCollectionMockProxy.php';
error_reporting($oldErrorLevel);