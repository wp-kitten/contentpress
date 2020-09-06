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
/******/ 	return __webpack_require__(__webpack_require__.s = 13);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/admin/_scripts/menus/edit.js":
/*!***************************************************!*\
  !*** ./resources/js/admin/_scripts/menus/edit.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var locale = typeof window.AppLocale !== 'undefined' ? window.AppLocale : false;

if (!locale) {
  throw new Error('AppLocale locale not loaded.');
}

var pageLocale = typeof window.MenuLocale !== 'undefined' ? window.MenuLocale : false;

if (!pageLocale) {
  throw new Error('MenuLocale locale not loaded.');
}
/* Sortable menu items */


jQuery(function ($) {
  "use strict"; //#! vars

  var menuList = $('#list-1'),
      btnAddToMenu = $('.js-btn-add-to-menu'),
      btnAddCustomToMenu = $('.js-custom-add-to-menu-button'),
      btnSaveMenu = $('.js-btn-save-menu'),
      emptyMenuItem = $('.menu-empty'),
      __ACTION_ADD__ = 'x0000',
      __ACTION_REMOVE__ = 'x0001';
  /**
   * Stores the last ID used for custom entries
   * @type {number}
   * @internal
   * @private
   */

  var _lastCustomMenuItemID = 1000; //#! Helpers

  var UIHelper = {
    initMainSortable: function initMainSortable() {
      $(".js-menu-list").sortable({
        placeholder: "ui-state-highlight",
        connectWith: '.submenu-list',
        forcePlaceholderSize: true,
        forceHelperSize: true,
        tolerance: "pointer",
        start: function start(event, ui) {
          $('body').addClass('dragging');
        },
        stop: function stop(event, ui) {
          $('body').removeClass('dragging');
          btnSaveMenu.removeClass('no-click disabled');
        }
      }).disableSelection();
    },
    initConnectedSortable: function initConnectedSortable() {
      var submenuLists = $(".submenu-list");
      submenuLists.sortable({
        placeholder: "ui-state-highlight",
        connectWith: '.submenu-list,.js-menu-list',
        forcePlaceholderSize: true,
        forceHelperSize: true,
        tolerance: "pointer",
        start: function start(event, ui) {
          $('body').addClass('dragging');
        },
        stop: function stop(event, ui) {
          $('body').removeClass('dragging');
          btnSaveMenu.removeClass('no-click disabled');
        }
      }).disableSelection();
    },
    updateSaveButtonState: function updateSaveButtonState(action) {
      var enable = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

      if (action === __ACTION_ADD__) {
        emptyMenuItem.hide();
        btnSaveMenu.removeClass('hidden');
      }

      if (!UIHelper.__hasMenuItems()) {
        if (!enable) {
          btnSaveMenu.addClass('hidden');
        }

        emptyMenuItem.show();
      }

      if (enable) {
        btnSaveMenu.removeClass('no-click disabled');
      } else {
        btnSaveMenu.addClass('no-click disabled');
      }
    },
    __hasMenuItems: function __hasMenuItems() {
      var c = menuList.find('.list-item');
      return c && c.length >= 1;
    },
    __removeMenuItem: function __removeMenuItem(ev, $element) {
      ev.preventDefault();
      ev.stopPropagation();

      if (confirm(pageLocale.confirm_delete_item)) {
        //#! Get the target
        var targetID = $element.attr('data-target');

        if (typeof targetID !== 'undefined') {
          menuList.find('.list-item[data-selector="' + targetID + '"]').remove();
          UIHelper.updateSaveButtonState(__ACTION_REMOVE__, true);
        }
      }
    },
    __setupDynamicListeners: function __setupDynamicListeners() {
      $.each($('.js-btn-remove', menuList), function (ix, el) {
        $(el).off('click').on('click', function (ev) {
          UIHelper.__removeMenuItem(ev, $(el));
        });
      });
    },
    __resetChecked: function __resetChecked(target) {
      var items = target.find('.js-check-input:checked');

      if (items && items.length >= 1) {
        $.each(items, function (ix, el) {
          var self = $(el);

          if (self.is(':checked')) {
            self.prop('checked', false);
          }
        });
      }
    },
    __saveMenu: function __saveMenu(ev) {
      var menuItems = UIHelper.__menuToArray();

      var loader = $('#menu-items-sortable .js-ajax-loader');
      btnSaveMenu.addClass('no-click');
      loader.removeClass('hidden');
      var ajaxData = {
        url: locale.ajax.url,
        method: 'POST',
        async: true,
        timeout: 29000,
        data: _defineProperty({
          action: 'menu_save',
          menu_id: pageLocale.menu_id,
          menu_items: menuItems
        }, locale.nonce_name, locale.nonce_value)
      };
      $.ajax(ajaxData).done(function (r) {
        if (r) {
          if (r.success) {
            if (r.data) {
              showToast(r.data, 'success');
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
          showToast(locale.ajax.no_response, 'warning');
        }
      }).fail(function (x, s, e) {
        showToast(e, 'error');
      }).always(function () {
        //! Hide the save button if there are no menu items
        UIHelper.updateSaveButtonState(__ACTION_REMOVE__, false);
        loader.addClass('hidden');
      });
    },
    //#! Retrieve the menu data as a list to send to server
    __menuToArray: function __menuToArray() {
      var parentIds = menuList.sortable('toArray', {
        //#! the attribute to look for
        attribute: 'data-selector',
        //#! The key to extract
        key: 'data-id',
        //#! The RegExp expression allowing to determine how to split the data in key-value. In this case it's just the value
        expression: /(.+)/
      });
      var mainMenuItems = $('>.list-item', $('.js-menu-list'));
      var itemData = {};
      $.each(mainMenuItems, function (i, el) {
        var $item = $(el);
        var dataSelector = $item.attr('data-selector');
        var type = $item.attr('data-type');
        itemData[i] = {
          id: $item.attr('data-id'),
          type: type,
          selector: dataSelector,
          menuItemId: $item.attr('data-menu-item-id'),
          children: []
        };

        if ('custom' === type) {
          itemData[i]['title'] = $item.attr('data-title');
          itemData[i]['url'] = $item.attr('data-url');
        }

        itemData[i]['children'] = UIHelper.__getChildren($item);
      });
      return itemData;
    },
    //#! Recursively build submenu items
    __getChildren: function __getChildren($menuItem) {
      var _result = [];
      var children = $('> .submenu-list > li', $menuItem);

      if (children.length > 0) {
        $.each(children, function (i, el) {
          var child = $(el);
          var dataSelector = child.attr('data-selector');
          var type = child.attr('data-type');
          _result[i] = {
            id: child.attr('data-id'),
            type: type,
            selector: dataSelector,
            menuItemId: child.attr('data-menu-item-id'),
            children: UIHelper.__getChildren(child)
          };

          if ('custom' === type) {
            _result[i]['title'] = child.attr('data-title');
            _result[i]['url'] = child.attr('data-url');
          }
        });
      }

      return _result;
    }
  }; //[[ OnLoad ===================================================

  UIHelper.initMainSortable();
  UIHelper.initConnectedSortable();
  UIHelper.updateSaveButtonState(__ACTION_ADD__, false);

  UIHelper.__setupDynamicListeners(); //! Event listeners


  btnSaveMenu.on('click', UIHelper.__saveMenu); //#! Add entries to menu (post types & categories), except custom menu items

  btnAddToMenu.on('click', function (ev) {
    var self = $(this),
        target = $('.collapse.show ' + self.attr('data-target'));

    if (typeof target !== 'undefined') {
      var selectedEntries = target.find('.js-check-input:checked');
      $.each(selectedEntries, function (ix, el) {
        var entry = $(el),
            dataID = entry.val(),
            dataType = entry.attr('data-type'),
            dataMenuItemID = entry.attr('data-menu-item-id'),
            dataTitle = entry.attr('data-title'),
            dataSelector = dataType + dataID;
        menuList.append('<li data-selector="' + dataSelector + '" data-id="' + dataID + '" data-menu-item-id="' + dataMenuItemID + '" data-type="' + dataType + '" class="list-item"><p>' + dataTitle + '<a href="#" class="js-btn-remove" data-target="' + dataSelector + '" title="' + pageLocale.delete_text_title + '">' + pageLocale.delete_text + '</a></p><ul class="list-unstyled submenu-list"></ul></li>');
      });

      if (selectedEntries.length >= 1) {
        UIHelper.updateSaveButtonState(__ACTION_ADD__);

        UIHelper.__setupDynamicListeners();

        UIHelper.__resetChecked(target);

        UIHelper.initConnectedSortable();
      }
    }
  }); //#! Add custom entries to menu

  btnAddCustomToMenu.on('click', function (ev) {
    var title = $('#menu-item-title').val(),
        url = $('#menu-item-url').val(),
        type = $('#menu-item-data-type').val(),
        id = _lastCustomMenuItemID + 1,
        dataSelector = type + id;

    if (!title || title.length < 1) {
      return false;
    }

    if (!url || url.length < 1) {
      return false;
    }

    menuList.append('<li data-selector="' + dataSelector + '" data-id="' + id + '" data-menu-item-id="0" data-type="' + type + '" data-title="' + title + '" data-url="' + url + '" class="list-item"><p>' + title + '<a href="#" class="js-btn-remove" data-target="' + dataSelector + '" title="' + pageLocale.delete_text_title + '">' + pageLocale.delete_text + '</a></p><ul class="list-unstyled submenu-list"></ul></li>');
    UIHelper.updateSaveButtonState(__ACTION_ADD__);

    UIHelper.__setupDynamicListeners();
  });
});
/* Menu name */

jQuery(function ($) {
  "use strict";

  var loader = $('#form-menu-name .js-ajax-loader');
  $('.js-save-menu-title-button').on('click', function (ev) {
    ev.preventDefault();
    var self = $(this);
    var form = $('#form-menu-name');
    var nameField = $('.name-field', form);

    if (!nameField || !nameField.val().length > 0) {
      return false;
    }

    self.addClass('no-click');
    loader.removeClass('hidden');
    var ajaxConfig = {
      url: locale.ajax.url,
      method: 'POST',
      cache: false,
      timeout: 29000,
      data: _defineProperty({
        action: 'save_menu_name',
        menu_name: nameField.val(),
        menu_id: pageLocale.menu_id
      }, locale.nonce_name, locale.nonce_value)
    };
    $.ajax(ajaxConfig).done(function (r) {
      if (r) {
        if (r.success) {
          showToast(r.data, 'success');
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
      self.removeClass('no-click');
      loader.addClass('hidden');
    });
  });
});
/* Menu options */

jQuery(function ($) {
  "use strict";

  var loader = $('.form-menu-options .js-ajax-loader');
  $('.js-menu-save-options-button').on('click', function (ev) {
    ev.preventDefault();
    var self = $(this);
    var radio = $('input[name="display_as"]:checked');

    if (!radio || !radio.val().length > 0) {
      return false;
    }

    self.addClass('no-click');
    loader.removeClass('hidden');
    var ajaxConfig = {
      url: locale.ajax.url,
      method: 'POST',
      cache: false,
      timeout: 29000,
      data: _defineProperty({
        action: 'save_menu_options',
        display_as: radio.val(),
        menu_id: pageLocale.menu_id
      }, locale.nonce_name, locale.nonce_value)
    };
    $.ajax(ajaxConfig).done(function (r) {
      if (r) {
        if (r.success) {
          showToast(pageLocale.text_options_saved, 'success');
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
      self.removeClass('no-click');
      loader.addClass('hidden');
    });
  });
});
/* Accordion */

jQuery(function ($) {
  "use strict";

  var accordion = $('.cp-menu-edit-accordion');
  $('.js-trigger').on('click', function (ev) {
    ev.preventDefault();
    var self = $(this);
    var icon = self.next('.js-sign');

    if (self.hasClass('collapsed')) {
      icon.removeClass('fa-plus').addClass('fa-minus');
    } else {
      icon.removeClass('fa-minus').addClass('fa-plus');
    }
  });
});

/***/ }),

/***/ 13:
/*!*********************************************************!*\
  !*** multi ./resources/js/admin/_scripts/menus/edit.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\www\contentpress\resources\js\admin\_scripts\menus\edit.js */"./resources/js/admin/_scripts/menus/edit.js");


/***/ })

/******/ });