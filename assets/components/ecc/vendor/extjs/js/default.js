Ext.namespace('ecc');

ecc = function(config) {
	config = config || {};
	ecc.superclass.constructor.call(this, config);
	this.config = config;
};
Ext.extend(ecc, Ext.Component, {
	page: {},
	window: {},
	grid: {},
	tree: {},
	panel: {},
	combo: {},
	config: {},
	view: {},
	keymap: {},
	plugin: {},
	utils: {}
});
Ext.reg('ecc', ecc);

ecc = new ecc();