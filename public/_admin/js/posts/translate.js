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
/******/ 	return __webpack_require__(__webpack_require__.s = 8);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/admin/_scripts/posts/translate.js":
/*!********************************************************!*\
  !*** ./resources/js/admin/_scripts/posts/translate.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var pageLocale = typeof window.PostsLocale !== 'undefined' ? window.PostsLocale : false;

if (!pageLocale) {
  throw new Error('PostsLocale locale not loaded.');
}
/*#!
 * Global object
 * Themes and plugins MUST override the getContent method in order to inject their own content
 */


window.AppTextEditor = {
  getContent: function getContent(contentBuilder) {
    var editor = $('#plugin_text_editor');
    return editor ? editor.val() : '';
  }
}; //<editor-fold desc="post-actions">

jQuery(function ($) {
  "use strict";

  var locale = window.AppLocale;
  ContentPressTextEditor.register('post_excerpt', new Quill('#post_excerpt-editor', {
    modules: {
      toolbar: [[{
        header: [false]
      }], ['bold', 'italic', 'underline']]
    },
    scrollingContainer: '.quill-scrolling-container',
    placeholder: pageLocale.text_description,
    theme: 'bubble'
  }));
  $('.js-save-post-button').on('click', function (e) {
    e.preventDefault();
    var self = $(this);
    self.addClass('no-click');
    $.ajax({
      url: locale.ajax.url,
      method: 'POST',
      async: true,
      timeout: 29000,
      data: _defineProperty({
        action: 'save_post_translation',
        post_status: $('#post_status').val(),
        post_title: $('#post_title').val(),
        // keeps the post data size small
        post_content: AppTextEditor.getContent(null),
        post_excerpt: ContentPressTextEditor.getHTML('post_excerpt'),
        post_categories: $('#post_categories').val(),
        post_tags: $('#post_tags').val(),
        language_id: pageLocale.language_id,
        parent_post_id: pageLocale.parent_post_id,
        current_post_id: pageLocale.current_post_id,
        sticky_featured: $('#sticky_featured').val(),
        comments_enabled: $('#comments_enabled').val(),
        __post_image_id: $('#__post_image_id').val()
      }, locale.nonce_name, locale.nonce_value)
    }).done(function (r) {
      if (r) {
        if (r.success) {
          if (r.data) {
            showToast(r.data.message, 'success'); //#! Update the preview url

            $('.view-post-button').attr('href', r.data.preview_url);
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
    }).always(function () {
      self.removeClass('no-click');
    });
    return false;
  });
}); //</editor-fold desc="post-actions">
//<editor-fold desc="selectize-categories-tags">

jQuery(function ($) {
  "use strict";

  $('#post_categories, #post_tags').selectize({
    create: false
  }); //#! END $( '#post_categories, #post_tags' ).selectize
}); //</editor-fold desc="selectize-categories-tags">

/***/ }),

/***/ 8:
/*!**************************************************************!*\
  !*** multi ./resources/js/admin/_scripts/posts/translate.js ***!
  \**************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\www\contentpress\resources\js\admin\_scripts\posts\translate.js */"./resources/js/admin/_scripts/posts/translate.js");


/***/ })

/******/ });