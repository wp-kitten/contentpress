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
/******/ 	return __webpack_require__(__webpack_require__.s = 9);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/admin/_scripts/settings/index.js":
/*!*******************************************************!*\
  !*** ./resources/js/admin/_scripts/settings/index.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var pageLocale = typeof window.SettingsPageLocale !== 'undefined' ? window.SettingsPageLocale : false;

if (!pageLocale) {
  throw new Error('SettingsPageLocale locale not loaded.');
} //<editor-fold desc="action-links">


jQuery(function ($) {
  "use strict"; //#! Edit links

  var editLinks = $('.js-button-edit');

  if (typeof editLinks !== 'undefined') {
    editLinks.on('click', function (e) {
      e.preventDefault();
      var link = $(this),
          id = link.attr('data-id'),
          row = $('#row-edit-' + id);

      if (typeof row !== 'undefined') {
        if (row.hasClass('hidden')) {
          row.removeClass('hidden');
        } else {
          row.addClass('hidden');
        }
      }
    });
  } //#! Update links


  var updateLinks = $('.js-button-form-update');

  if (typeof updateLinks !== 'undefined') {
    updateLinks.on('click', function (e) {
      e.preventDefault();
      var self = $(this),
          id = self.attr('data-id'),
          editRow = $('#row-edit-' + id),
          mainRow = $('#row-' + id);

      if (typeof editRow !== 'undefined') {
        var fieldName = $('.field-name', editRow).val(),
            fieldDisplayName = $('.field-display-name', editRow).val(),
            fieldPluralName = $('.field-plural-name', editRow).val(),
            __nameCell = $('.post-type-name-cell', mainRow),
            __displayNameCell = $('.post-type-display-name-cell', mainRow),
            __pluralNameCell = $('.post-type-plural-name-cell', mainRow),
            //#! checkboxes
        allowCategoriesChk = $('.allow_categories', editRow),
            allowCommentsChk = $('.allow_comments', editRow),
            allowTagsChk = $('.allow_tags', editRow),
            ajaxData = _defineProperty({
          action: 'update_post_type',
          id: id,
          name: fieldName,
          display_name: fieldDisplayName,
          plural_name: fieldPluralName
        }, window.AppLocale.nonce_name, window.AppLocale.nonce_value);

        if (allowCategoriesChk.is(':checked')) {
          ajaxData['allow_categories'] = true;
        }

        if (allowCommentsChk.is(':checked')) {
          ajaxData['allow_comments'] = true;
        }

        if (allowTagsChk.is(':checked')) {
          ajaxData['allow_tags'] = true;
        } //#! update


        if (fieldName.length > 0 && fieldDisplayName.length > 0) {
          self.addClass('no-click');
          $.ajax({
            url: window.AppLocale.ajax.url,
            method: 'POST',
            timeout: 29000,
            async: true,
            cache: false,
            data: ajaxData
          }).done(function (r) {
            if (r) {
              if (r.success) {
                // update + hide
                __nameCell.text(fieldName);

                __displayNameCell.text(fieldDisplayName);

                __pluralNameCell.text(fieldPluralName);

                editRow.addClass('hidden');
              } else {
                console.warn('response', r);
              }
            } else {
              console.error('no response');
            }
          }).fail(function (x, s, e) {
            console.error(e);
          }).always(function () {
            self.removeClass('no-click');
          });
        }
      }
    });
  }
}); //</editor-fold desc="action-links">

/***/ }),

/***/ 9:
/*!*************************************************************!*\
  !*** multi ./resources/js/admin/_scripts/settings/index.js ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\www\contentpress\resources\js\admin\_scripts\settings\index.js */"./resources/js/admin/_scripts/settings/index.js");


/***/ })

/******/ });