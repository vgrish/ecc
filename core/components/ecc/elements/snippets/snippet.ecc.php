<?php

/** @var array $scriptProperties */
$scriptProperties['snippetName'] = $modx->getOption('snippetName', $scriptProperties, $this->get('name'), true);
/** @var ecc $ecc */
if (!$ecc = $modx->getService('ecc', 'ecc', $modx->getOption('ecc_core_path', null, $modx->getOption('core_path') . 'components/ecc/') . 'model/ecc/', $scriptProperties)) {
	return 'Could not load ecc class!';
}

$scriptProperties['namespace'] = $modx->getOption('namespace', $scriptProperties, 'ecc', true);
$scriptProperties['controller'] = $modx->getOption('controller', $scriptProperties, 'default', true);
$scriptProperties['path'] = $modx->getOption('path', $scriptProperties, 'controllers/welcome', true);
$scriptProperties['location'] = $modx->getOption('location', $scriptProperties, '0', true);

$ecc->initialize($modx->context->key, $scriptProperties);
if (!$response = $ecc->handleRequest($scriptProperties)) {
	$response = 'Error';
}

return $response;
