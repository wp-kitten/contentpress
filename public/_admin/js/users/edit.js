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
/******/ 	return __webpack_require__(__webpack_require__.s = 12);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/admin/_scripts/users/edit.js":
/*!***************************************************!*\
  !*** ./resources/js/admin/_scripts/users/edit.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var pageLocale = typeof window.UsersPageLocale !== 'undefined' ? window.UsersPageLocale : false;

if (!pageLocale) {
  throw new Error('UsersPageLocale locale not loaded.');
} //<editor-fold desc="user-profile-image">


jQuery(function ($) {
  "use strict";

  var locale = window.AppLocale;
  var __dropifySetup = false;
  var dropifyImageUploader = new DropifyImageUploader(
  /* the selector for the file upload field */
  $('#user_image_field'));

  if (!__dropifySetup) {
    dropifyImageUploader.setup({
      on_add_image: function on_add_image($this) {
        var value = $this.element[0].files[0];

        if (value.length < 1) {
          return false;
        }

        var ajaxData = new FormData();
        ajaxData.append('action', 'set_user_image');
        ajaxData.append('user_id', pageLocale.user_id);
        ajaxData.append('user_image', value);
        ajaxData.append(locale.nonce_name, locale.nonce_value);
        $.ajax({
          url: locale.ajax.url,
          method: 'POST',
          async: true,
          timeout: 29000,
          data: ajaxData,
          processData: false,
          contentType: false
        }).done(function (r) {
          if (r) {
            if (r.success) {
              showToast(pageLocale.text_image_set, 'success');
            } else {
              showToast(locale.ajax.empty_response, 'warning');
            }
          } else {
            showToast(locale.ajax.no_response, 'error');
          }
        }).fail(function (x, s, e) {
          showToast(e, 'error');
        });
      },
      on_remove_image: function on_remove_image() {
        $.ajax({
          url: locale.ajax.url,
          method: 'POST',
          async: true,
          timeout: 29000,
          data: _defineProperty({
            action: 'delete_user_image',
            user_id: pageLocale.user_id
          }, locale.nonce_name, locale.nonce_value)
        }).done(function (r) {
          if (r) {
            if (r.success) {
              showToast(pageLocale.text_image_removed, 'success');
            } else {
              showToast(locale.ajax.empty_response, 'warning');
            }
          } else {
            showToast(locale.ajax.no_response, 'error');
          }
        }).fail(function (x, s, e) {
          showToast(e, 'error');
        });
      }
    });
    __dropifySetup = true;
  }
}); //<editor-fold desc="user-profile-image">
//<editor-fold desc="wysiwyg-editors">

jQuery(function ($) {
  "use strict";

  ContentPressTextEditor.register('author_bio', new Quill('#field-bio-editor', {
    modules: {
      toolbar: [[{
        header: [false]
      }], ['bold', 'italic', 'underline']]
    },
    scrollingContainer: '.quill-scrolling-container',
    placeholder: pageLocale.text_info_bio,
    theme: 'bubble'
  })); //#! On save button click, get the values from editors

  $('#js-acc-mgmt-update-btn').on('click', function (ev) {
    $('#field-bio').val(ContentPressTextEditor.getHTML('author_bio'));
  });
}); //<editor-fold desc="wysiwyg-editors">

/***/ }),

/***/ 12:
/*!*********************************************************!*\
  !*** multi ./resources/js/admin/_scripts/users/edit.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\www\contentpress\resources\js\admin\_scripts\users\edit.js */"./resources/js/admin/_scripts/users/edit.js");


/***/ })

/******/ });