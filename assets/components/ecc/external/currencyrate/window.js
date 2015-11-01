currencyrate.window.CreateValute = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        title: _('create'),
        width: 550,
        url: currencyrate.config.connector_url,
        action: 'mgr/valute/create',
        baseParams: {
            namespace: eccConfig.currencyrate.namespace || '',
            path: eccConfig.currencyrate.path || ''
        }
    });

    currencyrate.window.CreateValute.superclass.constructor.call(this, config);
};
Ext.extend(currencyrate.window.CreateValute, ecc.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id'
        }, {
            xtype: 'textfield',
            fieldLabel: _('currencyrate_name'),
            name: 'name',
            anchor: '98.5%',
            allowBlank: false
        }, {
            items: [{
                layout: 'form',
                cls: 'modx-panel',
                items: [{
                    layout: 'column',
                    border: false,
                    items: [{
                        columnWidth: .485,
                        border: false,
                        layout: 'form',
                        items: this.getLeftFields(config)
                    }, {
                        columnWidth: .505,
                        border: false,
                        layout: 'form',
                        cls: 'right-column',
                        items: this.getRightFields(config)
                    }]
                }]
            }]
        }];
    },

    getLeftFields: function(config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('currencyrate_numcode'),
            name: 'numcode',
            anchor: '99%',
            allowBlank: false
        }, {
            xtype: 'numberfield',
            fieldLabel: _('currencyrate_value'),
            name: 'value',
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            fieldLabel: _('currencyrate_rate'),
            name: 'rate',
            anchor: '99%',
            allowBlank: false
        }];
    },

    getRightFields: function(config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('currencyrate_charcode'),
            name: 'charcode',
            anchor: '99%',
            allowBlank: false
        }, {
            xtype: 'numberfield',
            fieldLabel: _('currencyrate_nominal'),
            name: 'nominal',
            anchor: '99%',
            allowBlank: false
        },{
            xtype: 'textfield',
            fieldLabel: _('currencyrate_valuerate'),
            name: 'valuerate',
            anchor: '99%',
            allowBlank: true,
            disabled: true
        }];
    }

});
Ext.reg('currencyrate-valute-window-create', currencyrate.window.CreateValute);
