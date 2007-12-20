/*
 * Ext JS Library 1.1.1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */

Ext.form.TextArea=function(A){Ext.form.TextArea.superclass.constructor.call(this,A);if(this.minHeight!==undefined){this.growMin=this.minHeight}if(this.maxHeight!==undefined){this.growMax=this.maxHeight}};Ext.extend(Ext.form.TextArea,Ext.form.TextField,{growMin:60,growMax:1000,preventScrollbars:false,onRender:function(B,A){if(!this.el){this.defaultAutoCreate={tag:"textarea",style:"width:300px;height:60px;",autocomplete:"off"}}Ext.form.TextArea.superclass.onRender.call(this,B,A);if(this.grow){this.textSizeEl=Ext.DomHelper.append(document.body,{tag:"pre",cls:"x-form-grow-sizer"});if(this.preventScrollbars){this.el.setStyle("overflow","hidden")}this.el.setHeight(this.growMin)}},onDestroy:function(){if(this.textSizeEl){this.textSizeEl.parentNode.removeChild(this.textSizeEl)}Ext.form.TextArea.superclass.onDestroy.call(this)},onKeyUp:function(A){if(!A.isNavKeyPress()||A.getKey()==A.ENTER){this.autoSize()}},autoSize:function(){if(!this.grow||!this.textSizeEl){return }var C=this.el;var A=C.dom.value;var D=this.textSizeEl;D.innerHTML="";D.appendChild(document.createTextNode(A));A=D.innerHTML;Ext.fly(D).setWidth(this.el.getWidth());if(A.length<1){A="&#160;&#160;"}else{if(Ext.isIE){A=A.replace(/\n/g,"<p>&#160;</p>")}A+="&#160;\n&#160;"}D.innerHTML=A;var B=Math.min(this.growMax,Math.max(D.offsetHeight,this.growMin));if(B!=this.lastHeight){this.lastHeight=B;this.el.setHeight(B);this.fireEvent("autosize",this,B)}}});