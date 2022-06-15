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
  INVOICE_AND_RECEIPT: 'invoiceAndReceipts',
  PURCHASE_ORDERS: 'purchaseOrders',
  PRO_FORMA_INVOICES: 'proFormaInvoices',
  SIMPLIFIED_INVOICES: 'simplifiedInvoices',
  ESTIMATE: 'estimate'
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
  CUSTOM: -1,
  MOLONI: 0
};


/***/ }),

/***/ "./js/pages/documents.js":
/*!*******************************!*\
  !*** ./js/pages/documents.js ***!
  \*******************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ MoloniDocuments; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _paginator_paginator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../paginator/paginator */ "./js/paginator/paginator.js");




var MoloniDocuments = /*#__PURE__*/function () {
  function MoloniDocuments() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, MoloniDocuments);
  }

  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(MoloniDocuments, [{
    key: "startObservers",
    value: function startObservers() {
      new _paginator_paginator__WEBPACK_IMPORTED_MODULE_2__["default"]();
    }
  }]);

  return MoloniDocuments;
}();



/***/ }),

/***/ "./js/pages/login.js":
/*!***************************!*\
  !*** ./js/pages/login.js ***!
  \***************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ MoloniLogin; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");



var MoloniLogin = /*#__PURE__*/function () {
  function MoloniLogin() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, MoloniLogin);
  }

  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(MoloniLogin, [{
    key: "startObservers",
    value: function startObservers() {}
  }]);

  return MoloniLogin;
}();



/***/ }),

/***/ "./js/pages/logs.js":
/*!**************************!*\
  !*** ./js/pages/logs.js ***!
  \**************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ MoloniLogs; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _paginator_paginator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../paginator/paginator */ "./js/paginator/paginator.js");




var MoloniLogs = /*#__PURE__*/function () {
  function MoloniLogs() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, MoloniLogs);
  }

  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(MoloniLogs, [{
    key: "startObservers",
    value: function startObservers() {
      new _paginator_paginator__WEBPACK_IMPORTED_MODULE_2__["default"]();
    }
  }]);

  return MoloniLogs;
}();



/***/ }),

/***/ "./js/pages/orders.js":
/*!****************************!*\
  !*** ./js/pages/orders.js ***!
  \****************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ MoloniOrders; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _paginator_paginator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../paginator/paginator */ "./js/paginator/paginator.js");




var MoloniOrders = /*#__PURE__*/function () {
  function MoloniOrders() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, MoloniOrders);
  }

  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(MoloniOrders, [{
    key: "startObservers",
    value: function startObservers() {
      new _paginator_paginator__WEBPACK_IMPORTED_MODULE_2__["default"]();
    }
  }]);

  return MoloniOrders;
}();



/***/ }),

/***/ "./js/pages/registration.js":
/*!**********************************!*\
  !*** ./js/pages/registration.js ***!
  \**********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ MoloniRegistration; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");



var MoloniRegistration = /*#__PURE__*/function () {
  function MoloniRegistration() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, MoloniRegistration);
  }

  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(MoloniRegistration, [{
    key: "startObservers",
    value: function startObservers() {}
  }]);

  return MoloniRegistration;
}();



/***/ }),

/***/ "./js/pages/settings.js":
/*!******************************!*\
  !*** ./js/pages/settings.js ***!
  \******************************/
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

    this.settingIdPrefix = 'MoloniSettings_';
  }

  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(MoloniSettings, [{
    key: "startObservers",
    value: function startObservers() {
      // Holders
      this.$loadAddressHolder = $('#settings_form_loadAddress_row');
      this.$customLoadAddressHolder = $('#settings_form_custom_loadAddress_row');
      this.$sendByEmailHolder = $('#settings_form_sendByEmail_row');
      this.$billOfLadingHolder = $('#settings_form_billOfLading_row'); // Fields

      this.$shippingInfo = $('#' + this.settingIdPrefix + 'shippingInformation');
      this.$loadAddress = $('#' + this.settingIdPrefix + 'loadAddress');
      this.$documentStatus = $('#' + this.settingIdPrefix + 'documentStatus');
      this.$documentType = $('#' + this.settingIdPrefix + 'documentType');
      this.$sendByEmail = $('#' + this.settingIdPrefix + 'sendByEmail');
      this.$billOfLading = $('#' + this.settingIdPrefix + 'billOfLading'); // Actions

      this.$documentStatus.on('change', this.onDocumentStatusChange.bind(this)).trigger('change');
      this.$shippingInfo.on('change', this.onShippingInformationChange.bind(this)).trigger('change');
      this.$loadAddress.on('change', this.onAddressChange.bind(this)).trigger('change');
      this.$documentType.on('change', this.onDocumentTypeChange.bind(this)).trigger('change');
    }
  }, {
    key: "onDocumentTypeChange",
    value: function onDocumentTypeChange(event) {
      if (event.target.value === _enums_DocumentType__WEBPACK_IMPORTED_MODULE_4__.DocumentType.INVOICE_AND_RECEIPT) {
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
          this.$billOfLadingHolder.slideUp(200);
          this.$sendByEmail.val(_enums_Boolean__WEBPACK_IMPORTED_MODULE_5__.Boolean.NO);
          this.$billOfLading.val(_enums_Boolean__WEBPACK_IMPORTED_MODULE_5__.Boolean.NO);
          break;

        case _enums_DocumentStatus__WEBPACK_IMPORTED_MODULE_3__.DocumentStatus.CLOSED:
          this.$sendByEmailHolder.slideDown(200);
          this.$billOfLadingHolder.slideDown(200);
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
        this.$loadAddress.val(_enums_LoadAddress__WEBPACK_IMPORTED_MODULE_2__.LoadAddress.MOLONI).trigger('change');
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

/***/ "./js/pages/tools.js":
/*!***************************!*\
  !*** ./js/pages/tools.js ***!
  \***************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ MoloniTools; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _tools_syncProducts__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../tools/syncProducts */ "./js/tools/syncProducts.js");
/* harmony import */ var _tools_syncStock__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../tools/syncStock */ "./js/tools/syncStock.js");





var MoloniTools = /*#__PURE__*/function () {
  function MoloniTools() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, MoloniTools);
  }

  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(MoloniTools, [{
    key: "startObservers",
    value: function startObservers() {
      $('#import_products_button').on('click', this.syncProducts);
      $('#synchronize_stocks_button').on('click', this.syncStock);
    }
  }, {
    key: "syncProducts",
    value: function syncProducts() {
      var action = $(this).attr('data-href');
      (0,_tools_syncProducts__WEBPACK_IMPORTED_MODULE_2__["default"])({
        action: action
      });
    }
  }, {
    key: "syncStock",
    value: function syncStock() {
      var action = $(this).attr('data-href');
      (0,_tools_syncStock__WEBPACK_IMPORTED_MODULE_3__["default"])({
        action: action
      });
    }
  }]);

  return MoloniTools;
}();



/***/ }),

/***/ "./js/paginator/paginator.js":
/*!***********************************!*\
  !*** ./js/paginator/paginator.js ***!
  \***********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ Paginator; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");



var Paginator = /*#__PURE__*/function () {
  function Paginator() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, Paginator);

    this.startObservers();
  }

  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(Paginator, [{
    key: "startObservers",
    value: function startObservers() {
      var paginator = $('input[name="moloni_paginator"]');

      if (paginator.length) {
        paginator.on('focusout', this.onLoseFocus);
      }
    }
  }, {
    key: "onLoseFocus",
    value: function onLoseFocus() {
      var pageNumberInput = $(this);
      var page = parseInt(pageNumberInput.val());
      var value = parseInt(pageNumberInput.attr('value'));
      var url = pageNumberInput.attr('psurl');
      var psmax = parseInt(pageNumberInput.attr('psmax'));

      if (page === value) {
        return;
      }

      pageNumberInput.attr('disabled', true);

      if (page > psmax) {
        page = psmax;
      }

      if (page < 0) {
        page = 1;
      }

      pageNumberInput.val(page);
      url = url + '&page=' + page;
      window.location.href = url;
    }
  }]);

  return Paginator;
}();



/***/ }),

/***/ "./js/tools/makeRequest.js":
/*!*********************************!*\
  !*** ./js/tools/makeRequest.js ***!
  \*********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
var MakeRequest = async function MakeRequest(action, data) {
  return $.post({
    url: action,
    cache: false,
    data: data
  });
};

/* harmony default export */ __webpack_exports__["default"] = (MakeRequest);

/***/ }),

/***/ "./js/tools/syncProducts.js":
/*!**********************************!*\
  !*** ./js/tools/syncProducts.js ***!
  \**********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _makeRequest__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./makeRequest */ "./js/tools/makeRequest.js");


var SyncProducts = async function SyncProducts(_ref) {
  var action = _ref.action;
  var actionButton = $('#action_overlay_button');
  var actionModal = $('#action_overlay_modal');
  var closeButton = actionModal.find('#action_overlay_button');
  var spinner = actionModal.find('#action_overlay_spinner');
  var content = actionModal.find('#action_overlay_content');
  var error = actionModal.find('#action_overlay_error');
  content.html('').hide();
  closeButton.hide();
  error.hide();
  spinner.show();
  actionButton.trigger('click');
  var page = 1;

  var toogleContent = function toogleContent() {
    spinner.fadeOut(100, function () {
      content.fadeIn(200);
    });
  };

  var sync = async function sync() {
    var resp = await (0,_makeRequest__WEBPACK_IMPORTED_MODULE_0__["default"])(action, {
      page: page
    });
    resp = JSON.parse(resp);

    if (page === 1) {
      toogleContent();
    }

    content.html(resp.overlayContent);

    if (resp.hasMore && actionModal.is(':visible')) {
      page = page + 1;
      return await sync();
    }
  };

  try {
    await sync();
  } catch (ex) {
    spinner.fadeOut(50);
    content.fadeOut(50);
    error.fadeIn(200);
  }

  closeButton.show(200);
};

/* harmony default export */ __webpack_exports__["default"] = (SyncProducts);

/***/ }),

/***/ "./js/tools/syncStock.js":
/*!*******************************!*\
  !*** ./js/tools/syncStock.js ***!
  \*******************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _makeRequest__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./makeRequest */ "./js/tools/makeRequest.js");


var SyncStock = async function SyncStock(_ref) {
  var action = _ref.action;
  var actionButton = $('#action_overlay_button');
  var actionModal = $('#action_overlay_modal');
  var closeButton = actionModal.find('#action_overlay_button');
  var spinner = actionModal.find('#action_overlay_spinner');
  var content = actionModal.find('#action_overlay_content');
  var error = actionModal.find('#action_overlay_error');
  content.html('').hide();
  closeButton.hide();
  error.hide();
  spinner.show();
  actionButton.trigger('click');
  var page = 1;

  var toogleContent = function toogleContent() {
    spinner.fadeOut(100, function () {
      content.fadeIn(200);
    });
  };

  var sync = async function sync() {
    var resp = await (0,_makeRequest__WEBPACK_IMPORTED_MODULE_0__["default"])(action, {
      page: page
    });
    resp = JSON.parse(resp);

    if (page === 1) {
      toogleContent();
    }

    content.html(resp.overlayContent);

    if (resp.hasMore && actionModal.is(':visible')) {
      page = page + 1;
      return await sync();
    }
  };

  try {
    await sync();
  } catch (ex) {
    spinner.fadeOut(50);
    content.fadeOut(50);
    error.fadeIn(200);
  }

  closeButton.show(200);
};

/* harmony default export */ __webpack_exports__["default"] = (SyncStock);

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
/* harmony import */ var _pages_settings__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./pages/settings */ "./js/pages/settings.js");
/* harmony import */ var _pages_orders__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./pages/orders */ "./js/pages/orders.js");
/* harmony import */ var _pages_documents__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./pages/documents */ "./js/pages/documents.js");
/* harmony import */ var _pages_tools__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./pages/tools */ "./js/pages/tools.js");
/* harmony import */ var _pages_logs__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./pages/logs */ "./js/pages/logs.js");
/* harmony import */ var _pages_login__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./pages/login */ "./js/pages/login.js");
/* harmony import */ var _pages_registration__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./pages/registration */ "./js/pages/registration.js");







$(document).ready(function () {
  window.moloniSettings = new _pages_settings__WEBPACK_IMPORTED_MODULE_0__["default"]();
  window.moloniOrders = new _pages_orders__WEBPACK_IMPORTED_MODULE_1__["default"]();
  window.moloniDocuments = new _pages_documents__WEBPACK_IMPORTED_MODULE_2__["default"]();
  window.moloniTools = new _pages_tools__WEBPACK_IMPORTED_MODULE_3__["default"]();
  window.moloniLogs = new _pages_logs__WEBPACK_IMPORTED_MODULE_4__["default"]();
  window.moloniLogin = new _pages_login__WEBPACK_IMPORTED_MODULE_5__["default"]();
  window.moloniRegistration = new _pages_registration__WEBPACK_IMPORTED_MODULE_6__["default"]();
});
}();
/******/ })()
;