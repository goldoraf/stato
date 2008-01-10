/*
 * Ext JS Library 1.1.1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */

Ext.data.Store=function(A){this.data=new Ext.util.MixedCollection(false);this.data.getKey=function(B){return B.id};this.baseParams={};this.paramNames={"start":"start","limit":"limit","sort":"sort","dir":"dir"};if(A&&A.data){this.inlineData=A.data;delete A.data}Ext.apply(this,A);if(this.reader){if(!this.recordType){this.recordType=this.reader.recordType}if(this.reader.onMetaChange){this.reader.onMetaChange=this.onMetaChange.createDelegate(this)}}if(this.recordType){this.fields=this.recordType.prototype.fields}this.modified=[];this.addEvents({datachanged:true,metachange:true,add:true,remove:true,update:true,clear:true,beforeload:true,load:true,loadexception:true});if(this.proxy){this.relayEvents(this.proxy,["loadexception"])}this.sortToggle={};Ext.data.Store.superclass.constructor.call(this);if(this.inlineData){this.loadData(this.inlineData);delete this.inlineData}};Ext.extend(Ext.data.Store,Ext.util.Observable,{remoteSort:false,pruneModifiedRecords:false,lastOptions:null,add:function(B){B=[].concat(B);for(var D=0,A=B.length;D<A;D++){B[D].join(this)}var C=this.data.length;this.data.addAll(B);this.fireEvent("add",this,B,C)},remove:function(A){var B=this.data.indexOf(A);this.data.removeAt(B);if(this.pruneModifiedRecords){this.modified.remove(A)}this.fireEvent("remove",this,A,B)},removeAll:function(){this.data.clear();if(this.pruneModifiedRecords){this.modified=[]}this.fireEvent("clear",this)},insert:function(C,B){B=[].concat(B);for(var D=0,A=B.length;D<A;D++){this.data.insert(C,B[D]);B[D].join(this)}this.fireEvent("add",this,B,C)},indexOf:function(A){return this.data.indexOf(A)},indexOfId:function(A){return this.data.indexOfKey(A)},getById:function(A){return this.data.key(A)},getAt:function(A){return this.data.itemAt(A)},getRange:function(B,A){return this.data.getRange(B,A)},storeOptions:function(A){A=Ext.apply({},A);delete A.callback;delete A.scope;this.lastOptions=A},load:function(B){B=B||{};if(this.fireEvent("beforeload",this,B)!==false){this.storeOptions(B);var C=Ext.apply(B.params||{},this.baseParams);if(this.sortInfo&&this.remoteSort){var A=this.paramNames;C[A["sort"]]=this.sortInfo.field;C[A["dir"]]=this.sortInfo.direction}this.proxy.load(C,this.reader,this.loadRecords,this,B)}},reload:function(A){this.load(Ext.applyIf(A||{},this.lastOptions))},loadRecords:function(G,B,F){if(!G||F===false){if(F!==false){this.fireEvent("load",this,[],B)}if(B.callback){B.callback.call(B.scope||this,[],B,false)}return }var E=G.records,D=G.totalRecords||E.length;if(!B||B.add!==true){if(this.pruneModifiedRecords){this.modified=[]}for(var C=0,A=E.length;C<A;C++){E[C].join(this)}if(this.snapshot){this.data=this.snapshot;delete this.snapshot}this.data.clear();this.data.addAll(E);this.totalLength=D;this.applySort();this.fireEvent("datachanged",this)}else{this.totalLength=Math.max(D,this.data.length+E.length);this.add(E)}this.fireEvent("load",this,E,B);if(B.callback){B.callback.call(B.scope||this,E,B,true)}},loadData:function(C,A){var B=this.reader.readRecords(C);this.loadRecords(B,{add:A},true)},getCount:function(){return this.data.length||0},getTotalCount:function(){return this.totalLength||0},getSortState:function(){return this.sortInfo},applySort:function(){if(this.sortInfo&&!this.remoteSort){var C=this.sortInfo,D=C.field;var A=this.fields.get(D).sortType;var B=function(F,E){var H=A(F.data[D]),G=A(E.data[D]);return H>G?1:(H<G?-1:0)};this.data.sort(C.direction,B);if(this.snapshot&&this.snapshot!=this.data){this.snapshot.sort(C.direction,B)}}},setDefaultSort:function(B,A){this.sortInfo={field:B,direction:A?A.toUpperCase():"ASC"}},sort:function(C,A){var B=this.fields.get(C);if(!A){if(this.sortInfo&&this.sortInfo.field==B.name){A=(this.sortToggle[B.name]||"ASC").toggle("ASC","DESC")}else{A=B.sortDir}}this.sortToggle[B.name]=A;this.sortInfo={field:B.name,direction:A};if(!this.remoteSort){this.applySort();this.fireEvent("datachanged",this)}else{this.load(this.lastOptions)}},each:function(B,A){this.data.each(B,A)},getModifiedRecords:function(){return this.modified},createFilterFn:function(B,A,C){if(!A.exec){A=String(A);if(A.length==0){return false}A=new RegExp((C===true?"":"^")+Ext.escapeRe(A),"i")}return function(D){return A.test(D.data[B])}},sum:function(E,F,A){var C=this.data.items,B=0;F=F||0;A=(A||A===0)?A:C.length-1;for(var D=F;D<=A;D++){B+=(C[D].data[E]||0)}return B},filter:function(C,B,D){var A=this.createFilterFn(C,B,D);return A?this.filterBy(A):this.clearFilter()},filterBy:function(B,A){this.snapshot=this.snapshot||this.data;this.data=this.queryBy(B,A||this);this.fireEvent("datachanged",this)},query:function(C,B,D){var A=this.createFilterFn(C,B,D);return A?this.queryBy(A):this.data.clone()},queryBy:function(B,A){var C=this.snapshot||this.data;return C.filterBy(B,A||this)},collect:function(G,H,B){var F=(B===true&&this.snapshot)?this.snapshot.items:this.data.items;var I,J,A=[],C={};for(var D=0,E=F.length;D<E;D++){I=F[D].data[G];J=String(I);if((H||!Ext.isEmpty(I))&&!C[J]){C[J]=true;A[A.length]=I}}return A},clearFilter:function(A){if(this.snapshot&&this.snapshot!=this.data){this.data=this.snapshot;delete this.snapshot;if(A!==true){this.fireEvent("datachanged",this)}}},afterEdit:function(A){if(this.modified.indexOf(A)==-1){this.modified.push(A)}this.fireEvent("update",this,A,Ext.data.Record.EDIT)},afterReject:function(A){this.modified.remove(A);this.fireEvent("update",this,A,Ext.data.Record.REJECT)},afterCommit:function(A){this.modified.remove(A);this.fireEvent("update",this,A,Ext.data.Record.COMMIT)},commitChanges:function(){var B=this.modified.slice(0);this.modified=[];for(var C=0,A=B.length;C<A;C++){B[C].commit()}},rejectChanges:function(){var B=this.modified.slice(0);this.modified=[];for(var C=0,A=B.length;C<A;C++){B[C].reject()}},onMetaChange:function(B,A,C){this.recordType=A;this.fields=A.prototype.fields;delete this.snapshot;this.sortInfo=B.sortInfo;this.modified=[];this.fireEvent("metachange",this,this.reader.meta)}});