<?php

$corePath = $modx->getOption('ecc_core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/ecc/');
$ecc = $modx->getService('ecc', 'ecc', $corePath . 'model/ecc/', array('core_path' => $corePath));

$className = 'eccSystem' . $modx->event->name;
$modx->loadClass('eccSystemPlugin', $ecc->getOption('modelPath') . 'ecc/systems/', true, true);
$modx->loadClass($className, $ecc->getOption('modelPath') . 'ecc/systems/', true, true);
if (class_exists($className)) {
	/** @var $ecc $handler */
	$handler = new $className($modx, $scriptProperties);
	$handler->run();
}
return;