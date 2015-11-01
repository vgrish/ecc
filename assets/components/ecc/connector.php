<?php
/** @noinspection PhpIncludeInspection */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var ecc $ecc */
$ecc = $modx->getService('ecc', 'ecc', $modx->getOption('ecc_core_path', null, $modx->getOption('core_path') . 'components/ecc/') . 'model/ecc/');
$modx->lexicon->load('ecc:default');

// handle request
$corePath = $modx->getOption('ecc_core_path', null, $modx->getOption('core_path') . 'components/ecc/');
$path = $modx->getOption('processorsPath', $ecc->config, $corePath . 'processors/');
$modx->request->handleRequest(array(
	'processors_path' => $path,
	'location' => '',
));