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
/******/ 	return __webpack_require__(__webpack_require__.s = 16);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/admin/_scripts/plugins/add.js":
/*!****************************************************!*\
  !*** ./resources/js/admin/_scripts/plugins/add.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var appLocale = typeof window.AppLocale !== 'undefined' ? window.AppLocale : false;

if (!appLocale) {
  throw new Error('PluginsLocale locale not loaded.');
}

var pageLocale = typeof window.PluginsLocale !== 'undefined' ? window.PluginsLocale : false;

if (!pageLocale) {
  throw new Error('PluginsLocale locale not loaded.');
}

jQuery(function ($) {
  "use strict"; //#! Dropify -- upload plugin

  var __dropifySetup = false;
  var dropifyImageUploader = new DropifyImageUploader(
  /* the selector for the file upload field */
  $('#plugin_upload_field'));

  if (!__dropifySetup) {
    dropifyImageUploader.setup({
      on_add_image: function on_add_image($this) {
        var value = $this.element[0].files[0];

        if (value.length < 1) {
          return false;
        }

        var ajaxData = new FormData();
        ajaxData.append('action', 'upload_plugin');
        ajaxData.append('the_file', value);
        ajaxData.append(appLocale.nonce_name, appLocale.nonce_value);
        $.ajax({
          url: appLocale.ajax.url,
          method: 'POST',
          async: true,
          timeout: 29000,
          data: ajaxData,
          processData: false,
          contentType: false
        }).done(function (r) {
          if (r) {
            if (r.success) {
              if (r.data) {
                $this.ajaxResponse = r.data;
                showToast(pageLocale.text_plugin_uploaded, 'success');
              } else {
                showToast(appLocale.ajax.empty_response, 'warning');
              }
            } else {
              if (r.data) {
                showToast(r.data, 'warning');
              } else {
                showToast(appLocale.ajax.empty_response, 'warning');
              }
            }
          } else {
            showToast(appLocale.ajax.no_response, 'error');
          }
        }).fail(function (x, s, e) {
          showToast(e, 'error');
        });
      },
      on_remove_image: function on_remove_image($this) {
        $.ajax({
          url: appLocale.ajax.url,
          method: 'POST',
          async: true,
          timeout: 29000,
          data: _defineProperty({
            action: 'delete_plugin',
            path: $this.ajaxResponse.path
          }, appLocale.nonce_name, appLocale.nonce_value)
        }).done(function (r) {
          if (r) {
            if (r.success) {
              showToast(pageLocale.text_plugin_deleted, 'success');
            } else {
              if (r.data) {
                showToast(r.data, 'warning');
              } else {
                showToast(appLocale.ajax.empty_response, 'warning');
              }
            }
          } else {
            showToast(appLocale.ajax.no_response, 'error');
          }
        }).fail(function (x, s, e) {
          showToast(e, 'error');
        });
      }
    });
    __dropifySetup = true;
  } //#! Search plugins


  var searchField = $('#plugin-search-field');
  $('#plugin-search-button').on('click', function () {});
});

/***/ }),

/***/ 16:
/*!**********************************************************!*\
  !*** multi ./resources/js/admin/_scripts/plugins/add.js ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\www\contentpress\resources\js\admin\_scripts\plugins\add.js */"./resources/js/admin/_scripts/plugins/add.js");


/***/ })

/******/ });