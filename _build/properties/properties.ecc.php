<?php

$properties = array();

$tmp = array(
	'namespace'   => array(
		'type'  => 'textfield',
		'value' => 'ecc',
	),
	'controller'  => array(
		'type'  => 'textfield',
		'value' => 'default',
	),
	'path'        => array(
		'type'  => 'textfield',
		'value' => 'controllers/welcome',
	),
	'tplOuter'    => array(
		'type'  => 'textfield',
		'value' => 'ecc.wrapper',
	),
	'frontendCss' => array(
		'type'  => 'textfield',
		'value' => '',
	),
	'frontendJs'  => array(
		'type'  => 'textfield',
		'value' => '',
	),
	'mainCss'     => array(
		'type'  => 'textfield',
		'value' => '',
	),
	'mainJs'      => array(
		'type'  => 'textfield',
		'value' => '',
	),
	'location'    => array(
		'type'    => 'list',
		'options' => array(
			array('text' => 'in', 'value' => '0'),
			array('text' => 'out', 'value' => '1'),
		),
		'value'   => '0',
	),
);

foreach ($tmp as $k => $v) {
	$properties[] = array_merge(
		array(
			'name'    => $k,
			'desc'    => PKG_NAME_LOWER . '_prop_' . $k,
			'lexicon' => PKG_NAME_LOWER . ':properties',
		), $v
	);
}

return $properties;