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
/******/ 	return __webpack_require__(__webpack_require__.s = 20);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/admin/_scripts/media/modal.js":
/*!****************************************************!*\
  !*** ./resources/js/admin/_scripts/media/modal.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var pageLocale = typeof window.MediaLocale !== 'undefined' ? window.MediaLocale : false;

if (!pageLocale) {
  throw new Error('MediaLocale locale not loaded.');
}

jQuery(function ($) {
  "use strict";

  var locale = window.AppLocale;
  var $mediaModal = $('#mediaModal'); //#! Referenced fields to update

  var $imagePreview = null;
  var $imageInput = null;
  var __set = false; //#! onClick listener for media files inside the modal

  var __setupImageOnClickListener = function __setupImageOnClickListener($context, sender) {
    //#! Update references
    $imagePreview = $(sender.data('imageTarget'));
    $imageInput = $(sender.data('inputTarget'));
    $('.js-contentpress-thumbnail', $context).on('click', function (ev) {
      ev.preventDefault();
      ev.stopPropagation();
      var jsItem = $(this);
      var jsImage = $('img', jsItem); //#! Update preview & hidden field

      $imageInput.val(jsItem.data('id'));
      $imagePreview.attr('src', jsImage.attr('src')).removeClass('hidden');
    });
  }; //#! OnClick listener to delete the attached image


  $('.js-preview-image-delete').on('click', function (ev) {
    ev.preventDefault();
    ev.stopPropagation();
    var jsItemParent = $(this).parents('.js-image-preview');
    $('input[type="hidden"]', jsItemParent).val('');
    $('img', jsItemParent).addClass('hidden').attr('src', '');
  }); //<editor-fold desc=":: IMAGE UPLOADER ::">
  //#! Render media file -- this content is populated after the file upload ajax request and displayed in the media files tab

  var imageTemplate = '<div class="item js--item" data-id="__FILE_ID__">\n' + '<a href="#" class="js-contentpress-thumbnail thumbnail" data-id="__FILE_ID__">\n' + '     <img src="__FILE_URL__" alt="" class="contentpress-thumbnail"/>\n' + '</a>\n' + '</div>';
  var __dropifySetup = false;
  var dropifyImageUploader = new DropifyImageUploader(
  /* the selector for the file upload field */
  $('#dropify_image_field')); //</editor-fold desc=":: IMAGE UPLOADER ::">
  //#! Media Modal

  $mediaModal.on('show.bs.modal', function (ev) {
    //#! The element triggering the modal to open
    var sender = $(ev.relatedTarget);
    var $modal = $(this);
    var $modalBody = $modal.find('.modal-body');
    var $modalContent = $modalBody.find('.js-content');
    var $modalContentList = $modalContent.find('.contentpress-media-list');
    $modal.find('.modal-title').text(pageLocale.text_media); //#! Setup the on-click event listener for all existent media files inside the modal

    if (!__set) {
      __setupImageOnClickListener($modal, sender);

      __set = true;
    }

    $modalContent.removeClass('hidden');

    if (!__dropifySetup) {
      dropifyImageUploader.setup({
        on_add_image: function on_add_image($this) {
          var value = $this.element[0].files[0];

          if (value.length < 1) {
            return false;
          }

          var ajaxData = new FormData();
          ajaxData.append('action', 'media_upload_image');
          ajaxData.append('media_image', value);
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
                if (r.data) {
                  $this.ajaxResponse = r.data; //!# Add the image to the list

                  var __template = imageTemplate;
                  __template = __template.replace(/__FILE_ID__/g, r.data.id).replace('__FILE_URL__', r.data.url);
                  $modalContentList.prepend(__template);
                  $modalContentList.find('.js--info').addClass('hidden');

                  __setupImageOnClickListener($modalContentList, sender); //#! Update preview and the hidden field


                  $imageInput.val(r.data.id);
                  $imagePreview.attr('src', r.data.url).removeClass('hidden');
                  showToast(pageLocale.text_image_set, 'success');
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
          });
        },
        on_remove_image: function on_remove_image($this) {
          $.ajax({
            url: locale.ajax.url,
            method: 'POST',
            async: true,
            timeout: 29000,
            data: _defineProperty({
              action: 'modal_delete_image',
              id: $this.ajaxResponse.id
            }, locale.nonce_name, locale.nonce_value)
          }).done(function (r) {
            if (r) {
              if (r.success) {
                if ($this.ajaxResponse.id) {
                  var $image = $('.js--item[data-id="' + $this.ajaxResponse.id + '"]', $modalContentList);

                  if ($image) {
                    $image.remove();
                  } //#! Update preview and the hidden field


                  $imageInput.val('');
                  $imagePreview.addClass('hidden').attr('src', ''); //#! Display the no content alert if there are no images

                  var _images = $modalContentList.find('.js--item');

                  if (!_images || _images.length < 1) {
                    $modalContentList.find('.js--info').removeClass('hidden');
                  }
                }

                showToast(pageLocale.text_image_removed, 'success');
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
          });
        }
      });
      __dropifySetup = true;
    }
  }).on('hidden.bs.modal', function (ev) {
    //#! Clear the preview of the uploaded image
    var dropify = $('#dropify_image_field').dropify();
    dropify = dropify.data('dropify');
    dropify.resetPreview();
  }); //#! END Media Modal
});

/***/ }),

/***/ 20:
/*!**********************************************************!*\
  !*** multi ./resources/js/admin/_scripts/media/modal.js ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\www\contentpress\resources\js\admin\_scripts\media\modal.js */"./resources/js/admin/_scripts/media/modal.js");


/***/ })

/******/ });