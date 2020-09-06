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
/******/ 	return __webpack_require__(__webpack_require__.s = 23);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/admin/_scripts/admin.js":
/*!**********************************************!*\
  !*** ./resources/js/admin/_scripts/admin.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/*#!
 * Global object
 * Themes and plugins MUST override the getContent method in order to inject their own content
 */
window.AppTextEditor = {
  getContent: function getContent(contentBuilder) {
    return contentBuilder ? contentBuilder.html() : '';
  }
}; //#! Admin sidebar toggle

jQuery(function ($) {
  "use strict";

  var LOCAL_STORAGE_KEY = 'contentpress_admin'; //#! onLoad

  var LocalStorage = {
    init: function init() {
      this.ls = window.localStorage;
      this.data = this.load();
      return this;
    },
    load: function load() {
      var data = this.ls.getItem(LOCAL_STORAGE_KEY);

      if (data) {
        return JSON.parse(data);
      }

      return {};
    },
    get: function get(key) {
      return typeof this.data[key] !== 'undefined' ? this.data[key] : null;
    },
    update: function update(key, value) {
      this.data[key] = value;
      this.ls.setItem(LOCAL_STORAGE_KEY, JSON.stringify(this.data));
      return this;
    }
  };
  LocalStorage.init();
  var isHidden = false;
  var hidden = LocalStorage.get('hide_admin_sidebar');

  if (hidden) {
    $('body').addClass('sidenav-toggled');
    isHidden = true;
  } else {
    $('body').removeClass('sidenav-toggled');
  } //#! On toggle


  $('.app-sidebar__toggle').on('click', function (ev) {
    if (isHidden) {
      $('body').removeClass('sidenav-toggled');
      isHidden = false;
    } else {
      $('body').addClass('sidenav-toggled');
      isHidden = true;
    }

    LocalStorage.update('hide_admin_sidebar', isHidden);
    return true;
  });
}); //#! Delete links

jQuery(function ($) {
  "use strict";
  /**
   * Display the confirmation dialog and submits the form on success
   */

  $('[data-confirm]').on('click', function (ev) {
    ev.stopPropagation();
    var element = $(this);
    var formID = element.attr('data-form-id');
    var confirmText = element.attr('data-confirm');

    if (confirmText.length > 0) {
      if (confirm(confirmText)) {
        var form = $('#' + formID);

        if (form && form.length > 0) {
          form.trigger('submit');
        }

        return true;
      }
    }

    ev.preventDefault();
    return false;
  });
});

/***/ }),

/***/ 23:
/*!****************************************************!*\
  !*** multi ./resources/js/admin/_scripts/admin.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\www\contentpress\resources\js\admin\_scripts\admin.js */"./resources/js/admin/_scripts/admin.js");


/***/ })

/******/ });