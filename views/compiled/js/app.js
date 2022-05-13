/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./js/enums/Boolean.js":
/*!*****************************!*\
  !*** ./js/enums/Boolean.js ***!
  \*****************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Boolean": function() { return /* binding */ Boolean; }
/* harmony export */ });
var Boolean = {
  NO: 0,
  YES: 1
};


/***/ }),

/***/ "./js/enums/DocumentStatus.js":
/*!************************************!*\
  !*** ./js/enums/DocumentStatus.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "DocumentStatus": function() { return /* binding */ DocumentStatus; }
/* harmony export */ });
var DocumentStatus = {
  DRAFT: 0,
  CLOSED: 1
};


/***/ }),

/***/ "./js/enums/DocumentType.js":
/*!**********************************!*\
  !*** ./js/enums/DocumentType.js ***!
  \**********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "DocumentType": function() { return /* binding */ DocumentType; }
/* harmony export */ });
var DocumentType = {
  INVOICES: 'invoices',
  RECEIPTS: 'receipts',
  PURCHASE_ORDERS: 'purchaseOrders',
  PRO_FORMA_INVOICES: 'proFormaInvoices',
  SIMPLIFIED_INVOICES: 'simplifiedInvoices'
};


/***/ }),

/***/ "./js/enums/LoadAddress.js":
/*!*********************************!*\
  !*** ./js/enums/LoadAddress.js ***!
  \*********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "LoadAddress": function() { return /* binding */ LoadAddress; }
/* harmony export */ });
var LoadAddress = {
  SHOP: 1,
  MOLONI: 2,
  CUSTOM: 3
};


/***/ }),

/***/ "./js/settings/settings.js":
/*!*********************************!*\
  !*** ./js/settings/settings.js ***!
  \*********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ MoloniSettings; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _enums_LoadAddress__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../enums/LoadAddress */ "./js/enums/LoadAddress.js");
/* harmony import */ var _enums_DocumentStatus__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../enums/DocumentStatus */ "./js/enums/DocumentStatus.js");
/* harmony import */ var _enums_DocumentType__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../enums/DocumentType */ "./js/enums/DocumentType.js");
/* harmony import */ var _enums_Boolean__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../enums/Boolean */ "./js/enums/Boolean.js");







var MoloniSettings = /*#__PURE__*/function () {
  function MoloniSettings() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, MoloniSettings);

    this.settingIdPrefix = 'settings_form_';
  }

  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(MoloniSettings, [{
    key: "startObservers",
    value: function startObservers() {
      // Holders
      this.$loadAddressHolder = $('#' + this.settingIdPrefix + 'loadAddress_row');
      this.$customLoadAddressHolder = $('#' + this.settingIdPrefix + 'custom_loadAddress_row');
      this.$sendByEmailHolder = $('#' + this.settingIdPrefix + 'sendByEmail_row');
      this.$billOfLandingHolder = $('#' + this.settingIdPrefix + 'billOfLanding_row'); // Fields

      this.$shippingInfo = $('#' + this.settingIdPrefix + 'shippingInformation');
      this.$loadAddress = $('#' + this.settingIdPrefix + 'loadAddress');
      this.$documentStatus = $('#' + this.settingIdPrefix + 'documentStatus');
      this.$documentType = $('#' + this.settingIdPrefix + 'documentType');
      this.$sendByEmail = $('#' + this.settingIdPrefix + 'sendByEmail');
      this.$billOfLanding = $('#' + this.settingIdPrefix + 'billOfLanding'); // Actions

      this.$documentStatus.on('change', this.onDocumentStatusChange.bind(this)).trigger('change');
      this.$shippingInfo.on('change', this.onShippingInformationChange.bind(this)).trigger('change');
      this.$loadAddress.on('change', this.onAddressChange.bind(this)).trigger('change');
      this.$documentType.on('change', this.onDocumentTypeChange.bind(this)).trigger('change');
    }
  }, {
    key: "onDocumentTypeChange",
    value: function onDocumentTypeChange(event) {
      if (event.target.value === _enums_DocumentType__WEBPACK_IMPORTED_MODULE_4__.DocumentType.RECEIPTS) {
        this.$documentStatus.val(_enums_DocumentStatus__WEBPACK_IMPORTED_MODULE_3__.DocumentStatus.CLOSED).attr('disabled', true).trigger('change');
      } else {
        this.$documentStatus.removeAttr('disabled');
      }
    }
  }, {
    key: "onDocumentStatusChange",
    value: function onDocumentStatusChange(event) {
      switch (parseInt(event.target.value)) {
        case _enums_DocumentStatus__WEBPACK_IMPORTED_MODULE_3__.DocumentStatus.DRAFT:
          this.$sendByEmailHolder.slideUp(200);
          this.$billOfLandingHolder.slideUp(200);
          this.$sendByEmail.val(_enums_Boolean__WEBPACK_IMPORTED_MODULE_5__.Boolean.NO);
          this.$billOfLanding.val(_enums_Boolean__WEBPACK_IMPORTED_MODULE_5__.Boolean.NO);
          break;

        case _enums_DocumentStatus__WEBPACK_IMPORTED_MODULE_3__.DocumentStatus.CLOSED:
          this.$sendByEmailHolder.slideDown(200);
          this.$billOfLandingHolder.slideDown(200);
          break;
      }
    }
  }, {
    key: "onShippingInformationChange",
    value: function onShippingInformationChange(event) {
      if (parseInt(event.target.value) > 0) {
        this.$loadAddressHolder.slideDown(200);
      } else {
        this.$loadAddressHolder.slideUp(200);
        this.$loadAddress.val(_enums_LoadAddress__WEBPACK_IMPORTED_MODULE_2__.LoadAddress.SHOP).trigger('change');
      }
    }
  }, {
    key: "onAddressChange",
    value: function onAddressChange(event) {
      if (parseInt(event.target.value) === _enums_LoadAddress__WEBPACK_IMPORTED_MODULE_2__.LoadAddress.CUSTOM) {
        this.$customLoadAddressHolder.slideDown(200);
      } else {
        this.$customLoadAddressHolder.slideUp(200);
      }
    }
  }]);

  return MoloniSettings;
}();



/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/classCallCheck.js ***!
  \*******************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ _classCallCheck; }
/* harmony export */ });
function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/createClass.js":
/*!****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/createClass.js ***!
  \****************************************************************/
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ _createClass; }
/* harmony export */ });
function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  Object.defineProperty(Constructor, "prototype", {
    writable: false
  });
  return Constructor;
}

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!*******************!*\
  !*** ./js/app.js ***!
  \*******************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _settings_settings__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./settings/settings */ "./js/settings/settings.js");

$(document).ready(function () {
  console.log('Moloni module loaded');
  window.moloniSettings = new _settings_settings__WEBPACK_IMPORTED_MODULE_0__["default"]();
});
}();
/******/ })()
;