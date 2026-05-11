'use strict';

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i.return) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
(function ($) {
  /**
   * Main object for managing merchant analytics charts.
   * @namespace merchantAnalyticsChart
   */
  var merchantAnalyticsChart = {
    AJAX_URL: merchant_analytics.ajax_url,
    NONCE: merchant_analytics.nonce,
    impressionsChart: null,
    revenueChart: null,
    avgOrderValChart: null,
    /**
     * Options for the impressions chart (bar chart).
     * @type {Object}
     */
    columnChartOptions: {
      series: [{
        data: []
      }],
      noData: {
        text: 'No data available',
        align: 'center',
        verticalAlign: 'middle',
        offsetX: 0,
        offsetY: 0,
        style: {
          color: '#686868',
          fontSize: '18px'
        }
      },
      chart: {
        type: 'bar',
        height: 350,
        stacked: false,
        toolbar: {
          show: false,
          offsetX: -10,
          offsetY: 10,
          tools: {
            download: false,
            selection: true,
            zoom: true,
            zoomin: true,
            zoomout: true,
            pan: false,
            reset: true
          }
        },
        zoom: {
          enabled: false,
          allowMouseWheelZoom: false
        }
      },
      plotOptions: {
        bar: {
          columnWidth: '20%',
          borderRadius: 5,
          borderRadiusApplication: 'end',
          colors: {
            backgroundBarColors: ['#ebeffd'],
            backgroundBarRadius: 4
          }
        }
      },
      colors: ['#3A63E9'],
      dataLabels: {
        enabled: false
      },
      grid: {
        show: true,
        borderColor: '#D8D8D8',
        strokeDashArray: 5,
        position: 'back',
        xaxis: {
          lines: {
            show: true,
            offsetX: 60,
            style: {
              dashArray: 5
            }
          }
        },
        yaxis: {
          lines: {
            show: false
          }
        }
      },
      xaxis: {
        axisTicks: {
          show: false
        },
        axisBorder: {
          show: true,
          color: '#D8D8D8',
          height: 1
        }
      },
      tooltip: {
        enabled: false
      }
    },
    /**
     * Options for the revenue chart (area chart).
     * @type {Object}
     */
    revenueChartOptions: {
      series: [{
        data: []
      }],
      noData: {
        text: 'No data available',
        align: 'center',
        verticalAlign: 'middle',
        offsetX: 0,
        offsetY: 0,
        style: {
          color: '#686868',
          fontSize: '18px'
        }
      },
      legend: {
        show: false // This hides the legend
      },
      chart: {
        type: 'area',
        height: 350,
        stacked: false,
        toolbar: {
          show: false,
          offsetX: -10,
          offsetY: 10,
          tools: {
            download: false,
            selection: true,
            zoom: true,
            zoomin: true,
            zoomout: true,
            pan: false,
            reset: true
          }
        },
        zoom: {
          enabled: false,
          allowMouseWheelZoom: false
        }
      },
      stroke: {
        curve: 'smooth',
        dashArray: 6,
        width: 2,
        lineCap: 'round'
      },
      fill: {
        type: 'gradient',
        gradient: {
          inverseColors: false,
          opacityFrom: 0.55,
          opacityTo: 0.05,
          stops: [10, 100]
        }
      },
      markers: {
        size: 5,
        colors: ['#fff'],
        strokeColors: '#3A63E9',
        strokeWidth: 2,
        hover: {
          size: 6
        }
      },
      colors: ['#3A63E9', '#393939'],
      dataLabels: {
        enabled: false
      },
      grid: {
        show: true,
        borderColor: '#D8D8D8',
        strokeDashArray: 5,
        position: 'back',
        xaxis: {
          lines: {
            show: true,
            offsetX: 60,
            style: {
              dashArray: 5
            }
          }
        },
        yaxis: {
          lines: {
            show: false
          }
        }
      },
      xaxis: {
        axisTicks: {
          show: false
        },
        axisBorder: {
          show: true,
          color: '#D8D8D8',
          height: 1
        },
        tooltip: {
          enabled: false
        }
      },
      tooltip: {
        fixed: {
          offsetX: 0,
          offsetY: 0
        },
        enabled: true,
        theme: false,
        custom: function custom(_ref) {
          var series = _ref.series,
            seriesIndex = _ref.seriesIndex,
            dataPointIndex = _ref.dataPointIndex,
            w = _ref.w;
          var current_data = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
          return "\n                        <div class=\"arrow-box\">\n                            <div class=\"box-wrapper\">\n                                <div class=\"box-column big\">\n                                    <div class=\"head\">\n                                        <div class=\"box-title\">Total Income</div>\n                                        <div class=\"box-value\">".concat(current_data.number_currency, "</div>\n                                    </div>\n                                    <div class=\"orders-count\">\n                                        <strong>").concat(current_data.orders_count, "</strong> ").concat(merchant_analytics.labels.orders, "\n                                    </div>\n                                </div>\n                                <div class=\"separator\"></div>\n                                <div class=\"box-column small\">\n                                    <div class=\"head\">\n                                        <svg width=\"64\" height=\"47\" viewBox=\"0 0 64 41\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n                                            <!-- SVG content -->\n                                        </svg>\n                                    </div>\n                                    <div class=\"change-percentage ").concat(current_data.diff_type, "\">\n                                        <strong>").concat(current_data.difference, "%</strong>\n                                    </div>\n                                </div>\n                            </div>\n                        </div>");
        }
      }
    },
    /**
     * Options for the revenue chart (area chart).
     * @type {Object}
     */
    widgetChartOptions: {
      series: [{
        data: []
      }],
      noData: {
        text: 'No data available',
        align: 'center',
        verticalAlign: 'middle',
        offsetX: 0,
        offsetY: 0,
        style: {
          color: '#686868',
          fontSize: '18px'
        }
      },
      legend: {
        show: false // This hides the legend
      },
      chart: {
        type: 'area',
        height: 350,
        stacked: false,
        toolbar: {
          show: false,
          offsetX: -10,
          offsetY: 10,
          tools: {
            download: false,
            selection: true,
            zoom: true,
            zoomin: true,
            zoomout: true,
            pan: false,
            reset: true
          }
        },
        zoom: {
          enabled: false,
          allowMouseWheelZoom: false
        }
      },
      stroke: {
        curve: 'smooth',
        // dashArray: 6,
        width: 2,
        lineCap: 'round'
      },
      fill: {
        type: 'gradient',
        colors: ['#3A63E9'],
        gradient: {
          inverseColors: false,
          opacityFrom: 0.55,
          opacityTo: 0.15,
          stops: [10, 100]
        }
      },
      colors: ['#3A63E9'],
      dataLabels: {
        enabled: false
      },
      grid: {
        show: true,
        borderColor: '#D8D8D8',
        strokeDashArray: 5,
        position: 'back',
        xaxis: {
          lines: {
            show: true,
            offsetX: 60,
            style: {
              dashArray: 5
            }
          }
        },
        yaxis: {
          lines: {
            show: false
          }
        }
      },
      xaxis: {
        axisTicks: {
          show: false
        },
        axisBorder: {
          show: true,
          color: '#D8D8D8',
          height: 1
        },
        tooltip: {
          enabled: false
        }
      },
      tooltip: {
        enabled: false
      }
    },
    /**
     * Options for the average order value (AOV) chart (area chart).
     * @type {Object}
     */
    avgOrderValChartOptions: {
      series: [{
        data: []
      }],
      noData: {
        text: 'No data available',
        align: 'center',
        verticalAlign: 'middle',
        offsetX: 0,
        offsetY: 0,
        style: {
          color: '#686868',
          fontSize: '18px'
        }
      },
      chart: {
        type: 'area',
        height: 350,
        stacked: false,
        toolbar: {
          show: false,
          offsetX: -10,
          offsetY: 10,
          tools: {
            download: false,
            selection: true,
            zoom: true,
            zoomin: true,
            zoomout: true,
            pan: false,
            reset: true
          }
        },
        zoom: {
          enabled: false,
          allowMouseWheelZoom: false
        }
      },
      stroke: {
        curve: 'straight',
        dashArray: 6,
        width: 2
      },
      fill: {
        type: 'gradient',
        gradient: {
          inverseColors: false,
          opacityFrom: 0.55,
          opacityTo: 0.05,
          stops: [10, 100]
        }
      },
      markers: {
        size: 5,
        colors: ['#fff'],
        strokeColors: '#7880CA',
        strokeWidth: 2,
        hover: {
          size: 6
        }
      },
      colors: ['#7880CA'],
      dataLabels: {
        enabled: false
      },
      grid: {
        show: true,
        borderColor: '#D8D8D8',
        strokeDashArray: 5,
        position: 'back',
        xaxis: {
          lines: {
            show: true,
            offsetX: 60,
            style: {
              dashArray: 5
            }
          }
        },
        yaxis: {
          lines: {
            show: false
          }
        }
      },
      xaxis: {
        axisTicks: {
          show: false
        },
        axisBorder: {
          show: true,
          color: '#D8D8D8',
          height: 1
        },
        tooltip: {
          enabled: false
        }
      },
      tooltip: {
        enabled: true,
        theme: false,
        custom: function custom(_ref2) {
          var series = _ref2.series,
            seriesIndex = _ref2.seriesIndex,
            dataPointIndex = _ref2.dataPointIndex,
            w = _ref2.w;
          var current_data = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
          return "\n                        <div class=\"arrow-box-aov\">\n                            <div class=\"box-title\">".concat(merchant_analytics.labels.orders_aov, "</div>\n                            <div class=\"box-value\">").concat(current_data.number_currency, " <span class=\"diff ").concat(current_data.diff_type, "\">").concat(current_data.difference, "%</span></div>\n                        </div>");
        }
      }
    },
    /**
     * Sends an AJAX request and returns the response.
     * @param {Object} data - The data to send with the request.
     * @param {string} [loadingIndicatorSelector] - Selector for the loading indicator element.
     * @param method - The HTTP method to use for the request.
     * @returns {Promise} - The resolved response or rejected error.
     */
    sendAjaxRequest: function () {
      var _sendAjaxRequest = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(data) {
        var loadingIndicatorSelector,
          method,
          _args = arguments,
          _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              loadingIndicatorSelector = _args.length > 1 && _args[1] !== undefined ? _args[1] : '';
              method = _args.length > 2 && _args[2] !== undefined ? _args[2] : 'GET';
              _context.p = 1;
              if (loadingIndicatorSelector) {
                $(loadingIndicatorSelector).addClass('show');
              }
              _context.n = 2;
              return $.ajax({
                url: this.AJAX_URL,
                method: method,
                data: data
              });
            case 2:
              return _context.a(2, _context.v);
            case 3:
              _context.p = 3;
              _t = _context.v;
              console.error('AJAX request failed:', _t);
              throw _t;
            case 4:
              _context.p = 4;
              if (loadingIndicatorSelector) {
                $(loadingIndicatorSelector).removeClass('show');
              }
              return _context.f(4);
            case 5:
              return _context.a(2);
          }
        }, _callee, this, [[1, 3, 4, 5]]);
      }));
      function sendAjaxRequest(_x) {
        return _sendAjaxRequest.apply(this, arguments);
      }
      return sendAjaxRequest;
    }(),
    /**
     * Prepares data for an AJAX request.
     * @param {string} action - The action to perform.
     * @param {string} startDate - The start date for the data range.
     * @param {string} endDate - The end date for the data range.
     * @param {string} compareStartDate - The start date for the comparison range.
     * @param {string} compareEndDate - The end date for the comparison range.
     * @returns {Object} - The prepared data object.
     */
    prepareAjaxData: function prepareAjaxData(action, startDate, endDate) {
      var compareStartDate = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';
      var compareEndDate = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : '';
      return {
        action: action,
        nonce: this.NONCE,
        start_date: startDate,
        end_date: endDate,
        compare_start_date: compareStartDate,
        compare_end_date: compareEndDate
      };
    },
    /**
     * Updates the impressions chart with new data.
     * @param {object} data - The selected date.
     */
    updateImpressionsChart: function () {
      var _updateImpressionsChart = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(data) {
        var _data$formattedDate, startDate, endDate, response, _t2;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.p = _context2.n) {
            case 0:
              _data$formattedDate = _slicedToArray(data.formattedDate, 2), startDate = _data$formattedDate[0], endDate = _data$formattedDate[1];
              _context2.p = 1;
              _context2.n = 2;
              return this.sendAjaxRequest(this.prepareAjaxData('merchant_get_impressions_chart_data', startDate, endDate), '.impressions-chart-section .merchant-analytics-loading-spinner');
            case 2:
              response = _context2.v;
              if (response.success) {
                this.impressionsChart.updateSeries([{
                  data: response.data
                }]);
              }
              _context2.n = 4;
              break;
            case 3:
              _context2.p = 3;
              _t2 = _context2.v;
              console.error('Error fetching impressions data:', _t2);
            case 4:
              return _context2.a(2);
          }
        }, _callee2, this, [[1, 3]]);
      }));
      function updateImpressionsChart(_x2) {
        return _updateImpressionsChart.apply(this, arguments);
      }
      return updateImpressionsChart;
    }(),
    /**
     * Updates the revenue chart with new data.
     * @param {object} data - The selected date.
     */
    updateRevenueChart: function () {
      var _updateRevenueChart = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3(data) {
        var _data$formattedDate2, startDate, endDate, response, _t3;
        return _regenerator().w(function (_context3) {
          while (1) switch (_context3.p = _context3.n) {
            case 0:
              _data$formattedDate2 = _slicedToArray(data.formattedDate, 2), startDate = _data$formattedDate2[0], endDate = _data$formattedDate2[1];
              _context3.p = 1;
              _context3.n = 2;
              return this.sendAjaxRequest(this.prepareAjaxData('merchant_get_revenue_chart_data', startDate, endDate), '.revenue-chart-section .merchant-analytics-loading-spinner');
            case 2:
              response = _context3.v;
              if (response.success) {
                this.revenueChart.updateSeries([{
                  data: response.data
                }]);
              }
              _context3.n = 4;
              break;
            case 3:
              _context3.p = 3;
              _t3 = _context3.v;
              console.error('Error fetching revenue data:', _t3);
            case 4:
              return _context3.a(2);
          }
        }, _callee3, this, [[1, 3]]);
      }));
      function updateRevenueChart(_x3) {
        return _updateRevenueChart.apply(this, arguments);
      }
      return updateRevenueChart;
    }(),
    /**
     * Updates the average order value (AOV) chart with new data.
     * @param {object} data - The selected date.
     */
    updateAOVChart: function () {
      var _updateAOVChart = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee4(data) {
        var _data$formattedDate3, startDate, endDate, response, _t4;
        return _regenerator().w(function (_context4) {
          while (1) switch (_context4.p = _context4.n) {
            case 0:
              _data$formattedDate3 = _slicedToArray(data.formattedDate, 2), startDate = _data$formattedDate3[0], endDate = _data$formattedDate3[1];
              _context4.p = 1;
              _context4.n = 2;
              return this.sendAjaxRequest(this.prepareAjaxData('merchant_get_avg_order_value_chart_data', startDate, endDate), '.aov-chart-section .merchant-analytics-loading-spinner');
            case 2:
              response = _context4.v;
              if (response.success) {
                this.avgOrderValChart.updateSeries([{
                  data: response.data
                }]);
              }
              _context4.n = 4;
              break;
            case 3:
              _context4.p = 3;
              _t4 = _context4.v;
              console.error('Error fetching AOV data:', _t4);
            case 4:
              return _context4.a(2);
          }
        }, _callee4, this, [[1, 3]]);
      }));
      function updateAOVChart(_x4) {
        return _updateAOVChart.apply(this, arguments);
      }
      return updateAOVChart;
    }(),
    /**
     * Updates the overview cards with new data.
     * @param {Object} dates - The selected date ranges.
     * @returns {Promise<void>}
     */
    updateOverviewCards: function () {
      var _updateOverviewCards = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee5(dates) {
        var response, _t5;
        return _regenerator().w(function (_context5) {
          while (1) switch (_context5.p = _context5.n) {
            case 0:
              _context5.p = 0;
              _context5.n = 1;
              return this.sendAjaxRequest(this.prepareAjaxData($('.merchant-analytics-overview-section').attr('data-action'), dates.startDate, dates.endDate, dates.compareStartDate, dates.compareEndDate), '.merchant-analytics-overview-section .merchant-analytics-loading-spinner');
            case 1:
              response = _context5.v;
              if (response.success) {
                // Update the cards with the new data
                this.updateCardsWithData(response.data);
              }
              _context5.n = 3;
              break;
            case 2:
              _context5.p = 2;
              _t5 = _context5.v;
              console.error('Error fetching cards data:', _t5);
            case 3:
              return _context5.a(2);
          }
        }, _callee5, this, [[0, 2]]);
      }));
      function updateOverviewCards(_x5) {
        return _updateOverviewCards.apply(this, arguments);
      }
      return updateOverviewCards;
    }(),
    /**
     * Updates the overview cards with new data.
     * @param {Object} data - The data to update the cards with.
     */
    updateCardsWithData: function updateCardsWithData(data) {
      var container = $('.merchant-analytics-overview-section');
      var cards = container.find('.overview-cards');
      cards.html(data);
    },
    /**
     * Updates the performing campaigns table with new data.
     *
     * @param {Object} dates - The selected date ranges.
     *
     * @returns {Promise<void>}
     */
    updatePerformingCampaignsTable: function () {
      var _updatePerformingCampaignsTable = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee6(dates) {
        var response, _t6;
        return _regenerator().w(function (_context6) {
          while (1) switch (_context6.p = _context6.n) {
            case 0:
              _context6.p = 0;
              _context6.n = 1;
              return this.sendAjaxRequest(this.prepareAjaxData('merchant_get_top_performing_campaigns_table_data', dates.startDate, dates.endDate, '', ''), '.merchant-analytics-overview-section .merchant-analytics-loading-spinner');
            case 1:
              response = _context6.v;
              if (response.success) {
                // Update the cards with the new data
                this.updateTopCampaignsWithData(response.data, dates.container);
              }
              _context6.n = 3;
              break;
            case 2:
              _context6.p = 2;
              _t6 = _context6.v;
              console.error('Error fetching cards data:', _t6);
            case 3:
              return _context6.a(2);
          }
        }, _callee6, this, [[0, 2]]);
      }));
      function updatePerformingCampaignsTable(_x6) {
        return _updatePerformingCampaignsTable.apply(this, arguments);
      }
      return updatePerformingCampaignsTable;
    }(),
    /**
     * Updates the all campaigns table with new data.
     * @param dates - The selected date ranges.
     * @returns {Promise<void>} - The resolved promise.
     */
    updateAllCampaignsTable: function () {
      var _updateAllCampaignsTable = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee7(dates) {
        var response, _t7;
        return _regenerator().w(function (_context7) {
          while (1) switch (_context7.p = _context7.n) {
            case 0:
              _context7.p = 0;
              _context7.n = 1;
              return this.sendAjaxRequest(this.prepareAjaxData('merchant_get_all_campaigns_table_data', dates.startDate, dates.endDate, '', ''), '.merchant-page-campaigns .merchant-analytics-loading-spinner');
            case 1:
              response = _context7.v;
              if (response.success) {
                // Update the cards with the new data
                this.updateAllCampaignsWithData(response.data, dates.container);
                this.populateFilterSelect(dates.container);
              }
              _context7.n = 3;
              break;
            case 2:
              _context7.p = 2;
              _t7 = _context7.v;
              console.error('Error fetching cards data:', _t7);
            case 3:
              return _context7.a(2);
          }
        }, _callee7, this, [[0, 2]]);
      }));
      function updateAllCampaignsTable(_x7) {
        return _updateAllCampaignsTable.apply(this, arguments);
      }
      return updateAllCampaignsTable;
    }(),
    /**
     * Updates the top campaigns table with new data.
     * @param data
     * @param container
     */
    updateTopCampaignsWithData: function updateTopCampaignsWithData(data, container) {
      var table_body = container.find('tbody');
      container.find('table th').removeClass('asc desc');
      table_body.empty();
      $.each(data, function (campaignId, campaign) {
        // Create the HTML for each row using template literals
        var rowHTML = "\n\t\t            <tr>\n\t\t                <td>".concat(campaign.campaign_info.module_name, ": ").concat(campaign.campaign_info.campaign_title, "</td>\n\t\t                <td>").concat(campaign.impressions, "</td>\n\t\t                <td>").concat(campaign.clicks, "</td>\n\t\t                <td class=\"ctr\">").concat(campaign.ctr, "</td>\n\t\t                <td>").concat(campaign.orders, "</td>\n\t\t                <td>").concat(campaign.revenue, "</td>\n\t\t            </tr>\n\t\t        ");

        // Append the row HTML to the container
        $(table_body).append(rowHTML);
      });
    },
    /**
     * Updates all campaigns table with new data.
     * @param data - The data to update the table with.
     * @param container - The container element for the table.
     */
    updateAllCampaignsWithData: function updateAllCampaignsWithData(data, container) {
      var self = this;
      var rowsHTML = [];
      var table_body = container.find('tbody');
      table_body.empty();
      container.find('table th').removeClass('asc desc');
      container.find('.js-campaign-search').val('');
      container.find('.no-results-message').hide();
      var count = 0;
      var $pagination = container.find('.js-pagination');
      var rowsPerPage = parseInt($pagination.attr('data-rows-per-page'));
      $.each(data, function (moduleIndex, module_object) {
        // Extract module and campaign info
        var moduleId = module_object.module_id;
        // check if module_object.campaigns is not empty
        if (module_object.campaigns.length > 0) {
          // Loop through each campaign
          module_object.campaigns.forEach(function (campaign, index) {
            var _campaign$revenue;
            count++;
            var switcherId = "".concat(moduleId, "-campaign-").concat(moduleIndex, "-").concat(index);
            rowsHTML.push("\n\t\t\t\t            <tr\n\t\t\t\t            \tclass=\"".concat(count > rowsPerPage ? 'is-hidden' : '', "\"\n\t\t\t\t            \t").concat(count > rowsPerPage ? 'style="display: none;"' : '', "\n\t\t\t\t                data-module-id=\"").concat(moduleId, "\"\n\t\t\t\t                data-campaign-key=\"").concat(campaign.campaign_key, "\"\n\t\t\t\t                data-campaign-id=\"").concat(campaign.campaign_id, "\"\n\t\t\t\t                data-row-count=\"").concat(count, "\">\n\t\t\t\t                <td><input type=\"checkbox\" name=\"campaign_select[]\" value=\"").concat(campaign.title, "\" /></td>\n\t\t\t\t                <td class=\"merchant__campaign-name js-campaign-name\">").concat(campaign.title, "</td>\n\t\t\t\t                <td class=\"merchant__module-name js-module-name\" data-module-id=\"").concat(module_object.module_id, "\">").concat(module_object.module_name, "</td>\n\t\t\t\t                <td class=\"merchant__status merchant-module-page-setting-field-switcher js-status\">\n\t\t\t\t                    ").concat(campaign.status === 'active' || campaign.status === 'inactive' ? "<div class=\"merchant-toggle-switch\">\n\t\t\t\t\t\t\t\t                <input type=\"checkbox\" id=\"".concat(switcherId, "\" name=\"merchant[").concat(switcherId, "]\" value=\"").concat(campaign.status === 'active' ? '1' : '', "\" ").concat(campaign.status === 'active' ? 'checked ' : '', "class=\"toggle-switch-checkbox\">\n\t\t\t\t\t\t\t\t                <label class=\"toggle-switch-label\" for=\"").concat(switcherId, "\">\n\t\t\t\t\t\t\t\t                    <span class=\"toggle-switch-inner\"></span>\n\t\t\t\t\t\t\t\t                    <span class=\"toggle-switch-switch\"></span>\n\t\t\t\t\t\t\t\t                </label>\n\t\t\t\t\t\t\t\t\t\t\t</div>") : '-', "\n\t\t\t\t                </td>\n\t\t\t\t                <td class=\"merchant__impressions\">").concat(campaign.impression, "</td>\n\t\t\t\t                <td class=\"merchant__clicks\">").concat(campaign.clicks, "</td>\n\t\t\t\t                <td class=\"merchant__revenue\">").concat((_campaign$revenue = campaign.revenue) !== null && _campaign$revenue !== void 0 ? _campaign$revenue : '-', "</td>\n\t\t\t\t                <td class=\"merchant__ctr\">").concat(campaign.ctr, "</td>\n\t\t\t\t                <td class=\"merchant__orders\">").concat(campaign.orders, "</td>\n\t\t\t\t                <td class=\"merchant__edit\">\n\t\t\t\t                    <a href=\"").concat(module_object.edit_url || '#', "\" target=\"_blank\">\n\t\t\t\t                        <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"12\" height=\"12\" viewBox=\"0 0 12 12\" fill=\"none\">\n\t\t\t\t                            <path d=\"M8.30399 1.00174C8.90067 0.405063 9.8596 0.405063 10.4563 1.00174L10.7333 1.27876C11.33 1.87543 11.33 2.83437 10.7333 3.43104L6.51398 7.65037C6.3435 7.82085 6.10909 7.97001 5.85338 8.03394L3.7224 8.65192C3.55193 8.69454 3.36014 8.65192 3.23228 8.50276C3.08311 8.3749 3.04049 8.18311 3.08311 8.01263L3.70109 5.88166C3.76502 5.62594 3.91419 5.39154 4.08467 5.22106L8.30399 1.00174ZM9.73175 1.72627C9.53996 1.53448 9.22031 1.53448 9.02852 1.72627L8.38923 2.34425L9.39079 3.3458L10.0088 2.70651C10.2006 2.51473 10.2006 2.19508 10.0088 2.00329L9.73175 1.72627ZM4.68134 6.15869L4.31908 7.41596L5.57635 7.0537C5.66159 7.03239 5.72552 6.98977 5.78945 6.92584L8.66626 4.04903L7.68601 3.06878L4.8092 5.94559C4.74527 6.00952 4.70265 6.07345 4.68134 6.15869ZM4.61741 1.83281C4.89444 1.83281 5.12885 2.06722 5.12885 2.34425C5.12885 2.64258 4.89444 2.85568 4.61741 2.85568H2.23072C1.7406 2.85568 1.37834 3.23926 1.37834 3.70807V9.50431C1.37834 9.99444 1.7406 10.3567 2.23072 10.3567H8.02697C8.49578 10.3567 8.87936 9.99444 8.87936 9.50431V7.11763C8.87936 6.8406 9.09245 6.60619 9.39079 6.60619C9.66782 6.60619 9.90222 6.8406 9.90222 7.11763V9.50431C9.90222 10.5485 9.04983 11.3796 8.02697 11.3796H2.23072C1.18655 11.3796 0.355469 10.5485 0.355469 9.50431V3.70807C0.355469 2.6852 1.18655 1.83281 2.23072 1.83281H4.61741Z\" fill=\"#565865\"/>\n\t\t\t\t                        </svg>\n\t\t\t\t                        Edit\n\t\t\t\t                    </a>\n\t\t\t\t                </td>\n\t\t\t\t            </tr>\n\t\t\t\t        "));
          });
        }
      });
      $(table_body).append(rowsHTML.join(''));

      // Reset pagination initial state
      self.updatePaginationButtons(1, parseInt($pagination.attr('data-total-pages-initial')), parseInt($pagination.attr('data-total-rows-initial')));
    },
    /**
     * Initializes the date picker for a chart container.
     * @param {jQuery} container - The container element for the chart.
     * @param {Object} options - Options for the date picker.
     * @param {Function} options.onSelectHandler - Callback function for date selection.
     * @param {Object} options.datePickerArgs - Additional arguments for the date picker.
     */
    datePickerInit: function datePickerInit(container, _ref3) {
      var onSelectHandler = _ref3.onSelectHandler,
        datePickerArgs = _ref3.datePickerArgs;
      var inputs = container.find('.date-range-input');
      if (!inputs.length) {
        return;
      }
      inputs.each(function () {
        var datePicker = $(this);
        var initialValue = datePicker.val();
        var selectedDates = [];
        if (initialValue) {
          selectedDates = initialValue.split(' - ').map(function (dateStr) {
            return new Date(dateStr.trim());
          });
        }
        var dpArgs = _objectSpread(_objectSpread({}, {
          maxDate: new Date(),
          locale: JSON.parse(merchant_datepicker_locale),
          range: true,
          position: 'bottom right',
          dateFormat: 'MM/dd/yy',
          selectedDates: selectedDates,
          // Set the selected dates
          multipleDatesSeparator: ' - ',
          onSelect: function onSelect(data) {
            if (typeof onSelectHandler === 'function') {
              onSelectHandler(data);
            }
          }
        }), datePickerArgs);
        new AirDatepicker(datePicker.get(0), dpArgs);
      });
    },
    /**
     * Initializes the overview cards.
     */
    initOverviewCards: function initOverviewCards() {
      var _this = this;
      var container = $('.merchant-analytics-overview-section');

      // Initialize the date picker
      this.datePickerInit(container, {
        onSelectHandler: function onSelectHandler() {
          // Get both date range inputs
          var firstInput = container.find('.first-date-range .date-range-input');
          var secondInput = container.find('.second-date-range .date-range-input');
          var firstDateRange = firstInput.val().split(' - ').map(function (dateStr) {
            return dateStr.trim();
          });
          var secondDateRange = secondInput.val().split(' - ').map(function (dateStr) {
            return dateStr.trim();
          });

          // Ensure both date ranges have exactly two dates
          if (firstDateRange.length === 2 && secondDateRange.length === 2) {
            _this.updateOverviewCards({
              startDate: firstDateRange[0],
              endDate: firstDateRange[1],
              compareStartDate: secondDateRange[0],
              compareEndDate: secondDateRange[1]
            });
          }
        }
      });
    },
    /**
     * Renders a chart and initializes its date picker.
     * @param {jQuery} container - The container element for the chart.
     * @param {Object} chartOptions - Options for the ApexCharts instance.
     * @param {Function} updateFunction - Function to call when the date is selected.
     * @param {string} loadingIndicatorSelector - Selector for the loading indicator.
     * @param datePickerArgs
     * @returns {ApexCharts} - The rendered chart instance.
     */
    renderChart: function renderChart(container, chartOptions, updateFunction, loadingIndicatorSelector) {
      var datePickerArgs = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : {};
      var chartEl = container.find('.chart');
      if (!chartEl.length) {
        return;
      }
      var chart = new ApexCharts(chartEl.get(0), chartOptions);
      chart.render();
      chart.updateSeries([{
        data: JSON.parse(chartEl.attr('data-period'))
      }]);
      this.datePickerInit(container, {
        onSelectHandler: function onSelectHandler(data) {
          if (data.formattedDate.length === 2) {
            updateFunction(data);
          }
        },
        datePickerArgs: datePickerArgs
      });
      return chart;
    },
    /**
     * Renders the revenue chart.
     */
    widgetChartRender: function widgetChartRender() {
      var _this2 = this;
      var container = $('.widget-chart-section');
      var isRTL = $('body').hasClass('rtl');
      merchantAnalyticsChart.revenueChart = this.renderChart(container, this.widgetChartOptions, function (data) {
        return _this2.updateRevenueChart(data);
      }, '.widget-chart-section .merchant-analytics-loading-spinner', {
        position: isRTL ? 'top right' : 'top left'
      });
    },
    /**
     * Renders the revenue chart.
     */
    revenueChartRender: function revenueChartRender() {
      var _this3 = this;
      var container = $('.revenue-chart-section');
      if (container.length) {
        this.revenueChart = this.renderChart(container, this.revenueChartOptions, function (data) {
          return _this3.updateRevenueChart(data);
        }, '.revenue-chart-section .merchant-analytics-loading-spinner');
      }
    },
    /**
     * Renders the average order value (AOV) chart.
     */
    avgOrderValChartRender: function avgOrderValChartRender() {
      var _this4 = this;
      var container = $('.aov-chart-section');
      if (container.length) {
        this.avgOrderValChart = this.renderChart(container, this.avgOrderValChartOptions, function (data) {
          return _this4.updateAOVChart(data);
        }, '.aov-chart-section .merchant-analytics-loading-spinner');
      }
    },
    /**
     * Renders the impressions chart.
     */
    impressionsChartRender: function impressionsChartRender() {
      var _this5 = this;
      var container = $('.impressions-chart-section');
      if (container.length) {
        this.impressionsChart = this.renderChart(container, this.columnChartOptions, function (data) {
          return _this5.updateImpressionsChart(data);
        }, '.impressions-chart-section .merchant-analytics-loading-spinner');
      }
    },
    /**
     * Initializes the top campaigns table.
     */
    initTopCampaignsTable: function initTopCampaignsTable() {
      var container = $('.merchant-analytics-section.campaigns-table');
      var self = this;
      if (container.length) {
        // Initialize the date picker
        this.datePickerInit(container, {
          onSelectHandler: function onSelectHandler() {
            // Get both date range inputs
            var firstInput = container.find('.first-date-range .date-range-input');
            var firstDateRange = firstInput.val().split(' - ').map(function (dateStr) {
              return dateStr.trim();
            });

            // Ensure both date ranges have exactly two dates
            if (firstDateRange.length === 2) {
              self.updatePerformingCampaignsTable({
                startDate: firstDateRange[0],
                endDate: firstDateRange[1],
                container: container
              });
            }
          },
          datePickerArgs: {
            position: 'top right'
          }
        });
        this.setupSortableTableEventListeners(container);
      }
    },
    /**
     * Initializes the all campaigns table.
     */
    initAllCampaignsTable: function initAllCampaignsTable() {
      var container = $('.merchant-analytics-section.all-campaigns-table');
      var self = this;
      if (container.length) {
        // Initialize the date picker
        this.datePickerInit(container, {
          onSelectHandler: function onSelectHandler() {
            // Get both date range inputs
            var firstInput = container.find('.first-date-range .date-range-input');
            var firstDateRange = firstInput.val().split(' - ').map(function (dateStr) {
              return dateStr.trim();
            });

            // Ensure both date ranges have exactly two dates
            if (firstDateRange.length === 2) {
              self.updateAllCampaignsTable({
                startDate: firstDateRange[0],
                endDate: firstDateRange[1],
                container: container
              });
            }
          }
        });
        self.setupSortableTableEventListeners(container);
        self.populateFilterSelect(container);
      }
    },
    /**
     * Filters the table based on the the available module in the table
     *
     * @param container - The table container element.
     */
    populateFilterSelect: function populateFilterSelect(container) {
      var table = container.find('.js-campaigns-table');
      var selectorField = $('.filter-campaign select');
      // Clear all options except the first one
      $(selectorField).find('option:not(:first)').remove();

      // Get unique values from the specified table column
      var values = [];
      $(table).find('tr .js-module-name').each(function () {
        var value = $(this).attr('data-module-id');
        var label = $(this).text().trim();

        // Check if the value is not already in the values array
        if (value && !values.some(function (item) {
          return item.value === value;
        })) {
          values.push({
            value: value,
            label: label
          });
        }
      });

      // Sort the values alphabetically (optional)
      values.sort();

      // Append new options to the select field
      $.each(values, function (index, item) {
        $(selectorField).append($('<option>', {
          value: item.value,
          text: item.label
        }));
      });
    },
    /**
     * Add event listeners to the sortable table.
     * @param container
     */
    setupSortableTableEventListeners: function setupSortableTableEventListeners(container) {
      var self = this;
      container.find('th:not(.no-sort)').on('click', function (event) {
        self.sortableTable($(event.currentTarget), container);
      });
      var table = $('.js-campaigns-table');
      var searchInput = $('.js-campaign-search');
      var filterSelect = $('.js-filter-module');
      var bulkActionBtn = $('.js-bulk-action');
      var $pagination = $('.js-pagination');

      // "Select All" checkbox
      table.find('thead th:first-child input[type="checkbox"]').on('change', function () {
        var isChecked = $(this).prop('checked');
        table.find('tbody tr:not(.is-hidden) input[type="checkbox"]:not(.toggle-switch-checkbox)').prop('checked', isChecked);
      });

      // Status - Single row
      table.on('change', '.js-status input[type="checkbox"]', function () {
        var checkbox = $(this);
        var row = checkbox.closest('tr');
        var moduleId = row.attr('data-module-id');
        var campaignData = _defineProperty({}, moduleId, {
          campaign_key: row.attr('data-campaign-key'),
          campaigns: [{
            campaign_id: row.attr('data-campaign-id'),
            status: checkbox.prop('checked') ? 'active' : 'inactive'
          }]
        });
        self.updateCampaignStatus(campaignData, checkbox, [checkbox], true);
      });

      // Status - Bulk action
      bulkActionBtn.on('click', function (e) {
        e.preventDefault();
        var $select = $(this).closest('.bulk-action').find('select');
        var statusAction = $select.val();
        if (!statusAction) {
          alert('Please select an action.');
          return;
        }
        var $checkboxes = table.find('tbody tr:not(.is-hidden) input[type="checkbox"]:not(.toggle-switch-checkbox):checked');
        if (!$checkboxes.length) {
          alert('Please select campaigns.');
          return;
        }
        var campaignData = {};
        $checkboxes.each(function () {
          var $row = $(this).closest('tr');
          var moduleId = $row.attr('data-module-id');
          if (!campaignData[moduleId]) {
            campaignData[moduleId] = {
              campaign_key: $row.attr('data-campaign-key'),
              campaigns: []
            };
          }
          campaignData[moduleId].campaigns.push({
            campaign_id: $row.attr('data-campaign-id'),
            status: statusAction
          });
        });
        self.updateCampaignStatus(campaignData, $(this), $checkboxes);
      });

      // Search input
      searchInput.on('input', self.debounce(function () {
        self.filterTableTable(filterSelect.val(), table, $(this).val());
      }, 300));

      // Module filter
      filterSelect.on('change', function () {
        self.filterTableTable($(this).val(), table, '');
        searchInput.val('');
      });

      // Pagination clicks
      $pagination.on('click', '.pagination-button', function (e) {
        e.preventDefault();
        var currentPage = parseInt($(this).attr('data-current-page'));
        var nextPage = parseInt($(this).attr('data-page'));
        if (isNaN(nextPage) || nextPage === currentPage) {
          return;
        }
        currentPage = nextPage;
        self.paginateRows(currentPage, table.find('tbody tr'));
        self.updatePaginationButtons(currentPage);
      });
    },
    /**
     * Make the table sortable by the selected column.
     * @param header - The header element that was clicked.
     * @param container - The table container element.
     */
    sortableTable: function sortableTable(header, container) {
      var self = this;
      var column = header.index();
      var type = header.data('sort');
      var currentOrder = header.hasClass('asc') ? 'desc' : 'asc';

      // Remove previous sorting classes
      container.find('th').removeClass('asc desc');

      // Add class to indicate current sorting order
      header.addClass(currentOrder);
      var tbody = container.find('tbody');
      var rows = tbody.find('tr').toArray();
      rows.sort(function (a, b) {
        var keyA = $(a).find('td').eq(column).text();
        var keyB = $(b).find('td').eq(column).text();
        var valueA, valueB;
        if (type === 'int') {
          valueA = parseInt(keyA.replace(/[^0-9]/g, ''), 10);
          valueB = parseInt(keyB.replace(/[^0-9]/g, ''), 10);
        } else if (type === 'float') {
          valueA = parseFloat(keyA.replace(/[^0-9.]/g, ''));
          valueB = parseFloat(keyB.replace(/[^0-9.]/g, ''));
        } else {
          valueA = keyA;
          valueB = keyB;
        }
        if (currentOrder === 'asc') {
          return valueA < valueB ? -1 : valueA > valueB ? 1 : 0;
        } else {
          return valueA > valueB ? -1 : valueA < valueB ? 1 : 0;
        }
      });

      // Append sorted rows back to the tbody
      tbody.append(rows);
    },
    /**
     * Filter the table rows based on the selected module and search term.
     * @param moduleId
     * @param $table
     * @param searchTerm
     */
    filterTableTable: function filterTableTable(moduleId, $table) {
      var searchTerm = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
      if (!$table.length) {
        return;
      }
      var self = this;
      var visibleCount = 0;
      var $rows = $table.find('tbody tr');
      $rows.each(function () {
        var $row = $(this);
        var rowModuleId = $row.attr('data-module-id');
        var campaignName = $row.find('.js-campaign-name').text().toLowerCase();
        var moduleName = $row.find('.js-module-name').text().toLowerCase();
        var moduleMatch = !moduleId || rowModuleId === moduleId;
        var searchMatch = !searchTerm || campaignName.includes(searchTerm) || moduleName.includes(searchTerm);
        if (moduleMatch && searchMatch) {
          $row.show().removeClass('filtered-out is-hidden');
          visibleCount++;
        } else {
          $row.hide().addClass('filtered-out');
        }
      });
      var currentPage = 1;
      var totalRows = visibleCount;
      var rowsPerPage = parseInt($table.closest('.merchant-page-campaigns').find('.js-pagination').attr('data-rows-per-page'));
      var totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));

      // Update after filtering
      self.paginateRows(currentPage, $rows);
      self.updateNoResults(visibleCount === 0, $table);
      self.updatePaginationButtons(currentPage, totalPages, totalRows);
    },
    /**
     * Update the table to show rows for the selected page.
     *
     * @param currentPage
     * @param $rows
     */
    paginateRows: function paginateRows() {
      var currentPage = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 1;
      var $rows = arguments.length > 1 ? arguments[1] : undefined;
      var $pagination = $('.js-pagination');
      var rowsPerPage = parseInt($pagination.attr('data-rows-per-page'));
      var startIndex = (currentPage - 1) * rowsPerPage;
      var endIndex = startIndex + rowsPerPage;
      $rows.hide().addClass('is-hidden');
      $rows.filter(':not(.filtered-out)').each(function (index) {
        if (index >= startIndex && index < endIndex) {
          $(this).show().removeClass('is-hidden');
        }
      });
    },
    /**
     * Show no rows found message.
     *
     * @param show
     * @param $table
     */
    updateNoResults: function updateNoResults(show, $table) {
      var $noResults = $table.next('.no-results-message');
      if (show) {
        if (!$noResults.length) {
          $noResults = $('<div class="no-results-message" style="">No matching campaigns found</div>');
          $table.after($noResults);
        }
        $noResults.show();
      } else if ($noResults.length) {
        $noResults.hide();
      }
    },
    /**
     * Update the pagination buttons.
     *
     * @param currentPage
     * @param totalPages
     * @param totalRows
     */
    updatePaginationButtons: function updatePaginationButtons(currentPage, totalPages, totalRows) {
      var $pagination = $('.js-pagination');
      $pagination.attr('data-current-page', currentPage);

      // If totalPages provided, update it to use the latest value
      if (totalPages) {
        $pagination.attr('data-total-pages', totalPages);
      }
      if (totalRows) {
        $pagination.attr('data-total-rows', totalRows);
      }

      // Get the latest value
      totalPages = parseInt($pagination.attr('data-total-pages'));
      var html = '';
      if (currentPage > 1) {
        html += "\n\t\t          <button class=\"pagination-button prev-page\" data-page=\"".concat(currentPage - 1, "\">\n\t\t            <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"7\" height=\"12\" viewBox=\"0 0 7 12\" fill=\"#565865\">\n\t\t              <path d=\"M5.16797 11.3301L0.521484 6.48047C0.394531 6.32812 0.34375 6.17578 0.34375 6.02344C0.34375 5.89648 0.394531 5.74414 0.496094 5.61719L5.14258 0.767578C5.37109 0.513672 5.77734 0.513672 6.00586 0.742188C6.25977 0.970703 6.25977 1.35156 6.03125 1.60547L1.79102 6.02344L6.05664 10.4922C6.28516 10.7207 6.28516 11.127 6.03125 11.3555C5.80273 11.584 5.39648 11.584 5.16797 11.3301Z\"/>\n\t\t            </svg>\n\t\t          </button>\n\t\t\t\t");
      }
      if (totalPages > 1) {
        for (var i = 1; i <= totalPages; i++) {
          html += "\n          \t\t\t<button class=\"pagination-button".concat(i === currentPage ? ' pagination-active' : '', "\" data-page=\"").concat(i, "\">").concat(i, "</button>\n\t\t\t\t");
        }
      }
      if (currentPage < totalPages) {
        html += "\n\t\t          <button class=\"pagination-button next-page\" data-page=\"".concat(currentPage + 1, "\">\n\t\t            <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"7\" height=\"12\" viewBox=\"0 0 7 12\" fill=\"#565865\">\n\t\t              <path d=\"M1.80664 0.742188L6.45312 5.5918C6.55469 5.71875 6.63086 5.87109 6.63086 6.02344C6.63086 6.17578 6.55469 6.32812 6.45312 6.42969L1.80664 11.2793C1.57812 11.5332 1.17188 11.5332 0.943359 11.3047C0.689453 11.0762 0.689453 10.6953 0.917969 10.4414L5.18359 5.99805L0.917969 1.58008C0.689453 1.35156 0.689453 0.945312 0.943359 0.716797C1.17188 0.488281 1.57812 0.488281 1.80664 0.742188Z\"/>\n\t\t            </svg>\n\t\t          </button>\n\t\t\t\t");
      }
      $pagination.html(html);

      // Update results
      var $paginationNotice = $('.js-pagination-results');
      if (totalPages > 1) {
        var _totalRows = parseInt($pagination.attr('data-total-rows'));
        var rowsPerPage = parseInt($pagination.attr('data-rows-per-page'));
        var startIndex = (currentPage - 1) * rowsPerPage;
        var endIndex = startIndex + rowsPerPage;
        $paginationNotice.find('.pagination-start-row').text(startIndex ? startIndex : 1);
        $paginationNotice.find('.pagination-end-row').text(endIndex > _totalRows ? _totalRows : endIndex);
        $paginationNotice.find('.pagination-total-rows').text(totalRows);
        $paginationNotice.show();
      } else {
        $paginationNotice.hide();
      }
    },
    /**
     * Update the campaign status.
     * @param campaignData - The campaign data to update.
     * @param el - The element that triggered the update.
     * @param checkboxes - The checkboxes to update.
     * @param singleRow - Whether to update a single row or multiple rows.
     */
    updateCampaignStatus: function () {
      var _updateCampaignStatus = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee8(campaignData, el, checkboxes) {
        var singleRow,
          self,
          $table,
          $loader,
          _args8 = arguments,
          _t8;
        return _regenerator().w(function (_context8) {
          while (1) switch (_context8.p = _context8.n) {
            case 0:
              singleRow = _args8.length > 3 && _args8[3] !== undefined ? _args8[3] : false;
              self = this;
              $table = el.closest('.all-campaigns-table').find('.js-campaigns-table'); // $ sign is used to indicate that it is a jQuery object
              $loader = '<span class="spinner is-active"></span>';
              el.prop('disabled', true);
              if (singleRow) {
                el.closest('.merchant-toggle-switch').append($loader);
                el.closest('tr').css('opacity', '.7');
              } else {
                $table.css('opacity', '.7');
                el.closest('.bulk-action').append($loader);
              }
              _context8.p = 1;
              _context8.n = 2;
              return this.sendAjaxRequest({
                action: 'merchant_update_campaign_status',
                nonce: self.NONCE,
                campaign_data: campaignData
              }, '', 'POST').then(function (response) {
                if (response.success) {
                  if (!singleRow) {
                    // Change the toggle of the selected campaigns
                    checkboxes === null || checkboxes === void 0 || checkboxes.each(function () {
                      $(this).closest('tr').find('.js-status input[type="checkbox"]').prop('checked', response.data.status === 'active');
                    });
                  }
                  $(document).trigger('merchant_campaign_status_updated', [response.data, el, checkboxes, singleRow, campaignData]);
                }
                $('.spinner').remove();
                el.prop('disabled', false);

                // Uncheck checkboxes & remove opacity
                if (singleRow) {
                  el.closest('tr').css('opacity', '');
                } else {
                  checkboxes === null || checkboxes === void 0 || checkboxes.each(function () {
                    $table.find('thead th:first-child input[type="checkbox"]').prop('checked', false);
                    $(this).prop('checked', false);
                  });
                  $table.css('opacity', '');
                }
              });
            case 2:
              _context8.n = 4;
              break;
            case 3:
              _context8.p = 3;
              _t8 = _context8.v;
              console.error('Error fetching campaign status data:', _t8);
            case 4:
              return _context8.a(2);
          }
        }, _callee8, this, [[1, 3]]);
      }));
      function updateCampaignStatus(_x8, _x9, _x0) {
        return _updateCampaignStatus.apply(this, arguments);
      }
      return updateCampaignStatus;
    }(),
    /**
     * Debounce function to limit the number of times a function is called.
     * @param func - The function to debounce.
     * @param wait - The time to wait before calling the function.
     * @returns {(function(...[*]): void)|*} - The debounced function.
     */
    debounce: function debounce(func, wait) {
      var timeout;
      return function () {
        var _this6 = this;
        for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
          args[_key] = arguments[_key];
        }
        clearTimeout(timeout);
        timeout = setTimeout(function () {
          return func.apply(_this6, args);
        }, wait);
      };
    }
  };

  // Initialize charts when the document is ready
  $(document).ready(function () {
    merchantAnalyticsChart.initOverviewCards();
    merchantAnalyticsChart.widgetChartRender();
    merchantAnalyticsChart.revenueChartRender();
    merchantAnalyticsChart.avgOrderValChartRender();
    merchantAnalyticsChart.impressionsChartRender();
    merchantAnalyticsChart.initTopCampaignsTable();
    merchantAnalyticsChart.initAllCampaignsTable();
  });
})(jQuery);