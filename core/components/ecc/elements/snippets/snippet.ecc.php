<?php

/** @var array $scriptProperties */
if (!isset($scriptProperties['snippetName']) AND $snippetName = $this->get('name')) {
	$scriptProperties['snippetName'] = $snippetName;
}

/** @var ecc $ecc */
if (!$ecc = $modx->getService('ecc', 'ecc', $modx->getOption('ecc_core_path', null, $modx->getOption('core_path') . 'components/ecc/') . 'model/ecc/', $scriptProperties)) {
	return 'Could not load ecc class!';
}

$scriptProperties['namespace'] = $modx->getOption('namespace', $scriptProperties, 'ecc');
$scriptProperties['controller'] = $modx->getOption('controller', $scriptProperties, 'default');
$scriptProperties['path'] = $modx->getOption('path', $scriptProperties, 'controllers/welcome');
$scriptProperties['location'] = $modx->getOption('location', $scriptProperties, '0');

$ecc->initialize($modx->context->key, $scriptProperties);
if (!$response = $ecc->handleRequest($scriptProperties)) {
	$response = 'Error';
}

return $response;
