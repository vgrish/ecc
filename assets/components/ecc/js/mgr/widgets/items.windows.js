ecc.window.CreateItem = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'ecc-item-window-create';
	}
	Ext.applyIf(config, {
		title: _('ecc_item_create'),
		width: 550,
		autoHeight: true,
		url: ecc.config.connector_url,
		action: 'mgr/item/create',
		fields: this.getFields(config),
		keys: [{
			key: Ext.EventObject.ENTER, shift: true, fn: function () {
				this.submit()
			}, scope: this
		}]
	});
	ecc.window.CreateItem.superclass.constructor.call(this, config);
};
Ext.extend(ecc.window.CreateItem, MODx.Window, {

	getFields: function (config) {
		return [{
			xtype: 'textfield',
			fieldLabel: _('ecc_item_name'),
			name: 'name',
			id: config.id + '-name',
			anchor: '99%',
			allowBlank: false,
		}, {
			xtype: 'textarea',
			fieldLabel: _('ecc_item_description'),
			name: 'description',
			id: config.id + '-description',
			height: 150,
			anchor: '99%'
		}, {
			xtype: 'xcheckbox',
			boxLabel: _('ecc_item_active'),
			name: 'active',
			id: config.id + '-active',
			checked: true,
		}];
	}

});
Ext.reg('ecc-item-window-create', ecc.window.CreateItem);


ecc.window.UpdateItem = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'ecc-item-window-update';
	}
	Ext.applyIf(config, {
		title: _('ecc_item_update'),
		width: 550,
		autoHeight: true,
		url: ecc.config.connector_url,
		action: 'mgr/item/update',
		fields: this.getFields(config),
		keys: [{
			key: Ext.EventObject.ENTER, shift: true, fn: function () {
				this.submit()
			}, scope: this
		}]
	});
	ecc.window.UpdateItem.superclass.constructor.call(this, config);
};
Ext.extend(ecc.window.UpdateItem, MODx.Window, {

	getFields: function (config) {
		return [{
			xtype: 'hidden',
			name: 'id',
			id: config.id + '-id',
		}, {
			xtype: 'textfield',
			fieldLabel: _('ecc_item_name'),
			name: 'name',
			id: config.id + '-name',
			anchor: '99%',
			allowBlank: false,
		}, {
			xtype: 'textarea',
			fieldLabel: _('ecc_item_description'),
			name: 'description',
			id: config.id + '-description',
			anchor: '99%',
			height: 150,
		}, {
			xtype: 'xcheckbox',
			boxLabel: _('ecc_item_active'),
			name: 'active',
			id: config.id + '-active',
		}];
	}

});
Ext.reg('ecc-item-window-update', ecc.window.UpdateItem);