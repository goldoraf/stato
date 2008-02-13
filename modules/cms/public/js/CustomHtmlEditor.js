// Create user extensions namespace (Ext.ux)
Ext.namespace('Ext.ux');
 
/**
  * Ext.ux.CustomHtmlEditor Extension Class
  *
  * @author Raphaël Rougeron
  * @version 1.0
  *
  * @class Ext.ux.CustomHtmlEditor
  * @extends Ext.form.HtmlEditor
  * @constructor
  * @param {Object} config Configuration options
  */
Ext.ux.CustomHtmlEditor = function(config) {
 
    // call parent constructor
    Ext.ux.CustomHtmlEditor.superclass.constructor.call(this, config);
 
}

// extend
Ext.extend(Ext.ux.CustomHtmlEditor, Ext.form.HtmlEditor, {
    
    enableBlockFormat : true,
    
    enableIndent : true,
    
    enableTables : true,
    
    linkDialog: false,
    
    imageDialog: false,
    
    tableDialog: false,
    
    blockFormatChoices : {
        P:  'Paragraph',
        H1: 'Heading 1',
        H2: 'Heading 2',
        H3: 'Heading 3',
        H4: 'Heading 4',
        H5: 'Heading 5'
    },
    
    defaultBlockFormat : 'P',
    
    linkStyleChoices : [
        ['', 'None'],
        ['file doc-type', 'Word document'],
        ['file img-type', 'Image'],
        ['file pdf-type', 'PDF']
    ],
    
    linkAttributes : ['href', 'class', 'title'],
    
    createBlockFormatOptions : function(){
        var buf = [];
        for (i in this.blockFormatChoices) {
            buf.push(
                '<option value="',i,'">',
                    this.blockFormatChoices[i],
                '</option>'
            );
        }
        return buf.join('');
    },
    
    /**
     * Overriden method
     */
    createToolbar : function(editor){

        function btn(id, toggle, handler){
            return {
                itemId : id,
                cls : 'x-btn-icon x-edit-'+id,
                enableToggle:toggle !== false,
                scope: editor,
                handler:handler||editor.relayBtnCmd,
                clickEvent:'mousedown',
                tooltip: editor.buttonTips[id] || undefined,
                tabIndex:-1
            };
        }

        // build the toolbar
        var tb = new Ext.Toolbar({
            renderTo:this.wrap.dom.firstChild
        });

        tb.el.on('click', function(e){
            e.preventDefault();
        });

        if(this.enableFont && !Ext.isSafari){
            this.fontSelect = tb.el.createChild({
                tag:'select',
                tabIndex: -1,
                cls:'x-font-select',
                html: this.createFontOptions()
            });
            this.fontSelect.on('change', function(){
                var font = this.fontSelect.dom.value;
                this.relayCmd('fontname', font);
                this.deferFocus();
            }, this);
            tb.add(
                this.fontSelect.dom,
                '-'
            );
        };

        if(this.enableFormat){
            tb.add(
                btn('bold'),
                btn('italic'),
                btn('underline'),
                btn('strikethrough') // added
            );
        };

        if(this.enableFontSize){
            tb.add(
                '-',
                btn('increasefontsize', false, this.adjustFont),
                btn('decreasefontsize', false, this.adjustFont)
            );
        };
        
        // Added
        if(this.enableBlockFormat && !Ext.isSafari){
            this.blockFormatSelect = tb.el.createChild({
                tag:'select',
                tabIndex: -1,
                cls:'x-font-select',
                html: this.createBlockFormatOptions()
            });
            this.blockFormatSelect.on('change', function(){
                var format = this.blockFormatSelect.dom.value;
                this.relayCmd('formatblock', format);
                this.deferFocus();
            }, this);
            tb.add(
                '-',
                this.blockFormatSelect.dom
            );
        };
        // end

        if(this.enableColors){
            tb.add(
                '-', {
                    id:'forecolor',
                    cls:'x-btn-icon x-edit-forecolor',
                    clickEvent:'mousedown',
                    tooltip: editor.buttonTips['forecolor'] || undefined,
                    tabIndex:-1,
                    menu : new Ext.menu.ColorMenu({
                        allowReselect: true,
                        focus: Ext.emptyFn,
                        value:'000000',
                        plain:true,
                        selectHandler: function(cp, color){
                            this.execCmd('forecolor', Ext.isSafari || Ext.isIE ? '#'+color : color);
                            this.deferFocus();
                        },
                        scope: this,
                        clickEvent:'mousedown'
                    })
                }, {
                    id:'backcolor',
                    cls:'x-btn-icon x-edit-backcolor',
                    clickEvent:'mousedown',
                    tooltip: editor.buttonTips['backcolor'] || undefined,
                    tabIndex:-1,
                    menu : new Ext.menu.ColorMenu({
                        focus: Ext.emptyFn,
                        value:'FFFFFF',
                        plain:true,
                        allowReselect: true,
                        selectHandler: function(cp, color){
                            if(Ext.isGecko){
                                this.execCmd('useCSS', false);
                                this.execCmd('hilitecolor', color);
                                this.execCmd('useCSS', true);
                                this.deferFocus();
                            }else{
                                this.execCmd(Ext.isOpera ? 'hilitecolor' : 'backcolor', Ext.isSafari || Ext.isIE ? '#'+color : color);
                                this.deferFocus();
                            }
                        },
                        scope:this,
                        clickEvent:'mousedown'
                    })
                }
            );
        };

        if(this.enableAlignments){
            tb.add(
                '-',
                btn('justifyleft'),
                btn('justifycenter'),
                btn('justifyright')
            );
        };

        if(!Ext.isSafari){
            if(this.enableLinks){
                tb.add(
                    '-',
                    btn('createlink', false, this.openLinkDialog), // modified
                    btn('createanchor', false, this.createAnchor), // added
                    '-',
                    btn('insertimage', false, this.insertImage) // added
                );
            };
            
            // Added
            if(this.enableTables){
                tb.add(
                    btn('createtable', false, this.openTableDialog)
                );
            };
            // end

            if(this.enableLists){
                tb.add(
                    '-',
                    btn('insertorderedlist'),
                    btn('insertunorderedlist')
                );
            }
            // Added
            if(this.enableIndent){
                tb.add(
                    '-',
                    btn('indent', false),
                    btn('outdent', false)
                );
            };
            // end
            if(this.enableSourceEdit){
                tb.add(
                    '-',
                    btn('sourceedit', true, function(btn){
                        this.toggleSourceEdit(btn.pressed);
                    })
                );
            }
        }

        this.tb = tb;
    },
    
    /**
     * Overriden method
     */
    updateToolbar: function(){

        if(!this.activated){
            this.onFirstFocus();
            return;
        }

        var btns = this.tb.items.map, doc = this.doc;

        if(this.enableFont && !Ext.isSafari){
            var name = (this.doc.queryCommandValue('FontName')||this.defaultFont).toLowerCase();
            if(name != this.fontSelect.dom.value){
                this.fontSelect.dom.value = name;
            }
        }
        // Added
        if(this.enableBlockFormat && !Ext.isSafari){
            var name = (this.doc.queryCommandValue('formatblock')||this.defaultBlockFormat).toUpperCase();
            if(name != this.blockFormatSelect.dom.value){
                this.blockFormatSelect.dom.value = name;
            }
        }
        // end
        if(this.enableFormat){
            btns.bold.toggle(doc.queryCommandState('bold'));
            btns.italic.toggle(doc.queryCommandState('italic'));
            btns.underline.toggle(doc.queryCommandState('underline'));
            btns.strikethrough.toggle(doc.queryCommandState('strikethrough')); // added
        }
        if(this.enableAlignments){
            btns.justifyleft.toggle(doc.queryCommandState('justifyleft'));
            btns.justifycenter.toggle(doc.queryCommandState('justifycenter'));
            btns.justifyright.toggle(doc.queryCommandState('justifyright'));
        }
        // Added
        /*if(this.enableIndent){
            btns.indent.toggle(doc.queryCommandState('indent'));
            btns.outdent.toggle(doc.queryCommandState('outdent'));
        }*/
        // end
        if(!Ext.isSafari){
            // Moved
            if (this.enableLists) {
                btns.insertorderedlist.toggle(doc.queryCommandState('insertorderedlist'));
                btns.insertunorderedlist.toggle(doc.queryCommandState('insertunorderedlist'));
            }
            // end
            
            // Added
            if(this.enableLinks){
                var selElt = this.getSelectedElement();
                if (selElt.tagName == 'A') {
                    if (selElt.hasAttribute('name')) {
                        btns.createanchor.toggle(true);
                    } else {
                        btns.createlink.toggle(true);
                    }
                } else {
                    btns.createanchor.toggle(false);
                    btns.createlink.toggle(false);
                }
            };
            // end
        }
        Ext.menu.MenuMgr.hideAll();

        this.syncValue();
    },
    
    /**
     * Overriden method
     */
    getDocMarkup : function(){
        return '<html><head><style type="text/css">body{border:0;margin:0;padding:3px;height:98%;cursor:text;}</style>'
        + '</head><body></body></html>';
    },
    
    createTable : function(){
        if (!this.tableDialog.form.isValid()) {
            return;
        }
        var table = document.createElement("table");
        var tbody = document.createElement("tbody");
        var values = this.tableDialog.form.getValues();

        for (var j = 0; j < values['rows']; j++) {
            var currentRow = document.createElement("tr");

            for (var i = 0; i < values['cols']; i++) {
                var currentCell = document.createElement("td");
                if (Ext.isGecko) {
                    // we need to append a bogus <br>
                    var br = document.createElement("br");
                    br.setAttribute( 'type', '_moz' );
                    currentCell.appendChild(br);
                }
                currentRow.appendChild(currentCell);
            }
            tbody.appendChild(currentRow);
        }

        table.appendChild(tbody);
        var selElt = this.getSelectedElement();
        selElt.parentNode.insertBefore(table, selElt.nextSibling);
        
        table.setAttribute("border", values['border']);
        table.setAttribute("width", values['width']);
        table.setAttribute("cellpadding", values['padding']);
        table.setAttribute("cellspacing", values['spacing']);
        
        if (values['height'] != '') {
            table.setAttribute("height", values['height']);
        }
        
        this.tableDialog.form.stopMonitoring();
        this.tableDialog.hide();
    },
    
    createAnchor : function(){
        var selElt = this.getSelectedElement();
        if (selElt.tagName == 'A' && selElt.hasAttribute('name')) {
            var name = prompt('Anchor name', selElt.getAttribute('name'));
            if (name){
                selElt.setAttribute('name', name);
            }
        } else {
            var name = prompt('Anchor name', '');
            if (name){
                this.insertAtCursor('<a name="' + name + '" title="' + name + '"></a>');
                // IE needs a fake image in order to display something... (cf FCKDocumentProcessor)
                // Nevermind...
            }
        }
    },
    
    insertImage : function(){
        if (!this.imageDialog) {
            this.insertImageDialog();
        }
        this.imageDialog.show();
    },
    
    /**
     * Overriden method
     */
    createLink : function(values){
        var selText = this.getSelectedText();
        if (!selText) {
            this.linkDialog.hide();
            return;
        }
        var attributes = [];
        for (i in values) {
            if (values[i] != '') {
                attributes.push(i+'="'+values[i]+'"');
            }
        }
        this.insertAtCursor('<a ' + attributes.join(' ') + '>' + selText + '</a>');
        this.linkDialog.destroy();
    },
    
    openLinkDialog : function(){
        this.linkDialog = new InsertLinkWindow();
        
        var selElt = this.getSelectedElement();
        if (selElt.tagName.toLowerCase() == 'a') {
            for (i in this.linkAttributes) {
                if (selElt.hasAttribute(this.linkAttributes[i])) {
                    // ugly, but it works...
                    Ext.getCmp('link-'+this.linkAttributes[i]).setValue(selElt.getAttribute(this.linkAttributes[i]));
                }
            }
        }
        this.linkDialog.on('linkselect', function(values){
            this.createLink(values);
        }.createDelegate(this));
        this.linkDialog.show();
    },
    
    openTableDialog : function(){
        if (!this.tableDialog) {
            this.createTableDialog();
        }
        this.tableDialog.form.reset();
        this.tableDialog.form.startMonitoring();
        this.tableDialog.show();
    },
    
    createTableDialog : function() {
        this.tableDialog = new Ext.BasicDialog("table-dlg", {
            modal:true,
            autoTabs:false,
            width:300,
            height:210,
            shadow:true,
            minWidth:300,
            minHeight:210
        });
        this.tableDialog.addKeyListener(27, this.tableDialog.hide, this.tableDialog);
        this.tableDialog.addButton('Submit', this.createTable, this);
        
        this.tableDialog.form = new Ext.form.Form({
        	labelAlign: 'left',
        	labelWidth: 75,
        	buttonAlign: 'right'
        });
        this.tableDialog.form.column(
            {}, 
            new Ext.form.TextField({fieldLabel: 'Rows', name: 'rows', value: '3', width:30, allowBlank:false}),
            new Ext.form.TextField({fieldLabel: 'Cols', name: 'cols', value: '2', width:30, allowBlank:false}),
            new Ext.form.TextField({fieldLabel: 'Border', name: 'border', value: '1', width:30, allowBlank:false}),
            new Ext.form.TextField({fieldLabel: 'Spacing', name: 'spacing', value: '1', width:30, allowBlank:false}),
            new Ext.form.TextField({fieldLabel: 'Padding', name: 'padding', value: '1', width:30, allowBlank:false})
        );
        this.tableDialog.form.column(
            {}, 
            new Ext.form.TextField({fieldLabel: 'Width', name: 'width', value: '200', width:60, allowBlank:false}),
            new Ext.form.TextField({fieldLabel: 'Height', name: 'height', width:60, allowBlank:true})
        );
        this.tableDialog.form.render('createtable-form');
    },
    
    insertImageDialog : function() {
        this.imageDialog = new Ext.BasicDialog("image-dlg", {
            modal:true,
            autoTabs:true,
            width:500,
            height:300,
            shadow:true,
            minWidth:300,
            minHeight:300
        });
        this.imageDialog.addKeyListener(27, this.imageDialog.hide, this.imageDialog);
        //this.imageDialog.addButton('Cancel', this.imageDialog.hide, this.imageDialog);
        this.imageDialog.addButton('Submit', this.imageDialog.hide, this.imageDialog).disable();
        
        var fileTree = statoCms.app.createTree('image-file-tree', 'http://fda/admin/files/nodes');
        fileTree.on('click', function(node, e){this.dump(node.attributes)
            if (node.attributes.cls == 'img-type' || confirm('Ce fichier ne semble pas être une image. Etes-vous sûr(e) de vouloir continuer ?')) {
                this.relayCmd('insertimage', '/documents/'+node.attributes.path);
                this.imageDialog.hide();
            }
        }.createDelegate(this));
    },
    
    getSelectedText : function() {
        if(Ext.isIE){
            this.win.focus();
            var selText = this.doc.selection.createRange().text;
            this.deferFocus();
        }else if(Ext.isGecko || Ext.isOpera){
            this.win.focus();
            var selText = this.win.getSelection().toString();
            this.deferFocus();
        }else if(Ext.isSafari){
            // ???
            this.deferFocus();
        }
        return selText;
    },
    
    getSelectedElement : function() {
        var selElt;
        if(Ext.isIE){
            this.win.focus();
            selElt = this.doc.selection.createRange().parentElement();
            this.deferFocus();
        }else if(Ext.isGecko || Ext.isOpera){
            this.win.focus();
            var sel = this.win.getSelection();
            var rng = (sel.rangeCount > 0) ? sel.getRangeAt(0) : null;
            
            if (!sel || !rng)
                selElt = null;
            
            var elt = rng.commonAncestorContainer;

            // Handle selection of an image or other control like element such as anchors
            if (!rng.collapsed && (rng.startContainer == rng.endContainer)
                && (rng.startOffset - rng.endOffset < 2) && rng.startContainer.hasChildNodes()) {
                    elt = rng.startContainer.childNodes[rng.startOffset];
            }
            if (elt.nodeType == 1) {
                selElt = elt;
            } else {
                selElt = elt.parentNode;
            }
            this.deferFocus();
        }
        return selElt;
    }
});

InsertLinkWindow = function() {
    this.linkStyleChoices = [
        ['', 'None'],
        ['file doc-type', 'Word document'],
        ['file img-type', 'Image'],
        ['file pdf-type', 'PDF']
    ],
    
    this.form = new Ext.form.FormPanel({
        id:'link-form',
        region:'north',
        labelAlign: 'left',
    	labelWidth: 75,
    	autoHeight:true,
    	split: true,
    	buttonAlign: 'right',
        defaultType: 'textfield',
        defaults:{bodyStyle:'padding:10px'},
        items: [{
            fieldLabel: 'URL',
            name: 'href',
            id: 'link-href',
            width:190
        },{
            fieldLabel: 'Title',
            name: 'title',
            id: 'link-title',
            width:190
        }, new Ext.form.ComboBox({
            fieldLabel: 'Class',
            hiddenName: 'class',
            id: 'link-class',
            store: new Ext.data.SimpleStore({
                fields: ['class', 'class_name'],
                data : this.linkStyleChoices
            }),
            displayField:'class_name',
            valueField:'class',
            typeAhead: true,
            mode: 'local',
            triggerAction: 'all',
            emptyText:'Select a style...',
            selectOnFocus:true,
            //editable:false,
            width:190
        })]
    });
    
    this.pageTree = new Ext.tree.TreePanel({
        id:'page-tree',
        title:'Pages',
        rootVisible:true,
        lines:false,
        autoScroll:true,
        loader: new Ext.tree.TreeLoader({dataUrl:StatoCms.BASE_URI+'/pages/nodes'}),
        root: new Ext.tree.AsyncTreeNode({
                text: 'Racine du site',
                draggable:false,
                id:'source'
            })
    });
    this.pageTree.on('click', function(node, e){
        this.form.getForm().findField('href').setValue('/'+node.attributes.path);
    }.createDelegate(this));
    
    this.fileTree = new Ext.tree.TreePanel({
        id:'file-tree',
        title:'Files',
        rootVisible:true,
        lines:false,
        autoScroll:true,
        loader: new Ext.tree.TreeLoader({dataUrl:StatoCms.BASE_URI+'/files/nodes'}),
        root: new Ext.tree.AsyncTreeNode({
                text: 'Racine du site',
                draggable:false,
                id:'source'
            })
    });
    // Users shouldn't be able to select a folder, but only leaves (files)
    this.fileTree.on('beforeclick', function(node, e){
        return node.leaf ? true : false;
    });
    this.fileTree.on('click', function(node, e){
        this.form.getForm().findField('href').setValue('/documents/'+node.attributes.path);
        this.form.getForm().findField('class').setValue('file '+node.attributes.cls);
    }.createDelegate(this));
    
    this.tabs = new Ext.TabPanel({
        region: 'center',
        margins:'3 3 3 0', 
        activeTab: 0,
        defaults:{autoScroll:true},
        items:[this.pageTree, this.fileTree]
    });
    
    InsertLinkWindow.superclass.constructor.call(this, {
        title: 'Insert Link',
        closable:true,
        width:500,
        height:300,
        //border:false,
        plain:true,
        layout: 'border',
        items: [this.form, this.tabs],
        buttons: [{
            text:'Submit',
            handler: function(){
                this.fireEvent('linkselect', this.form.getForm().getValues());
            },
            scope: this
        }]
    });
};

Ext.extend(InsertLinkWindow, Ext.Window, {
    
});
