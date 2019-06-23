webpackJsonp([23], {
    2: function(t, n, e) {
        "use strict";
        var i = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function(t) {
                return typeof t
            } : function(t) {
                return t && "function" == typeof Symbol && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t
            },
            s = {
                parseException: function(t) {
                    var n = void 0,
                        e = void 0,
                        i = void 0;
                    try {
                        i = t.response, e = i.statusText, n = i.status, e = JSON.parse(e)
                    } catch(t) {
                        n = 500
                    }
                    var s = "";
                    return s = n >= 500 ? "服务器暂时无法响应您的请求，请稍后再试" : 401 === n ? e.message : 402 === n ? "您无权限访问该页面" : 403 === n ? e.message : 404 === n ? "没有找到该链接" : "服务器无法连接，请稍后再试", {
                        message: s,
                        status: n
                    }
                },
                generateRandomMixed: function(t) {
                    for(var n = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"], e = "", i = 0; i < t; i++) {
                        e += n[this.generateRandomNum(35)]
                    }
                    return e
                },
                generateRandomNum: function(t) {
                    return Math.ceil(Math.random() * t)
                },
                rc4Cipher: function(t, n) {
                    return this.cipher("rc4", t, n)
                },
                rc4Decipher: function(t, n) {
                    return this.decipher("rc4", t, n)
                },
                cipher: function(t, n, e) {
                    var i = "",
                        s = crypto.createCipher(t, n);
                    return i += s.update(e, "binary", "hex"), i += s.final("hex")
                },
                decipher: function(t, n, e) {
                    var i = "",
                        s = crypto.createDecipher(t, n);
                    return i += s.update(e, "hex", "binary"), i += s.final("binary")
                },
                mnemonicRandomInputs: function(t, n) {
                    for(var e = [], i = [], s = 0; s < t.length; s++) {
                        var a = Math.ceil(Math.random() * (t.length - 1));
                        e.push(a)
                    }
                    for(var o = 0; o < e.length; o++) - 1 === i.indexOf(e[o]) && i.push(e[o]);
                    return i.splice(0, 3)
                },
                getCurrentDomain: function() {
                    return window.location.protocol + "//" + window.location.host
                },
                commentView: function() {
                    setTimeout(function() {
                        document.querySelector(".publish_com").style.bottom = "20px"
                    }, 200)
                },
                commentViewCancel: function() {
                    document.querySelector(".publish_com").style.bottom = "0px"
                },
                commentShow: function() {
                    document.querySelector(".publish_com").onfocus = function() {
                        setTimeout(function() {
                            document.body.scrollTop ? document.body.scrollTop = document.body.scrollHeight : document.documentElement.scrollTop = document.documentElement.scrollHeight
                        }, 500)
                    }
                },
                isIOS: function() {
                    var t = window.navigator.userAgent;
                    return t.toLowerCase().indexOf("iphone") >= 0 || t.toLowerCase().indexOf("ipad") >= 0
                },
                chunk: function(t) {
                    for(var n = [], e = 0, i = t.length; e < i; e += 4) n.push(t.slice(e, e + 4));
                    return n
                },
                compare: function(t) {
                    return function(n, e) {
                        var i = n[t];
                        return e[t] - i
                    }
                },
                compareSmall: function(t) {
                    return function(n, e) {
                        return n[t] - e[t]
                    }
                },
                coppyArray: function(t) {
                    return t.map(function(t) {
                        return "object" === (void 0 === t ? "undefined" : i(t)) ? Object.assign({}, t) : t
                    })
                },
                getUrlQueryString: function(t) {
                    var n = new RegExp("(^|&)" + t + "=([^&]*)(&|$)"),
                        e = decodeURI(window.location.search.substr(1)).match(n);
                    return null != e ? unescape(e[2]) : null
                },
                toLogin: function() {
                    window.open("", "_self").location.href = "/login"
                },
                isWechat: function() {
                    return /micromessenger/i.test(navigator.userAgent)
                },
                getCurrentDate: function() {
                    var t = new Date,
                        n = t.getDate(),
                        e = t.getMonth() + 1,
                        i = t.getYear();
                    return n < 10 && (n = "0" + n), i + 1900 + "-" + e + "-" + n
                },
                AdStatistic: function(t, n, e, i, s) {
                    window._hmt && window._hmt.push(["_trackEvent", t, n, e, i, s])
                },
                countDown: function(t, n) {
                    if(t > 0) var e = setInterval(function() {
                        t--;
                        var i = parseInt(t / 86400),
                            s = parseInt(t % 86400),
                            a = parseInt(s / 3600),
                            o = parseInt(s % 3600),
                            r = parseInt(o / 60),
                            c = parseInt(o % 60);
                        0 === t && clearInterval(e), n({
                            day: i < 10 ? "0" + i : i,
                            hours: a < 10 ? "0" + a : a,
                            min: r < 10 ? "0" + r : r,
                            seconds: c < 10 ? "0" + c : c
                        })
                    }, 1e3)
                }
            };
        n.a = s
    },
    350: function(t, n, e) {
        function i(t) {
            e(751)
        }
        var s = e(0)(e(435), e(860), i, "data-v-41544f38", null);
        t.exports = s.exports
    },
    404: function(t, n, e) {
        "use strict";
        Object.defineProperty(n, "__esModule", {
            value: !0
        });
        var i = e(5),
            s = e(350),
            a = e.n(s),
            o = e(72);
        e.n(o);
        i.a.config.productionTip = !1, new i.a({
            el: "#app",
            data: function() {
                return {
                    isShowMailLoading: !0
                }
            },
            components: {
                "component-app-download": a.a
            },
            mounted: function() {
                this.isShowMailLoading = !1
            }
        })
    },
    435: function(t, n, e) {
        "use strict";
        Object.defineProperty(n, "__esModule", {
            value: !0
        });
        var i = e(51),
            s = (e.n(i), e(2));
        n.default = {
            name: "AppDown",
            data: function() {
                return {
                    pageContent: [{
                        title: "",
                        description: {
                            shortWords: "",
                            summaryWords: ""
                        }
                    }, {
                        title: "",
                        description: {
                            shortWords: "",
                            summaryWords: ""
                        }
                    }, {
                        title: "",
                        description: {
                            shortWords: "",
                            summaryWords: ""
                        }
                    }, {
                        title: "",
                        description: {
                            shortWords: "",
                            summaryWords: ""
                        }
                    }],
                    staticUrl: window.JINSECONFIG.staticDomain,
                    staticVersion: window.JINSECONFIG.staticVersion,
                    swiperOption: {
                        loop: !0,
                        autoplay: !0
                    }
                }
            },
            computed: {
                swiper: function() {
                    return this.$refs.mySwiper.swiper
                }
            },
            components: {
                swiper: i.swiper,
                swiperSlide: i.swiperSlide
            },
            methods: {
                exchangeView: function(t) {
                    this.swiper.slideTo(t, 800, !1)
                },
                isAndroid: function() {
                    return window.navigator.userAgent.indexOf("Android") >= 0
                },
                isIos: function() {
                    return window.navigator.userAgent.indexOf("iPhone") >= 0
                },
                isWX: function() {
                    var t = window.navigator.userAgent.toLowerCase(),
                        n = t.match(/MicroMessenger/i);
                    return !!n && "micromessenger" === n[0]
                },
                isSafari: function() {
                    return /Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent) && !/MQQBrowser/.test(navigator.userAgent)
                },
                downloadApp: function() {
                    var t = location.href,
                        n = void 0,
                        e = void 0,
                        i = t.replace(/.+\./, "").toLowerCase();
                        function ALert(){
                            alert("请用默认浏览器打开")
                        }
                    if(this.isAndroid()) {
                        if(this.isWX()){
                            return ALert();
                        }
                        window.location.href="http://xldjt.oss-cn-hongkong.aliyuncs.com/H574887B0_0301195634.apk";
//
                    }
                    if(this.isIos()) {
                        if(this.isWX()){
                            return ALert();
                        }
                   
                        window.location.href="itms-services://?action=download-manifest&amp;url=https://xldjt.oss-cn-hongkong.aliyuncs.com/info.plist"
                    }
                }
            }
        }
    },
    72: function(t, n) {},      
    751: function(t, n) {},
    860: function(t, n) {
        t.exports = {
            render: function() {
                var t = this,
                    n = t.$createElement,
                    e = t._self._c || n;
                return e("div", {
                    staticClass: "app-down-container",
                    style: {
                        "background-image": "url(https://www.mstz2019.com/static/index/images/bg.png)"
                    }
                }, [e("swiper", {
                    ref: "mySwiper",
                    attrs: {
                        options: t.swiperOption
                    }
                }, [e("swiper-slide", [e("div", {
                    staticClass: "content"
                }, [e("div", {
                    staticClass: "content-title"
                }, [e("span", {
                    staticClass: "slide-aside",
                    on: {
                        click: function(n) {
                            t.exchangeView(4)
                        }
                    }
                }, [t._v("")]), t._v(" "), e("span", {
                    staticClass: "slide-center"
                }, [t._v("")]), t._v(" "), e("span", {
                    staticClass: "slide-aside",
                    on: {
                        click: function(n) {
                            t.exchangeView(2)
                        }
                    }
                }, [t._v("")])]), t._v(" "), e("div", {
                    staticClass: "content-description"
                }, [e("p", {
                    staticClass: "description"
                }, [e("span", {
                    staticClass: "words"
                }, [t._v("")]), t._v(" "), e("span", {
                    staticClass: "words"
                }, [t._v("")])])]), t._v(" "), e("img", {
                    staticClass: "show",
                    attrs: {
                        src: "https://www.mstz2019.com/static/index/images/01.png",
                        alt: ""
                    }
                })])]), t._v(" "), e("swiper-slide", [e("div", {
                    staticClass: "content"
                }, [e("div", {
                    staticClass: "content-title"
                }, [e("span", {
                    staticClass: "slide-aside",
                    on: {
                        click: function(n) {
                            t.exchangeView(1)
                        }
                    }
                }, [t._v("")]), t._v(" "), e("span", {
                    staticClass: "slide-center"
                }, [t._v("")]), t._v(" "), e("span", {
                    staticClass: "slide-aside",
                    on: {
                        click: function(n) {
                            t.exchangeView(3)
                        }
                    }
                }, [t._v("")])]), t._v(" "), e("div", {
                    staticClass: "content-description"
                }, [e("p", {
                    staticClass: "description"
                }, [e("span", {
                    staticClass: "words"
                }, [t._v("")]), t._v(" "), e("span", {
                    staticClass: "words"
                }, [t._v("")])])]), t._v(" "), e("img", {
                    staticClass: "show",
                    attrs: {
                        src: "https://www.mstz2019.com/static/index/images/02.png",
                        alt: ""
                    }
                })])]), t._v(" "), e("swiper-slide", [e("div", {
                    staticClass: "content"
                }, [e("div", {
                    staticClass: "content-title"
                }, [e("span", {
                    staticClass: "slide-aside",
                    on: {
                        click: function(n) {
                            t.exchangeView(2)
                        }
                    }
                }, [t._v("")]), t._v(" "), e("span", {
                    staticClass: "slide-center"
                }, [t._v("")]), t._v(" "), e("span", {
                    staticClass: "slide-aside",
                    on: {
                        click: function(n) {
                            t.exchangeView(4)
                        }
                    }
                }, [t._v("")])]), t._v(" "), e("div", {
                    staticClass: "content-description"
                }, [e("p", {
                    staticClass: "description"
                }, [e("span", {
                    staticClass: "words"
                }, [t._v("")]), t._v(" "), e("span", {
                    staticClass: "words"
                }, [t._v("")])])]), t._v(" "), e("img", {
                    staticClass: "show",
                    attrs: {
                        src: "https://www.mstz2019.com/static/index/images/03.png",
                        alt: ""
                    }
                })])]), t._v(" "), e("div", {
                    staticClass: "swiper-pagination",
                    attrs: {
                        slot: "pagination"
                    },
                    slot: "pagination"
                })], 1), t._v(" "), e("div", {
                    staticClass: "app-down-box"
                }, [t.isAndroid() && !t.isWX() ? e("div", {
                    staticClass: "app-down"
                }, [e("a", {
                    staticClass: "app-down-text",
                    attrs: {
                        href: "http://xldjt.oss-cn-hongkong.aliyuncs.com/H574887B0_0301195634.apk"
                    }
                }, [t._v("立即下载")])]) : e("div", {
                    staticClass: "app-down",
                }, [e("a", {
                    staticClass: "app-down-text",
                    attrs: {
                        href: "javascript:;"
                    },
                    on: {
                        click: t.downloadApp
                    }
                }, [t._v("立即下载")])])])], 1)
            },
            staticRenderFns: []
        }
    }
}, [404]);