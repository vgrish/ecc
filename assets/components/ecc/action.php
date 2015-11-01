<?php

if (empty($_REQUEST['action'])) {
	@session_write_close();
	die('Access denied');
}
define('MODX_API_MODE', true);
$productionIndex = dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';
$developmentIndex = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php';
if (file_exists($productionIndex)) {
	/** @noinspection PhpIncludeInspection */
	require_once $productionIndex;
} else {
	/** @noinspection PhpIncludeInspection */
	require_once $developmentIndex;
}
$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
$modx->error->message = null;
$ctx = !empty($_REQUEST['ctx']) ? $_REQUEST['ctx'] : 'web';
if ($ctx != 'web') {
	$modx->switchContext($ctx);
	$modx->user = null;
	$modx->getUser($ctx);
}
define('MODX_ACTION_MODE', true);
/* @var ecc $ecc */
$corePath = $modx->getOption('ecc_core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/ecc/');
$ecc = $modx->getService('ecc', 'ecc', $corePath . 'model/ecc/', array('core_path' => $corePath));
if ($modx->error->hasError() OR !($ecc instanceof ecc)) {
	@session_write_close();
	die('Error');
}
$ecc->initialize($ctx);
if (!$response = $ecc->handleRequest($_REQUEST)) {
	$response = $modx->toJSON(array(
		'success' => false,
		'code' => 401,
	));
}
@session_write_close();
echo $response;