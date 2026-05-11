"use strict";

function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i.return) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
(function ($) {
  'use strict';

  var addressAutocompletePreview = {
    // Cache DOM elements
    $apiKeyInput: null,
    $addressInput: null,
    // Store autocomplete instance
    placeAutocompleteElement: null,
    // Store bootstrap script reference
    bootstrapScript: null,
    // Configuration
    config: {
      debug: false,
      apiKeyLength: 39,
      retryAttempts: 3,
      retryDelay: 1000,
      scriptLoadTimeout: 5000
    },
    /**
     * Initialize the preview autocomplete
     */
    init: function init() {
      var _this = this;
      // Cache DOM elements
      this.$apiKeyInput = $('.merchant-module-page-setting-field[data-id="api_key"] input');
      this.$addressInput = $('#merchant-address-autocomplete');

      // Validate required elements exist
      if (!this.$apiKeyInput.length || !this.$addressInput.length) {
        this.handleError('Required DOM elements not found', 'warning');
        return;
      }

      // Trigger activation on page load
      this.activateAutocompleteIfNeeded();

      // Handle changes to the API key input with debouncing
      var debounceTimer;
      this.$apiKeyInput.on('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
          _this.activateAutocompleteIfNeeded();
        }, 500);
      });
      this.log('Preview autocomplete initialized');
    },
    /**
     * Check if the API key is valid and activate Autocomplete
     */
    activateAutocompleteIfNeeded: function activateAutocompleteIfNeeded() {
      var apiKey = this.$apiKeyInput.val();
      if (apiKey && this.validateApiKey(apiKey)) {
        this.loadGoogleMapsScript(apiKey);
      } else {
        this.deactivateAutocomplete();
      }
    },
    /**
     * Validate API key format
     * @param {string} apiKey - The API key to validate
     * @returns {boolean}
     */
    validateApiKey: function validateApiKey(apiKey) {
      if (typeof apiKey !== 'string') {
        return false;
      }

      // Basic length check
      if (apiKey.length !== this.config.apiKeyLength) {
        this.log("Invalid API key length: ".concat(apiKey.length, ", expected: ").concat(this.config.apiKeyLength), 'warning');
        return false;
      }

      // Check for valid characters (alphanumeric, hyphens, underscores)
      var validPattern = /^[A-Za-z0-9_-]+$/;
      if (!validPattern.test(apiKey)) {
        this.log('API key contains invalid characters', 'warning');
        return false;
      }
      return true;
    },
    /**
     * Load the Google Maps script using the inline bootstrap loader
     * @param {string} apiKey - The Google Maps API key
     */
    loadGoogleMapsScript: function loadGoogleMapsScript(apiKey) {
      // Deactivate any existing instance first
      this.deactivateAutocomplete();
      try {
        var _window$google;
        // Check if Google Maps is already loaded
        if ((_window$google = window.google) !== null && _window$google !== void 0 && (_window$google = _window$google.maps) !== null && _window$google !== void 0 && _window$google.importLibrary) {
          this.log('Google Maps already loaded, initializing autocomplete');
          this.initializeAutocomplete();
          return;
        }

        // Inject the inline bootstrap loader
        this.bootstrapScript = document.createElement('script');
        this.bootstrapScript.textContent = this.getBootstrapLoaderScript(apiKey);
        this.bootstrapScript.setAttribute('data-merchant-maps-loader', 'true');
        document.head.appendChild(this.bootstrapScript);

        // Initialize autocomplete with timeout protection
        this.initializeAutocompleteWithTimeout();
        this.log('Google Maps script injected');
      } catch (error) {
        this.handleError('Failed to load Google Maps script', 'error', error);
        this.deactivateAutocomplete();
      }
    },
    /**
     * Get the Google Maps bootstrap loader script
     * @param {string} apiKey - The API key
     * @returns {string} Bootstrap loader script
     */
    getBootstrapLoaderScript: function getBootstrapLoaderScript(apiKey) {
      // Sanitize API key
      var sanitizedKey = this.sanitizeValue(apiKey);
      return "\n\t\t\t\t(g=>{var h,a,k,p=\"The Google Maps JavaScript API\",c=\"google\",l=\"importLibrary\",q=\"__ib__\",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement(\"script\"));e.set(\"libraries\",[...r]+\"\");for(k in g)e.set(k.replace(/[A-Z]/g,t=>\"_\"+t[0].toLowerCase()),g[k]);e.set(\"callback\",c+\".maps.\"+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+\" could not load.\"));a.nonce=m.querySelector(\"script[nonce]\")?.nonce||\"\";m.head.append(a)}));d[l]?console.warn(p+\" only loads once. Ignoring:\",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({\n\t\t\t\t\tkey: \"".concat(sanitizedKey, "\",\n\t\t\t\t\tv: \"weekly\"\n\t\t\t\t});\n\t\t\t");
    },
    /**
     * Initialize autocomplete with timeout protection
     */
    initializeAutocompleteWithTimeout: function initializeAutocompleteWithTimeout() {
      var _this2 = this;
      var timeout = setTimeout(function () {
        _this2.handleError('Google Maps script load timeout', 'error');
        _this2.deactivateAutocomplete();
      }, this.config.scriptLoadTimeout);
      this.initializeAutocomplete().then(function () {
        clearTimeout(timeout);
      }).catch(function (error) {
        clearTimeout(timeout);
        _this2.handleError('Failed to initialize autocomplete', 'error', error);
        _this2.deactivateAutocomplete();
      });
    },
    /**
     * Initialize the PlaceAutocompleteElement
     * @returns {Promise<void>}
     */
    initializeAutocomplete: function () {
      var _initializeAutocomplete = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2() {
        var _this3 = this;
        var attempt,
          _yield$google$maps$im,
          PlaceAutocompleteElement,
          _args2 = arguments,
          _t;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.p = _context2.n) {
            case 0:
              attempt = _args2.length > 0 && _args2[0] !== undefined ? _args2[0] : 1;
              _context2.p = 1;
              _context2.n = 2;
              return this.waitForGoogleMaps();
            case 2:
              _context2.n = 3;
              return google.maps.importLibrary('places');
            case 3:
              _yield$google$maps$im = _context2.v;
              PlaceAutocompleteElement = _yield$google$maps$im.PlaceAutocompleteElement;
              // Create the new PlaceAutocompleteElement
              this.placeAutocompleteElement = new PlaceAutocompleteElement();

              // Copy styling from original input
              if (this.$addressInput.attr('class')) {
                this.placeAutocompleteElement.className = this.$addressInput.attr('class');
              }

              // Hide the original input field
              this.$addressInput.hide();

              // Insert the autocomplete element after the hidden input
              this.$addressInput.after(this.placeAutocompleteElement);

              // Listen for place selection
              this.placeAutocompleteElement.addEventListener('gmp-select', /*#__PURE__*/function () {
                var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(event) {
                  return _regenerator().w(function (_context) {
                    while (1) switch (_context.n) {
                      case 0:
                        _context.n = 1;
                        return _this3.handlePlaceSelection(event.placePrediction);
                      case 1:
                        return _context.a(2);
                    }
                  }, _callee);
                }));
                return function (_x) {
                  return _ref.apply(this, arguments);
                };
              }());
              this.log('Autocomplete element initialized successfully');
              _context2.n = 7;
              break;
            case 4:
              _context2.p = 4;
              _t = _context2.v;
              if (!(attempt < this.config.retryAttempts)) {
                _context2.n = 6;
                break;
              }
              this.log("Retry attempt ".concat(attempt + 1, " to initialize autocomplete"));
              _context2.n = 5;
              return this.delay(this.config.retryDelay);
            case 5:
              return _context2.a(2, this.initializeAutocomplete(attempt + 1));
            case 6:
              throw _t;
            case 7:
              return _context2.a(2);
          }
        }, _callee2, this, [[1, 4]]);
      }));
      function initializeAutocomplete() {
        return _initializeAutocomplete.apply(this, arguments);
      }
      return initializeAutocomplete;
    }(),
    /**
     * Wait for Google Maps to be available
     * @returns {Promise<void>}
     */
    waitForGoogleMaps: function waitForGoogleMaps() {
      return new Promise(function (resolve, reject) {
        var maxAttempts = 50;
        var attempts = 0;
        var _checkGoogleMaps = function checkGoogleMaps() {
          var _window$google2;
          if ((_window$google2 = window.google) !== null && _window$google2 !== void 0 && (_window$google2 = _window$google2.maps) !== null && _window$google2 !== void 0 && _window$google2.importLibrary) {
            resolve();
          } else if (attempts >= maxAttempts) {
            reject(new Error('Google Maps failed to load'));
          } else {
            attempts++;
            setTimeout(_checkGoogleMaps, 100);
          }
        };
        _checkGoogleMaps();
      });
    },
    /**
     * Handle place selection from autocomplete
     * @param {Object} placePrediction - Place prediction object
     */
    handlePlaceSelection: function () {
      var _handlePlaceSelection = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3(placePrediction) {
        var place, formattedAddress, _t2;
        return _regenerator().w(function (_context3) {
          while (1) switch (_context3.p = _context3.n) {
            case 0:
              _context3.p = 0;
              // Disable input during processing
              this.setLoadingState(true);
              place = placePrediction.toPlace(); // Fetch the fields you need
              _context3.n = 1;
              return place.fetchFields({
                fields: ['displayName', 'formattedAddress', 'location', 'addressComponents']
              });
            case 1:
              // Sanitize and update the hidden input
              formattedAddress = this.sanitizeValue(place.formattedAddress || '');
              this.$addressInput.val(formattedAddress);

              // Trigger change event
              this.$addressInput.trigger('change');
              this.log('Selected place:', place.toJSON());
              _context3.n = 3;
              break;
            case 2:
              _context3.p = 2;
              _t2 = _context3.v;
              this.handleError('Error fetching place details', 'error', _t2);
            case 3:
              _context3.p = 3;
              this.setLoadingState(false);
              return _context3.f(3);
            case 4:
              return _context3.a(2);
          }
        }, _callee3, this, [[0, 2, 3, 4]]);
      }));
      function handlePlaceSelection(_x2) {
        return _handlePlaceSelection.apply(this, arguments);
      }
      return handlePlaceSelection;
    }(),
    /**
     * Set loading state
     * @param {boolean} isLoading - Loading state
     */
    setLoadingState: function setLoadingState(isLoading) {
      if (this.placeAutocompleteElement) {
        if (isLoading) {
          this.placeAutocompleteElement.classList.add('loading');
        } else {
          this.placeAutocompleteElement.classList.remove('loading');
        }
      }
    },
    /**
     * Sanitize input value to prevent XSS
     * @param {string} value - Value to sanitize
     * @returns {string} Sanitized value
     */
    sanitizeValue: function sanitizeValue(value) {
      if (typeof value !== 'string') {
        return '';
      }
      return $('<div>').text(value).html();
    },
    /**
     * Deactivate and clean up autocomplete
     */
    deactivateAutocomplete: function deactivateAutocomplete() {
      // Remove the PlaceAutocompleteElement
      if (this.placeAutocompleteElement) {
        try {
          this.placeAutocompleteElement.remove();
        } catch (error) {
          this.log('Error removing autocomplete element', 'warning');
        }
        this.placeAutocompleteElement = null;
      }

      // Clean up Google Maps scripts
      this.cleanupGoogleMapsScripts();

      // Reset address field
      this.resetAddressField();
      this.log('Autocomplete deactivated');
    },
    /**
     * Clean up Google Maps scripts from DOM
     */
    cleanupGoogleMapsScripts: function cleanupGoogleMapsScripts() {
      try {
        var _window$google3;
        // Remove Google Maps from window
        if ((_window$google3 = window.google) !== null && _window$google3 !== void 0 && _window$google3.maps) {
          delete window.google;
        }

        // Remove all Google Maps related scripts
        document.querySelectorAll('script').forEach(function (script) {
          if (script.src.includes('googleapis.com/maps') || script.src.includes('maps.gstatic.com') || script.textContent.includes('importLibrary') || script.getAttribute('data-merchant-maps-loader')) {
            script.remove();
          }
        });
        this.bootstrapScript = null;
        this.log('Google Maps scripts cleaned up');
      } catch (error) {
        this.handleError('Error cleaning up scripts', 'warning', error);
      }
    },
    /**
     * Reset the address input field to default state
     */
    resetAddressField: function resetAddressField() {
      if (this.$addressInput && this.$addressInput.length) {
        this.$addressInput.val('');
        this.$addressInput.show();
        this.$addressInput.removeClass('loading');
      }
    },
    /**
     * Handle errors with different severity levels
     * @param {string} message - Error message
     * @param {string} severity - Error severity (critical, error, warning)
     * @param {Error} error - Original error object
     */
    handleError: function handleError(message) {
      var severity = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'error';
      var error = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
      var fullMessage = error ? "".concat(message, ": ").concat(error.message) : message;
      if (severity === 'critical' || severity === 'error') {
        console.error("[AddressAutocompletePreview] ".concat(fullMessage), error);
      } else {
        console.warn("[AddressAutocompletePreview] ".concat(fullMessage));
      }
      this.log(fullMessage, severity);
    },
    /**
     * Debug logging
     * @param {*} message - Log message
     * @param {string} level - Log level
     */
    log: function log(message) {
      var level = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'log';
      if (this.config.debug && typeof console !== 'undefined') {
        var timestamp = new Date().toISOString();
        console[level]("[AddressAutocompletePreview ".concat(timestamp, "]"), message);
      }
    },
    /**
     * Delay helper for retry logic
     * @param {number} ms - Milliseconds to delay
     * @returns {Promise}
     */
    delay: function delay(ms) {
      return new Promise(function (resolve) {
        return setTimeout(resolve, ms);
      });
    }
  };

  // Initialize when DOM is ready
  $(document).ready(function () {
    addressAutocompletePreview.init();
  });

  // Cleanup on page unload
  $(window).on('beforeunload', function () {
    addressAutocompletePreview.deactivateAutocomplete();
  });

  // Expose to global scope for debugging (optional)
  if (addressAutocompletePreview.config.debug) {
    window.addressAutocompletePreview = addressAutocompletePreview;
  }
})(jQuery);