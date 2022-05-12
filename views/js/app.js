/******/ (function() { // webpackBootstrap
    /******/ 	"use strict";
    /******/ 	var __webpack_modules__ = ({

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



            var MoloniSettings = /*#__PURE__*/function () {
                function MoloniSettings() {
                    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, MoloniSettings);

                    this.settingIdPrefix = 'settings_form_';
                    this.settingRowHolder = 'settings_row_';
                }

                (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(MoloniSettings, [{
                    key: "startObservers",
                    value: function startObservers() {
                        console.log('oi');
                    }
                }, {
                    key: "onShippingInfoChange",
                    value: function onShippingInfoChange() {}
                }, {
                    key: "onAddressChange",
                    value: function onAddressChange() {}
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

        var moloniSettings = new _settings_settings__WEBPACK_IMPORTED_MODULE_0__["default"]();
    }();
    /******/ })()
;
