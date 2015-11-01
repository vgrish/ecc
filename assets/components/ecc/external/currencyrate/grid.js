currencyrate.grid.List = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'currencyrate-grid-list';
    }
    Ext.applyIf(config, {
        url: currencyrate.config.connector_url,
        baseParams: {
            action: 'mgr/valute/getlist',
            namespace: eccConfig.currencyrate.namespace || '',
            path: eccConfig.currencyrate.path || ''
        },
        multi_select: true,
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            getRowClass: function(rec, ri, p) {
                return !rec.data.active ? 'currencyrate-row-disabled' : '';
            }
        },
        pageSize: 5
    });
    currencyrate.grid.List.superclass.constructor.call(this, config);

    /* Clear selection on grid refresh */
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(currencyrate.grid.List, ecc.grid.Default, {

    getFields: function(config) {
        return [
            'id', 'numcode', 'charcode', 'name', 'value', 'nominal', 'rate', 'valuerate', 'active', 'actions'
        ];
    },

    getColumns: function(config) {
        var columns = [ /*this.exp, this.sm*/ ];
        var add = {
            id: {
                width: 15,
                sortable: true
            },
            numcode: {
                width: 20,
                sortable: true,
                editor: {
                    xtype: 'textfield'
                }
            },
            charcode: {
                width: 20,
                sortable: true,
                editor: {
                    xtype: 'textfield'
                }
            },
            name: {
                width: 50,
                sortable: true,
                editor: {
                    xtype: 'textfield'
                }
            },
            value: {
                width: 20,
                sortable: true
            },
            nominal: {
                width: 20,
                sortable: true
            },
            rate: {
                width: 20,
                sortable: true,
                editor: {
                    xtype: 'textfield'
                }
            },
            valuerate: {
                width: 20,
                sortable: true,
                decimalPrecision: 4
            },
            actions: {
                width: 20,
                sortable: false,
                renderer: currencyrate.utils.renderActions,
                id: 'actions'
            }
        };

        for (var field in add) {
            if (add[field]) {
                Ext.applyIf(add[field], {
                    header: _('currencyrate_header_' + field),
                    tooltip: _('currencyrate_tooltip_' + field),
                    dataIndex: field
                });
                columns.push(add[field]);
            }
        }

        return columns;
    },

    getTopBar: function (config) {
        var tbar = [];

        tbar.push({
            text: '<i class="fa fa-cogs"></i> ',
            menu: [{
                text: '<i class="fa fa-plus"></i> ' + _('currencyrate_create'),
                cls: 'currencyrate-cogs',
                handler: this.create,
                scope: this
            }, '-', {
                text: '<i class="fa fa-refresh"></i> ' + _('currencyrate_index_create'),
                cls: 'currencyrate-cogs',
                handler: this.indexCreate,
                scope: this
            }, {
                text: '<i class="fa fa-trash-o"></i> ' + _('currencyrate_index_clear'),
                cls: 'currencyrate-cogs',
                handler: this.indexClear,
                scope: this
            }]
        });

        return tbar;
    },

    setAction: function(method, field, value) {
        var ids = this._getSelectedIds();
        if (!ids.length && (field !== 'false')) {
            return false;
        }
        MODx.Ajax.request({
            url: currencyrate.config.connector_url,
            params: {
                action: 'mgr/valute/multiple',
                namespace: eccConfig.currencyrate.namespace || '',
                path: eccConfig.currencyrate.path || '',
                method: method,
                field_name: field,
                field_value: value,
                ids: Ext.util.JSON.encode(ids)
            },
            listeners: {
                success: {
                    fn: function(response) {
                        this.refresh();
                        if (response.message != '') {
                            MODx.msg.alert(_('info'), response.message);
                        }
                    },
                    scope: this
                },
                failure: {
                    fn: function(response) {
                        MODx.msg.alert(_('error'), response.message);
                    },
                    scope: this
                }
            }
        })
    },

    indexCreate: function() {
        var el = this.getEl();
        el.mask(_('loading'), 'x-mask-loading');
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/index/create',
                namespace: eccConfig.currencyrate.namespace || '',
                path: eccConfig.currencyrate.path || ''
            },
            listeners: {
                success: {
                    fn: function() {
                        this.refresh();
                        el.unmask();
                    },
                    scope: this
                }
            }
        })
    },

    indexClear: function(btn, e) {
        MODx.msg.confirm({
            title: _('currencyrate_index_remove_all'),
            text: _('currencyrate_index_remove_all_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/index/clear',
                namespace: eccConfig.currencyrate.namespace || '',
                path: eccConfig.currencyrate.path || ''
            },
            listeners: {
                success: {
                    fn: function(r) {
                        this.refresh();
                    },
                    scope: this
                }
            }
        });
    },

    remove: function() {
        Ext.MessageBox.confirm(
            _('currencyrate_action_remove'),
            _('currencyrate_confirm_remove'),
            function(val) {
                if (val == 'yes') {
                    this.setAction('remove');
                }
            },
            this
        );
    },

    create: function(btn, e) {
        var record = {
            active: 1
        };
        var w = MODx.load({
            xtype: 'currencyrate-valute-window-create',
            class: this.config.class,
            listeners: {
                success: {
                    fn: function() {
                        this.refresh();
                    },
                    scope: this
                }
            }
        });
        w.reset();
        w.setValues(record);
        w.show(e.target);
    },

    update: function(btn, e, row) {
        var record = typeof(row) != 'undefined' ? row.data : this.menu.record;
        MODx.Ajax.request({
            url: currencyrate.config.connector_url,
            params: {
                action: 'mgr/valute/get',
                namespace: eccConfig.currencyrate.namespace || '',
                path: eccConfig.currencyrate.path || '',
                id: record.id
            },
            listeners: {
                success: {
                    fn: function(r) {
                        var record = r.object;
                        var w = MODx.load({
                            xtype: 'currencyrate-valute-window-create',
                            title: _('currencyrate_action_update'),
                            action: 'mgr/valute/update',
                            record: record,
                            listeners: {
                                success: {
                                    fn: this.refresh,
                                    scope: this
                                }
                            }
                        });
                        w.reset();
                        w.setValues(record);
                        w.show(e.target);
                    },
                    scope: this
                }
            }
        });
    }

});
Ext.reg('currencyrate-grid-list', currencyrate.grid.List);