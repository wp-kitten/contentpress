(()=>{var e=void 0!==window.ThemesLocale&&window.ThemesLocale;if(!e)throw new Error("ThemesLocale locale not loaded.");jQuery((function(a){"use strict";var o=window.AppLocale;a(".js-theme-delete").on("click",(function(a){return confirm(e.text_confirm_delete)}));var n="";a("#infoModal").on("show.bs.modal",(function(t){var d,s,r,i=a(t.relatedTarget),l=i.data("name"),m=i.data("displayName"),h=a(this),c=h.find(".modal-title"),f=h.find(".modal-body"),w=f.find(".js-content"),u=f.find(".js-ajax-loader");void 0===l?(u.addClass("hidden"),c.text(e.text_error),w.html(e.text_error_theme_name_not_found).removeClass("hidden")):n===l?(u.addClass("hidden"),w.removeClass("hidden")):(u.removeClass("hidden"),a.ajax({url:o.ajax.url,method:"POST",async:!0,timeout:29e3,data:(d={action:"get_theme_info",theme_name:l},s=o.nonce_name,r=o.nonce_value,s in d?Object.defineProperty(d,s,{value:r,enumerable:!0,configurable:!0,writable:!0}):d[s]=r,d)}).done((function(e){e?e.success?e.data&&e.data.length>0?(n=l,c.text(m),w.html(e.data).removeClass("hidden")):showToast(o.ajax.empty_response,"warning"):e.data?showToast(e.data,"warning"):showToast(o.ajax.empty_response,"warning"):showToast(o.ajax.no_response,"error")})).fail((function(e,a,o){showToast(o,"error")})).always((function(){u.addClass("hidden")})))}))}))})();