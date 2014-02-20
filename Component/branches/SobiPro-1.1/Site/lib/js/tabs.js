function hasSupport(){if(typeof hasSupport.support!="undefined")return hasSupport.support;var b=/msie 5\.[56789]/i.test(navigator.userAgent);hasSupport.support=(typeof document.implementation!="undefined"&&document.implementation.hasFeature("html","1.0")||b);if(b){document._getElementsByTagName=document.getElementsByTagName;document.getElementsByTagName=function(a){if(a=="*")return document.all;else return document._getElementsByTagName(a)}}return hasSupport.support}function WebFXTabPane(a,b){if(!hasSupport()||a==null)return;this.element=a;this.element.tabPane=this;this.pages=[];this.selectedIndex=null;this.useCookie=b!=null?b:true;this.element.className=this.classNameTag+" "+this.element.className;this.tabRow=document.createElement("div");this.tabRow.className="tab-row";a.insertBefore(this.tabRow,a.firstChild);var c=0;if(this.useCookie){c=Number(WebFXTabPane.getCookie("webfxtab_"+this.element.id));if(isNaN(c))c=0}this.selectedIndex=c;var d=a.childNodes;var n;for(var i=0;i<d.length;i++){if(d[i].nodeType==1&&d[i].className=="tab-page"){this.addTabPage(d[i])}}}WebFXTabPane.prototype.classNameTag="dynamic-tab-pane-control";WebFXTabPane.prototype.setSelectedIndex=function(n){if(this.selectedIndex!=n){if(this.selectedIndex!=null&&this.pages[this.selectedIndex]!=null)this.pages[this.selectedIndex].hide();this.selectedIndex=n;this.pages[this.selectedIndex].show();if(this.useCookie)WebFXTabPane.setCookie("webfxtab_"+this.element.id,n)}};WebFXTabPane.prototype.getSelectedIndex=function(){return this.selectedIndex};WebFXTabPane.prototype.addTabPage=function(a){if(!hasSupport())return;if(a.tabPage==this)return a.tabPage;var n=this.pages.length;var b=this.pages[n]=new WebFXTabPage(a,this,n);b.tabPane=this;this.tabRow.appendChild(b.tab);if(n==this.selectedIndex)b.show();else b.hide();return b};WebFXTabPane.prototype.dispose=function(){this.element.tabPane=null;this.element=null;this.tabRow=null;for(var i=0;i<this.pages.length;i++){this.pages[i].dispose();this.pages[i]=null}this.pages=null};WebFXTabPane.setCookie=function(a,b,c){var e="";if(c){var d=new Date();d.setTime(d.getTime()+c*24*60*60*1000);e="; expires="+d.toGMTString()}document.cookie=a+"="+b+e+"; path=/"};WebFXTabPane.getCookie=function(a){var b=new RegExp("(\;|^)[^;]*("+a+")\=([^;]*)(;|$)");var c=b.exec(document.cookie);return c!=null?c[3]:null};WebFXTabPane.removeCookie=function(a){setCookie(a,"",-1)};function WebFXTabPage(b,c,d){if(!hasSupport()||b==null)return;this.element=b;this.element.tabPage=this;this.index=d;var e=b.childNodes;for(var i=0;i<e.length;i++){if(e[i].nodeType==1&&e[i].className=="tab"){this.tab=e[i];break}}var a=document.createElement("A");this.aElement=a;a.href="#";a.onclick=function(){return false};while(this.tab.hasChildNodes())a.appendChild(this.tab.firstChild);this.tab.appendChild(a);var f=this;this.tab.onclick=function(){f.select()};this.tab.onmouseover=function(){WebFXTabPage.tabOver(f)};this.tab.onmouseout=function(){WebFXTabPage.tabOut(f)}}WebFXTabPage.prototype.show=function(){var a=this.tab;var s=a.className+" selected";s=s.replace(/ +/g," ");a.className=s;this.element.style.display="block"};WebFXTabPage.prototype.hide=function(){var a=this.tab;var s=a.className;s=s.replace(/ selected/g,"");a.className=s;this.element.style.display="none"};WebFXTabPage.prototype.select=function(){this.tabPane.setSelectedIndex(this.index)};WebFXTabPage.prototype.dispose=function(){this.aElement.onclick=null;this.aElement=null;this.element.tabPage=null;this.tab.onclick=null;this.tab.onmouseover=null;this.tab.onmouseout=null;this.tab=null;this.tabPane=null;this.element=null};WebFXTabPage.tabOver=function(a){var b=a.tab;var s=b.className+" hover";s=s.replace(/ +/g," ");b.className=s};WebFXTabPage.tabOut=function(a){var b=a.tab;var s=b.className;s=s.replace(/ hover/g,"");b.className=s};function setupAllTabs(){if(!hasSupport())return;var a=document.getElementsByTagName("*");var l=a.length;var b=/tab\-pane/;var c=/tab\-page/;var d,el;var e;for(var i=0;i<l;i++){el=a[i];d=el.className;if(d=="")continue;if(b.test(d)&&!el.tabPane)new WebFXTabPane(el);else if(c.test(d)&&!el.tabPage&&b.test(el.parentNode.className)){el.parentNode.tabPane.addTabPage(el)}}}function disposeAllTabs(){if(!hasSupport())return;var a=document.getElementsByTagName("*");var l=a.length;var b=/tab\-pane/;var c,el;var d=[];for(var i=0;i<l;i++){el=a[i];c=el.className;if(c=="")continue;if(b.test(c)&&el.tabPane)d[d.length]=el.tabPane}for(var i=d.length-1;i>=0;i--){d[i].dispose();d[i]=null}}if(typeof window.addEventListener!="undefined")window.addEventListener("load",setupAllTabs,false);else if(typeof window.attachEvent!="undefined"){window.attachEvent("onload",setupAllTabs);window.attachEvent("onunload",disposeAllTabs)}else{if(window.onload!=null){var oldOnload=window.onload;window.onload=function(e){oldOnload(e);setupAllTabs()}}else window.onload=setupAllTabs}