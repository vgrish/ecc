<?php

class eccSystemOnBeforeCacheUpdate extends eccSystemPlugin
{
	public function run()
	{
		$cachePath = $this->ecc->getOption('assetsCachePath', null, MODX_ASSETS_PATH . 'components/ecc/cache/');
		$cacheManager = $this->modx->getCacheManager();
		if ($cacheManager && file_exists($cachePath)) {
			$cacheManager->deleteTree($cachePath, array_merge(array('deleteTop' => false, 'skipDirs' => false, 'extensions' => array())));
		}
	}
}
