/*
 * Ext JS Library 1.1.1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */

Ext.menu.ColorItem=function(A){Ext.menu.ColorItem.superclass.constructor.call(this,new Ext.ColorPalette(A),A);this.palette=this.component;this.relayEvents(this.palette,["select"]);if(this.selectHandler){this.on("select",this.selectHandler,this.scope)}};Ext.extend(Ext.menu.ColorItem,Ext.menu.Adapter);