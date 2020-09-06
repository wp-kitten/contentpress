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
/******/ 	return __webpack_require__(__webpack_require__.s = 15);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/admin/_scripts/plugins/index.js":
/*!******************************************************!*\
  !*** ./resources/js/admin/_scripts/plugins/index.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var pageLocale = typeof window.PluginsLocale !== 'undefined' ? window.PluginsLocale : false;

if (!pageLocale) {
  throw new Error('PluginsLocale locale not loaded.');
}

jQuery(function ($) {
  "use strict";

  var locale = window.AppLocale; //#! Local cache to save ajax requests for the same plugin

  var currentPluginName = ''; //#! Open info modal

  $('#infoModal').on('show.bs.modal', function (ev) {
    //#! The element triggering the modal to open
    var sender = $(ev.relatedTarget);
    var pluginDirName = sender.data('name');
    var pluginDisplayName = sender.data('displayName');
    var $modal = $(this);
    var $modalTitle = $modal.find('.modal-title');
    var $modalBody = $modal.find('.modal-body');
    var $modalContent = $modalBody.find('.js-content');
    var $modalLoader = $modalBody.find('.js-ajax-loader');

    if (typeof pluginDirName === 'undefined') {
      $modalLoader.addClass('hidden');
      $modalTitle.text(pageLocale.text_error);
      $modalContent.html(pageLocale.text_error_plugin_name_not_found).removeClass('hidden');
    } else {
      if (currentPluginName === pluginDirName) {
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
            action: 'get_plugin_info',
            plugin_name: pluginDirName
          }, locale.nonce_name, locale.nonce_value)
        }).done(function (r) {
          if (r) {
            if (r.success) {
              if (r.data && r.data.length > 0) {
                currentPluginName = pluginDirName;
                $modalTitle.text(pluginDisplayName);
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
  }); //#! Deactivate plugins

  $('#js-btn-deactivate').on('click', function (ev) {
    var self = $(this);
    var theFormID = self.attr('data-form-id');
    var action = self.attr('data-action');

    if (typeof theFormID !== 'undefined' && typeof action !== 'undefined') {
      var theForm = $('#' + theFormID);
      theForm.attr('action', action).trigger('submit');
    }

    return false;
  });
});

/***/ }),

/***/ 15:
/*!************************************************************!*\
  !*** multi ./resources/js/admin/_scripts/plugins/index.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\www\contentpress\resources\js\admin\_scripts\plugins\index.js */"./resources/js/admin/_scripts/plugins/index.js");


/***/ })

/******/ });