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
/******/ 	return __webpack_require__(__webpack_require__.s = 5);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/admin/_scripts/posts/index.js":
/*!****************************************************!*\
  !*** ./resources/js/admin/_scripts/posts/index.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var locale = typeof window.AppLocale !== 'undefined' ? window.AppLocale : false;

if (!locale) {
  throw new Error('AppLocale locale not loaded.');
}

var pageLocale = typeof window.PostsLocale !== 'undefined' ? window.PostsLocale : false;

if (!pageLocale) {
  throw new Error('PostsLocale locale not loaded.');
} //<editor-fold desc="post-details">


if (pageLocale.isMultilanguage) {
  jQuery(function ($) {
    "use strict";

    var expandLinks = $('.js-expand-post');

    if (typeof expandLinks !== 'undefined') {
      expandLinks.on('click', function (e) {
        e.preventDefault();
        var link = $(this),
            rowID = link.attr('data-target'),
            row = $('#' + rowID);

        if (typeof row !== 'undefined') {
          if (row.hasClass('hidden')) {
            row.removeClass('hidden');
            link.text(pageLocale.text_collapse);
          } else {
            row.addClass('hidden');
            link.text(pageLocale.text_expand);
          }
        }
      });
    }
  });
} //</editor-fold desc="post-details">
//<editor-fold desc=":: POST TITLES -- INLINE EDIT ::">


jQuery(function ($) {
  /**
   * Helper object that allows post titles to be edited inline
   * @type {{edit(*): void, enable(*=): void, save(*): void, revert(*): void}}
   */
  var InlineEditor = {
    enable: function enable(elements) {
      var self = this;

      if (elements && elements.length >= 1) {
        $.each(elements, function (i, el) {
          var element = $(el);
          element.on('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            self.edit(element);
          }).on('blur', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            self.save(element);
          }) //#! Prevents the ENTER key to produce any change
          .on('keydown', function (ev) {
            if (ev.keyCode === 13) {
              ev.preventDefault();
              return false;
            }
          });
        });
      }
    },
    edit: function edit($element) {
      $element.addClass('is-editing');
      $element.attr('data-initial-value', $element.html().trim());
    },
    save: function save($element) {
      if (!this.hasChanged($element)) {
        $element.removeClass('is-editing');
        return false;
      }

      var self = this;
      $.ajax({
        url: locale.ajax.url,
        method: 'POST',
        async: true,
        timeout: 29000,
        cache: false,
        data: _defineProperty({
          action: 'update_post_title',
          post_id: $element.attr('data-id'),
          post_title: $element.html().trim(),
          post_type: $element.attr('data-post-type')
        }, locale.nonce_name, locale.nonce_value)
      }).done(function (r) {
        if (r) {
          if (r.success) {
            if (r.data) {
              showToast(r.data.message, 'success');
              $element.removeClass('is-editing');
            } else {
              showToast(AppLocale.ajax.empty_response, 'warning');
              self.revert($element);
            }
          } else {
            if (r.data) {
              showToast(r.data, 'warning');
              self.revert($element);
            } else {
              showToast(AppLocale.ajax.empty_response, 'warning');
              self.revert($element);
            }
          }
        } else {
          showToast(AppLocale.ajax.no_response, 'warning');
          self.revert($element);
        }
      }).fail(function (x, s, e) {
        showToast(e, 'error');
        self.revert($element);
      }).always(function () {});
    },
    revert: function revert($element) {
      $element.html($element.attr('data-initial-value'));
      $element.removeClass('is-editing');
    },
    hasChanged: function hasChanged($element) {
      var initialContent = $element.attr('data-initial-value');

      if (typeof initialContent === 'undefined') {
        return true;
      }

      return initialContent !== $element.html().trim();
    }
  };
  InlineEditor.enable($('.posts-list .post-title.js-editable'));
}); //</editor-fold desc=":: POST TITLES -- INLINE EDIT ::">
//<editor-fold desc="post-actions-hover">

jQuery(function ($) {
  "use strict";

  $('.js-post-title-cell').mouseenter(function () {
    $('.post-actions', $(this)).removeClass('hidden');
  }).mouseleave(function () {
    $('.post-actions', $(this)).addClass('hidden');
  });
}); //</editor-fold desc="post-actions-hover">
//<editor-fold desc="clear-filters">

jQuery('.js-btn-form-filters-clear').on('click', function (e) {
  var url = $(this).attr('data-url');

  if (typeof url !== 'undefined') {
    window.location.href = url;
  }

  return false;
}); //</editor-fold desc="clear-filters">

/***/ }),

/***/ 5:
/*!**********************************************************!*\
  !*** multi ./resources/js/admin/_scripts/posts/index.js ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\www\contentpress\resources\js\admin\_scripts\posts\index.js */"./resources/js/admin/_scripts/posts/index.js");


/***/ })

/******/ });