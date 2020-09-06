/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/admin-template/sass/main.scss":
/*!*************************************************!*\
  !*** ./resources/admin-template/sass/main.scss ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/js/admin/_scripts/categories/index.js":
/*!*********************************************************!*\
  !*** ./resources/js/admin/_scripts/categories/index.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var pageLocale = typeof window.CategoriesIndexLocale !== 'undefined' ? window.CategoriesIndexLocale : false;

if (!pageLocale) {
  throw new Error('CategoriesIndexLocale locale not loaded.');
} //<editor-fold desc="selectize-parent-categories">


jQuery(function ($) {
  "use strict";

  $('#field-category_id').selectize({
    create: false
  }); //#! END $( '#post_categories' ).selectize
}); //</editor-fold desc="selectize-parent-categories">
//<editor-fold desc="sortable-categories">

jQuery(function ($) {
  "use strict";

  var locale = window.AppLocale;
  $(".js-sortable").sortable({
    group: 'sortable',
    onDrop: function onDrop($item, container, _super) {
      container.el.removeClass("active");
      var droppedID = $item.attr('data-id'),
          parentID = container.el.parents('li').first().attr('data-id');

      if (typeof parentID === 'undefined') {
        parentID = 0;
      } //#! Save changes


      $.ajax({
        url: locale.ajax.url,
        method: 'POST',
        async: true,
        timeout: 29000,
        data: _defineProperty({
          action: 'update_category_parent',
          category_id: droppedID,
          parent_category_id: parentID
        }, locale.nonce_name, locale.nonce_value)
      }).done(function (r) {
        if (r) {
          if (r.success) {
            if (r.data) {
              showToast(r.data, 'success');
            } else {
              showToast(AppLocale.ajax.empty_response, 'warning');
            }
          } else {
            if (r.data) {
              showToast(r.data, 'warning');
            } else {
              showToast(AppLocale.ajax.empty_response, 'warning');
            }
          }
        } else {
          showToast(AppLocale.ajax.no_response, 'warning');
        }
      }).fail(function (x, s, e) {
        showToast(e, 'error');
      }).always(function () {});

      _super($item, container);
    },
    tolerance: 6,
    distance: 10
  });
}); //</editor-fold desc="sortable-categories">
//<editor-fold desc="category-description-quill">

jQuery(function ($) {
  "use strict";

  var elementsMap = {};
  $('.category-form').each(function (ix, el) {
    var theForm = $(el),
        textareas = $('.js-text-editor', theForm);
    elementsMap[theForm.attr('id')] = []; //#! Enable the editor for each textarea

    textareas.each(function (x, tx) {
      var textarea = $(tx),
          textAreaId = textarea.attr('id');
      elementsMap[theForm.attr('id')].push(textAreaId);
      ContentPressTextEditor.register(textAreaId, new Quill('#' + textAreaId + '-editor', {
        modules: {
          toolbar: [[{
            header: [false]
          }], ['bold', 'italic', 'underline']]
        },
        scrollingContainer: '.quill-scrolling-container',
        placeholder: pageLocale.description_placeholder,
        theme: 'bubble'
      }));
    });
  }).on('submit', function (e) {
    var textareas = elementsMap[$(this).attr('id')];
    textareas.map(function (id, x) {
      $('#' + id).val(ContentPressTextEditor.getHTML(id));
      return id;
    });
  });
}); //</editor-fold desc="category-description-quill">
//<editor-fold desc="category-translations">

jQuery(function ($) {
  "use strict";

  var locale = window.AppLocale; //#! Local cache to save ajax requests for the same plugin

  var currentCategoryID = ''; //#! Open info modal

  $('#infoModal').on('show.bs.modal', function (ev) {
    //#! The element triggering the modal to open
    var sender = $(ev.relatedTarget);
    var categoryID = sender.data('categoryId');
    var $modal = $(this);
    var $modalTitle = $modal.find('.modal-title');
    var $modalBody = $modal.find('.modal-body');
    var $modalContent = $modalBody.find('.js-content');
    var $modalLoader = $modalBody.find('.js-ajax-loader');

    if (typeof categoryID === 'undefined') {
      $modalLoader.addClass('hidden');
      $modalTitle.text(pageLocale.text_error);
      $modalContent.html(pageLocale.text_error_category_id_missing).removeClass('hidden');
    } else {
      if (currentCategoryID === categoryID) {
        $modalLoader.addClass('hidden');
        $modalContent.removeClass('hidden');
      } else {
        $modalLoader.removeClass('hidden');
        $.ajax({
          url: locale.ajax.url,
          method: 'POST',
          async: true,
          timeout: 29000,
          data: _defineProperty({
            action: 'get_category_translations',
            category_id: categoryID
          }, locale.nonce_name, locale.nonce_value)
        }).done(function (r) {
          if (r) {
            if (r.success) {
              if (r.data && r.data.length > 0) {
                currentCategoryID = categoryID;
                $modalTitle.text(pageLocale.text_translations);
                $modalContent.html(r.data).removeClass('hidden');
              } else {
                showToast(locale.ajax.empty_response, 'warning');
              }
            } else {
              if (r.data) {
                showToast(r.data, 'warning');
              } else {
                showToast(locale.ajax.empty_response, 'warning');
              }
            }
          } else {
            showToast(locale.ajax.no_response, 'error');
          }
        }).fail(function (x, s, e) {
          showToast(e, 'error');
        }).always(function () {
          $modalLoader.addClass('hidden');
        });
      }
    }
  });
}); //</editor-fold desc="category-translations">

/***/ }),

/***/ "./resources/sass/_admin/categories/index.scss":
/*!*****************************************************!*\
  !*** ./resources/sass/_admin/categories/index.scss ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/sass/_admin/dashboard/edit.scss":
/*!***************************************************!*\
  !*** ./resources/sass/_admin/dashboard/edit.scss ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/sass/_admin/media/index.scss":
/*!************************************************!*\
  !*** ./resources/sass/_admin/media/index.scss ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/sass/_admin/menus/index.scss":
/*!************************************************!*\
  !*** ./resources/sass/_admin/menus/index.scss ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/sass/_admin/plugins/index.scss":
/*!**************************************************!*\
  !*** ./resources/sass/_admin/plugins/index.scss ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/sass/_admin/themes/index.scss":
/*!*************************************************!*\
  !*** ./resources/sass/_admin/themes/index.scss ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/sass/admin-helpers.scss":
/*!*******************************************!*\
  !*** ./resources/sass/admin-helpers.scss ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** multi ./resources/js/admin/_scripts/categories/index.js ./resources/sass/admin-helpers.scss ./resources/sass/_admin/categories/index.scss ./resources/sass/_admin/dashboard/edit.scss ./resources/sass/_admin/menus/index.scss ./resources/sass/_admin/plugins/index.scss ./resources/sass/_admin/media/index.scss ./resources/sass/_admin/themes/index.scss ./resources/admin-template/sass/main.scss ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! D:\www\contentpress\resources\js\admin\_scripts\categories\index.js */"./resources/js/admin/_scripts/categories/index.js");
__webpack_require__(/*! D:\www\contentpress\resources\sass\admin-helpers.scss */"./resources/sass/admin-helpers.scss");
__webpack_require__(/*! D:\www\contentpress\resources\sass\_admin\categories\index.scss */"./resources/sass/_admin/categories/index.scss");
__webpack_require__(/*! D:\www\contentpress\resources\sass\_admin\dashboard\edit.scss */"./resources/sass/_admin/dashboard/edit.scss");
__webpack_require__(/*! D:\www\contentpress\resources\sass\_admin\menus\index.scss */"./resources/sass/_admin/menus/index.scss");
__webpack_require__(/*! D:\www\contentpress\resources\sass\_admin\plugins\index.scss */"./resources/sass/_admin/plugins/index.scss");
__webpack_require__(/*! D:\www\contentpress\resources\sass\_admin\media\index.scss */"./resources/sass/_admin/media/index.scss");
__webpack_require__(/*! D:\www\contentpress\resources\sass\_admin\themes\index.scss */"./resources/sass/_admin/themes/index.scss");
module.exports = __webpack_require__(/*! D:\www\contentpress\resources\admin-template\sass\main.scss */"./resources/admin-template/sass/main.scss");


/***/ })

/******/ });