!function(e){var t={};function r(n){if(t[n])return t[n].exports;var o=t[n]={i:n,l:!1,exports:{}};return e[n].call(o.exports,o,o.exports,r),o.l=!0,o.exports}r.m=e,r.c=t,r.d=function(e,t,n){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(r.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)r.d(n,o,function(t){return e[t]}.bind(null,o));return n},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="/",r(r.s=2)}([,,function(e,t,r){e.exports=r(3)},function(e,t){var r=void 0!==window.CategoriesIndexLocale&&window.CategoriesIndexLocale;if(!r)throw new Error("CategoriesIndexLocale locale not loaded.");jQuery((function(e){"use strict";window.AppLocale;var t=!1,r=new DropifyImageUploader(e("#category_image_field"));t||(r.setup(),t=!0)})),jQuery((function(e){"use strict";var t={};e(".category-form").each((function(n,o){var i=e(o),a=e(".js-text-editor",i);t[i.attr("id")]=[],a.each((function(n,o){var a=e(o).attr("id");t[i.attr("id")].push(a),ValPressTextEditor.register(a,new Quill("#"+a+"-editor",{modules:{toolbar:[[{header:[!1]}],["bold","italic","underline"]]},scrollingContainer:".quill-scrolling-container",placeholder:r.description_placeholder,theme:"bubble"}))}))})).on("submit",(function(r){t[e(this).attr("id")].map((function(t,r){return e("#"+t).val(ValPressTextEditor.getHTML(t)),t}))}))}))}]);