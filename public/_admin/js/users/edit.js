!function(e){var o={};function n(r){if(o[r])return o[r].exports;var t=o[r]={i:r,l:!1,exports:{}};return e[r].call(t.exports,t,t.exports,n),t.l=!0,t.exports}n.m=e,n.c=o,n.d=function(e,o,r){n.o(e,o)||Object.defineProperty(e,o,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,o){if(1&o&&(e=n(e)),8&o)return e;if(4&o&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&o&&"string"!=typeof e)for(var t in e)n.d(r,t,function(o){return e[o]}.bind(null,t));return r},n.n=function(e){var o=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(o,"a",o),o},n.o=function(e,o){return Object.prototype.hasOwnProperty.call(e,o)},n.p="/",n(n.s=24)}({24:function(e,o,n){e.exports=n(25)},25:function(e,o){var n=void 0!==window.UsersPageLocale&&window.UsersPageLocale;if(!n)throw new Error("UsersPageLocale locale not loaded.");jQuery((function(e){"use strict";var o=window.AppLocale,r=!1,t=new DropifyImageUploader(e("#user_image_field"));r||(t.setup({on_add_image:function(r){var t=r.element[0].files[0];if(t.length<1)return!1;var a=new FormData;a.append("action","set_user_image"),a.append("user_id",n.user_id),a.append("user_image",t),a.append(o.nonce_name,o.nonce_value),e.ajax({url:o.ajax.url,method:"POST",async:!0,timeout:29e3,data:a,processData:!1,contentType:!1}).done((function(e){e?e.success?showToast(n.text_image_set,"success"):showToast(o.ajax.empty_response,"warning"):showToast(o.ajax.no_response,"error")})).fail((function(e,o,n){showToast(n,"error")}))},on_remove_image:function(){var r,t,a;e.ajax({url:o.ajax.url,method:"POST",async:!0,timeout:29e3,data:(r={action:"delete_user_image",user_id:n.user_id},t=o.nonce_name,a=o.nonce_value,t in r?Object.defineProperty(r,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):r[t]=a,r)}).done((function(e){e?e.success?showToast(n.text_image_removed,"success"):showToast(o.ajax.empty_response,"warning"):showToast(o.ajax.no_response,"error")})).fail((function(e,o,n){showToast(n,"error")}))}}),r=!0)})),jQuery((function(e){"use strict";ValPressTextEditor.register("author_bio",new Quill("#field-bio-editor",{modules:{toolbar:[[{header:[!1]}],["bold","italic","underline"]]},scrollingContainer:".quill-scrolling-container",placeholder:n.text_info_bio,theme:"bubble"})),e("#js-acc-mgmt-update-btn").on("click",(function(o){e("#field-bio").val(ValPressTextEditor.getHTML("author_bio"))}))}))}});