<?php

class MiniShop2OrderController extends eccBaseController
{
	/* @var minishop2 $minishop2 */
	public $minishop2;

	/** @inheritdoc} */
	public function initialize($ctx = 'web')
	{
		$this->modx->error->errors = array();
		$this->modx->error->message = '';

		$config = $this->modx->toJSON(array(
			'connectorUrl' => $this->config['actionUrl'],
			'namespace' => $this->config['namespace'],
			'controller' => $this->config['controller'],
			'path' => $this->config['path'],
		));
		$this->regTopScript("eccConfig.{$this->config['namespace']}={$config};");


		$corePath = $this->modx->getOption('minishop2.core_path', null, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/minishop2/');
		if (!$this->minishop2 = $this->modx->getService('minishop2', 'minishop2', $corePath . 'model/minishop2/', array('core_path' => $corePath))) {
			return false;
		}

		$grid_fields = array_map('trim', explode(',', $this->modx->getOption('ms2_order_grid_fields', null, 'id,customer,num,status,cost,weight,delivery,payment,createdon,updatedon,comment', true)));
		$grid_fields = array_values(array_unique(array_merge($grid_fields, array('id', 'user_id', 'num', 'type', 'actions'))));
		$address_fields = array_map('trim', explode(',', $this->modx->getOption('ms2_order_address_fields')));
		$product_fields = array_map('trim', explode(',', $this->modx->getOption('ms2_order_product_fields', null, '')));
		$product_fields = array_values(array_unique(array_merge($product_fields, array('id', 'product_id', 'name'))));

		$config = $this->modx->toJSON(array_merge(
			$this->minishop2->config,
			array(
			'connector_url' => $this->config['actionUrl'],
			'order_grid_fields' => $grid_fields,
			'order_address_fields' => $address_fields,
			'order_product_fields' => $product_fields,
		)));
		$this->regTopScript("miniShop2Config={$config};");

		return true;
	}

	/** @inheritdoc} */
	public function getLanguageTopics() {
		return array('minishop2:default', 'minishop2:product', 'minishop2:manager');
	}

	/** @inheritdoc} */
	public function DefaultAction()
	{
		$this->modx->regClientCSS($this->minishop2->config['cssUrl'] . 'mgr/bootstrap.min.css');
		$this->modx->regClientCSS($this->minishop2->config['cssUrl'] . 'mgr/main.css');

		$this->ecc->addClientExtJS();
		$this->ecc->addClientLexicon($this->getLanguageTopics(),'lexicon/lexicon');

		$this->ecc->addClientJs(array(
			MODX_ASSETS_URL . 'components/minishop2/js/mgr/minishop2.js',
			MODX_ASSETS_URL . 'components/minishop2/js/mgr/misc/ms2.utils.js',

			$this->ecc->config['assetsUrl'] . 'external/minishop2/orders/panel.js',
			$this->ecc->config['assetsUrl'] . 'external/minishop2/orders/grid.js',
		), 'orders/all');

		$this->onReady();

		return $this->getWrapper();
	}

	/** @inheritdoc} */
	public function onReady()
	{
		$this->regBottomScript("
			Ext.onReady(function () {
				miniShop2.config = miniShop2Config || {};
				Ext.ComponentMgr.create({
					xtype: 'panel',
					renderTo: \"{$this->config['wrapperId']}\",
					items: [new miniShop2.panel.Orders()]
				}).doLayout();
				var preloader = document.getElementById(\"{$this->config['wrapperId']}\").querySelectorAll(\".ecc-preloader\");
				if (preloader) {
					preloader[0].parentNode.removeChild(preloader[0]);
				}
		});");

		$this->regBottomScript("MODx.config.ms2_date_format='{$this->modx->config['ms2_date_format']}'");

	}

	/** @inheritdoc} */
	public function defaultProcessorAction($data = array())
	{
		$this->ecc->config['processorsPath'] = MODX_CORE_PATH . 'components/minishop2/processors/';

		return $this->runProcessor($data['action'], $data);
	}

}
