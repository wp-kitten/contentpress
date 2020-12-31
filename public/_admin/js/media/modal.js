/*! For license information please see modal.js.LICENSE.txt */
!function(e){var a={};function t(n){if(a[n])return a[n].exports;var o=a[n]={i:n,l:!1,exports:{}};return e[n].call(o.exports,o,o.exports,t),o.l=!0,o.exports}t.m=e,t.c=a,t.d=function(e,a,n){t.o(e,a)||Object.defineProperty(e,a,{enumerable:!0,get:n})},t.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},t.t=function(e,a){if(1&a&&(e=t(e)),8&a)return e;if(4&a&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(t.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&a&&"string"!=typeof e)for(var o in e)t.d(n,o,function(a){return e[a]}.bind(null,o));return n},t.n=function(e){var a=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(a,"a",a),a},t.o=function(e,a){return Object.prototype.hasOwnProperty.call(e,a)},t.p="/",t(t.s=40)}({40:function(e,a,t){e.exports=t(41)},41:function(e,a){var t=void 0!==window.MediaLocale&&window.MediaLocale;if(!t)throw new Error("MediaLocale locale not loaded.");jQuery((function(e){"use strict";var a=window.AppLocale,n=e("#mediaModal"),o=null,r=null,i=!1,s=function(a,t){o=e(t.data("imageTarget")),r=e(t.data("inputTarget")),e(".js-valpress-thumbnail",a).on("click",(function(a){a.preventDefault(),a.stopPropagation();var t=e(this),n=e("img",t);r.val(t.data("id")),o.attr("src",n.attr("src")).removeClass("hidden")}))};e(".js-preview-image-delete").on("click",(function(a){a.preventDefault(),a.stopPropagation();var t=e(this).parents(".js-image-preview");e('input[type="hidden"]',t).val(""),e("img",t).addClass("hidden").attr("src","")}));var d=!1,l=new DropifyImageUploader(e("#dropify_image_field"));n.on("show.bs.modal",(function(n){var u=e(n.relatedTarget),c=e(this),f=c.find(".modal-body").find(".js-content"),p=f.find(".valpress-media-list");c.find(".modal-title").text(t.text_media),i||(s(c,u),i=!0),f.removeClass("hidden"),d||(l.setup({on_add_image:function(n){var i=n.element[0].files[0];if(i.length<1)return!1;var d=new FormData;d.append("action","media_upload_image"),d.append("media_image",i),d.append(a.nonce_name,a.nonce_value),e.ajax({url:a.ajax.url,method:"POST",async:!0,timeout:29e3,data:d,processData:!1,contentType:!1}).done((function(e){if(e)if(e.success)if(e.data){n.ajaxResponse=e.data;var i='<div class="item js--item" data-id="__FILE_ID__">\n<a href="#" class="js-valpress-thumbnail thumbnail" data-id="__FILE_ID__">\n     <img src="__FILE_URL__" alt="" class="valpress-thumbnail"/>\n</a>\n</div>';i=i.replace(/__FILE_ID__/g,e.data.id).replace("__FILE_URL__",e.data.url),p.prepend(i),p.find(".js--info").addClass("hidden"),s(p,u),r.val(e.data.id),o.attr("src",e.data.url).removeClass("hidden"),showToast(t.text_image_set,"success")}else showToast(a.ajax.empty_response,"warning");else e.data?showToast(e.data,"warning"):showToast(a.ajax.empty_response,"warning");else showToast(a.ajax.no_response,"error")})).fail((function(e,a,t){showToast(t,"error")}))},on_remove_image:function(n){var i,s,d;e.ajax({url:a.ajax.url,method:"POST",async:!0,timeout:29e3,data:(i={action:"modal_delete_image",id:n.ajaxResponse.id},s=a.nonce_name,d=a.nonce_value,s in i?Object.defineProperty(i,s,{value:d,enumerable:!0,configurable:!0,writable:!0}):i[s]=d,i)}).done((function(i){if(i)if(i.success){if(n.ajaxResponse.id){var s=e('.js--item[data-id="'+n.ajaxResponse.id+'"]',p);s&&s.remove(),r.val(""),o.addClass("hidden").attr("src","");var d=p.find(".js--item");(!d||d.length<1)&&p.find(".js--info").removeClass("hidden")}showToast(t.text_image_removed,"success")}else i.data?showToast(i.data,"warning"):showToast(a.ajax.empty_response,"warning");else showToast(a.ajax.no_response,"error")})).fail((function(e,a,t){showToast(t,"error")}))}}),d=!0)})).on("hidden.bs.modal",(function(a){var t=e("#dropify_image_field").dropify();(t=t.data("dropify")).resetPreview()}))}))}});