/*
 *  AdvancedAJAX 2.0 RC1 (27.08.2006)
 *  (c) 2005-2006 ??ukasz Lach
 *  mail: anakin@php5.pl
 *  www:  http://advajax.anakin.us/
 *        http://anakin.us/
 * Licensed under Creative Commons GNU Lesser General Public License
 * http://creativecommons.org/licenses/LGPL/2.1/
 */
function advAJAX() {
    this.url = window.location.href;
    this.method = 'GET';
    this.parameters = new Object();
    this.headers = new Object();
    this.mimeType = 'Content-Type: application/x-javascript; charset=UTF-8';
    this.username = null;
    this.password = null;

    this.useJSON = false;
    this.unique = true;
    this.uniqueParameter = '_uniqid';

    this.requestDone = false;
    this.requestAborted = false;
    this.requestTimedOut = false;

    this.queryString = '';
    this.responseText = null;
    this.responseXML = null;
    this.responseJSON = null;
    this.status = null;
    this.statusText = null;

    this.timeout = 0;
    this.retryCount = 0;
    this.retryDelay = 1000;
    this.retryNo = 0;

    this.repeat = false;
    this.repeatCount = 0;
    this.repeatNo = 0;
    this.repeatDelay = 1000;

    this.tag = null;
    this.group = null;
    this.form = null;
    this.disableForm = true;

    this.onInitialization = null;
    this.onFinalization = null;
    this.onAbort = null;
    this.onReadyStateChange = null;
    this.onLoading = null;
    this.onLoaded = null;
    this.onInteractive = null;
    this.onComplete = null;
    this.onSuccess = null;
    this.onFatalError = null;
    this.onInternalError = null;
    this.onError = null;
    this.onTimeout = null;
    this.onRetryDelay = null;
    this.onRetry = null;
    this.onRepeat = null;
    this.onRepeatDelay = null;
    this.onGroupEnter = null;
    this.onGroupLeave = null;

    this._xhr = null;
    this._eventHandled = [ false ];

    this._timerTimeout = null;
    this._timerRepeat = null;

    this.init = function() {

        (this._xhr !== null) && this.destroy();
        if ((this._xhr = this._createXHR()) === null)
            return false;
        if (typeof advAJAX._defaultParameters != 'undefined')
            this.handleArguments(advAJAX._defaultParameters);
        if (typeof this._xhr.overrideMimeType == 'function')
            this._xhr.overrideMimeType(this.mimeType);
        this._eventHandled = [ this._eventHandled[0], false, false, false, false ];

        var _this = this;
        this._xhr.onreadystatechange = function() {

            if (_this.requestAborted)
                return;
            _this._raise('ReadyStateChange', _this._xhr.readyState);
            (!_this._eventHandled[_this._xhr.readyState]) && _this._handleReadyState(_this._xhr.readyState);
        };
        return true;
    };

    this.destroy = function() {

        try {
            this._xhr.abort();
            delete this._xhr['onreadystatechange'];
        } catch(e) {
        }
        ;
        this._xhr = null;
    };

    this._createXHR = function() {

        if (typeof XMLHttpRequest != 'undefined')
            return new XMLHttpRequest();
        var xhr = [ 'MSXML2.XMLHttp.5.0', 'MSXML2.XMLHttp.4.0', 'MSXML2.XMLHttp.3.0',
                'MSXML2.XMLHttp', 'Microsoft.XMLHttp' ];
        for (var i = 0; i < xhr.length; i++) {
            try {
                var xhrObj = new ActiveXObject(xhr[i]);
                return xhrObj;
            } catch (e) {
            }
            ;
        }
        this._raise('FatalError');
        return null;
    };

    this._handleReadyState = function(readyState) {

        if (this._eventHandled[readyState])
            return;
        this._eventHandled[readyState] = true;
        switch (readyState) {
        /* loading */
            case 1:
                if (this.retryNo == 0 && this.group !== null) {
                    if (typeof advAJAX._groupData[this.group] == 'undefined') {
                        advAJAX._groupData[this.group] = 0;
                        this._raise('GroupEnter', this.group);
                    }
                    advAJAX._groupData[this.group]++;
                }
                this._raise('Loading', this);
                break;
            /* loaded */
            case 2:
                this._raise('Loaded', this);
                break;
            /* interactive */
            case 3:
                this._raise('Interactive', this);
                break;
            /* complete */
            case 4:
                window.clearTimeout(this._timerTimeout);
                if (this.requestAborted)
                    return;
                this.requestDone = true;
                this.responseText = this._xhr.responseText;
                this.responseXML = this._xhr.responseXML;
                try {
                    this.status = this._xhr.status || null;
                    this.statusText = this._xhr.statusText || null;
                } catch (e) {
                    this.status = null;
                    this.statusText = null;
                }
                this._raise('Complete', this);
                if (this.status == 200) {
                    this._raise('Success', this);
                    try {
                        var _contentType = this._xhr.getResponseHeader('Content-type');
                        if (_contentType.match(/^text\/javascript/i))
                            eval(this.responseText); else
                            if (_contentType.match(/^text\/x\-json/i))
                                this.responseJSON = eval('(' + this.responseText + ')');
                    } catch(e) {
                        this._raise('InternalError', advAJAX.ERROR_INVALID_EVAL_STRING);
                    }
                    ;
                } else
                    this._raise('Error', this);
                if (this.repeat) {
                    if (++this.repeatNo != this.repeatCount) {
                        this._raise('RepeatDelay', this);
                        var _this = this;
                        this._timerRepeat = window.setTimeout(function() {
                            _this._raise('Repeat', this);
                            _this.init();
                            _this.run();
                        }, this.repeatDelay);
                        return;
                    }
                }
                this.destroy();
                (this.disableForm) && this._switchForm(true);
                this._handleGroup();
                this._raise('Finalization', this);
        }
    };

    this._handleGroup = function() {

        if (this.group === null) return;
        (--advAJAX._groupData[this.group] == 0) && this._raise('GroupLeave', this);
    }

    this._onTimeout = function() {

        if (this._xhr == null || this._eventHandled[4])
            return;
        this.requestAborted = this.requestTimedOut = true;
        this._xhr.abort();
        this._raise('Timeout');
        if (this.retryNo++ < this.retryCount) {
            this.init();
            this._raise('RetryDelay', this);
            var _this = this;
            this._timerTimeout = window.setTimeout(function() {
                _this._raise('Retry', _this);
                _this.run();
            }, this.retryDelay);
        } else {
            this.destroy();
            (this.disableForm) && this._switchForm(true);
            this._handleGroup();
            this._raise('Finalization', this);
        }
    };

    this.run = function() {

        if (this.init() == false)
            return false;
        this.requestAborted = this.requestTimedOut = false;
        (!this._eventHandled[0]) && (this._raise('Initialization', this)) && (this._eventHandled[0] = true);
        if (this.retryNo == 0 && this.repeatNo == 0) {
            if (this.useJSON) {
                if (typeof [].toJSONString != 'function') {
                    this._raise('InternalError', advAJAX.ERROR_NO_JSON);
                    return;
                }
                for (var p in this.parameters) {
                    var useJson = typeof [].toJSONString == 'function';
                    (this.queryString.length > 0) && (this.queryString += '&');
                    this.queryString += encodeURIComponent(p) + '=' +
                                        encodeURIComponent(this.parameters[p].toJSONString());
                }
            } else {
                for (var p in this.parameters) {
                    (this.queryString.length > 0) && (this.queryString += '&');
                    if (typeof this.parameters[p] != "object")
                        this.queryString += encodeURIComponent(p) + '=' + encodeURIComponent(this.parameters[p]); else {
                        if (!(this.parameters[p] instanceof Array)) continue;
                        for (var i = 0, cnt = this.parameters[p].length; i < cnt; i++)
                            this.queryString += encodeURIComponent(p) + '=' + encodeURIComponent(this.parameters[p][i]) + '&';
                        this.queryString = this.queryString.slice(0, -1);
                    }
                }
            }
            if (this.method == 'GET' && this.unique) {
                (this.queryString.length > 0) && (this.queryString += '&');
                this.queryString += encodeURIComponent(this.uniqueParameter) + '=' + new Date().getTime().toString().substr(5) + Math.floor(Math.random() * 100).toString();
            }
            (this.method == 'GET') && (this.queryString.length > 0) && (this.url += (this.url.indexOf('?') != -1 ? '&' : '?') + this.queryString);
        }
        (this.disableForm) && this._switchForm(false);
        try {
            this._xhr.open(this.method, this.url, true, this.username || '', this.password || '');
        } catch(e) {
            this._raise('FatalError', this);
            return false;
        }
        var _this = this;
        (this.timeout > 0) && (this._timerTimeout = window.setTimeout(function() {
            _this._onTimeout();
        }, this.timeout));
        if (typeof this._xhr.setRequestHeader == 'function')
            for (var p in this.headers)
                this._xhr.setRequestHeader(encodeURIComponent(p), encodeURIComponent(this.headers[p]));
        if (this.method == 'POST') {
            try {
                this._xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            } catch(e) {
            }
            ;
            this._xhr.send(this.queryString);
        } else if (this.method == 'GET') {
            this._xhr.send('');
        }
    };

    this.abort = function() {

        this.requestAborted = true;
        window.clearTimeout(this._timerTimeout);
        window.clearTimeout(this._timerRepeat);
        this._handleGroup();
        this._raise('Abort', this);
        this.destroy();
        this._raise('Finalization', this);
    },

            this._extendObject = function(target, source) {

                for (var p in source)
                    target[p] = source[p];
            };

    this.handleArguments = function(args) {

        (typeof args['form'] == 'string') && (args['form'] = document.getElementById(args['form']));
        for (var p in args) {
            if (typeof this[p] == 'undefined') {
                this.parameters[p] = args[p];
            } else {
                if (p != 'parameters' && p != 'headers') {
                    this[p] = args[p];
                } else
                    this._extendObject(this[p], args[p]);
            }
        }
        this.method = this.method.toUpperCase();
        (typeof this.form == 'object') && (this.form !== null) && this._appendForm();
        (args.repeat) && (this.repeatCount++);
    };

    this._switchForm = function(enable) {

        if (typeof this.form != 'object' || this.form === null)
            return;
        var _f = this.form;
        for (var i = 0; i < _f.elements.length; i++)
            if (!enable) {
                if (_f.elements[i]['disabled'])
                    _f.elements[i]['_disabled'] = true; else
                    _f.elements[i]['disabled'] = 'disabled';
            } else {
                if (typeof _f.elements[i]['_disabled'] == 'undefined' || _f.elements[i]['_disabled'] === null)
                    _f.elements[i]['disabled'] = '';
                try {
                    delete _f.elements[i]['_disabled'];
                } catch(e) {
                    _f.elements[i]['_disabled'] = null;
                }
                ;
            }
    };

    this._appendForm = function() {

        var _f = this.form;
        this.method = _f.getAttribute('method').toUpperCase();
        this.url = _f.getAttribute('action');
        for (var i = 0; i < _f.elements.length; i++) {
            var _e = _f.elements[i];
            if (_e.disabled)
                continue;
            switch (_e.type) {
                case 'text':
                case 'password':
                case 'hidden':
                case 'textarea':
                    this._addParameter(_e.name, _e.value);
                    break;
                case 'select-one':
                    if (_e.selectedIndex >= 0)
                        this._addParameter(_e.name, _e.options[_e.selectedIndex].value);
                    break;
                case 'select-multiple':
                    var _r = [];
                    for (var j = 0; j < _e.options.length; j++)
                        if (_e.options[j].selected)
                            _r[_r.length] = _e.options[j].value;
                    (_r.length > 0) && (this._addParameter(_e.name, _r));
                    break;
                case 'checkbox':
                case 'radio':
                    (_e.checked) && (this._addParameter(_e.name, _e.value));
                    break;
            }
        }
    };

    this._addParameter = function(name, value) {

        if (typeof this.parameters[name] == 'undefined') {
            this.parameters[name] = value;
        } else {
            if (typeof this.parameters[name] != 'object')
                this.parameters[name] = [ this.parameters[name], value ]; else
                this.parameters[name][this.parameters[name].length] = value;
        }
    };

    this._delParameter = function(name) {

        delete this.parameters[name];
    };

    this._raise = function(name) {

        for (var i = 1, args = []; i < arguments.length; args[args.length] = arguments[i++]);
        (typeof this['on' + name] == 'function') && (this['on' + name].apply(null, args));
        (name == 'FatalError') && this._raise('Finalization', this);
    }

}

advAJAX._groupData = new Object();
advAJAX._defaultParameters = new Object();

advAJAX.get = function(args) {

    return advAJAX._handleRequest('GET', args);
}
advAJAX.post = function(args) {

    return advAJAX._handleRequest('POST', args);
}
advAJAX.head = function(args) {

    return advAJAX._handleRequest('HEAD', args);
}
advAJAX._handleRequest = function(requestType, args) {

    args = args || { };
    var _a = new advAJAX();
    _a.method = requestType;
    _a.handleArguments(args);
    setTimeout(function() {
        _a.run()
    }, 0);
    return _a;
};
advAJAX.submit = function(form, args) {

    args = args || {};
    if (typeof form == 'undefined' || form === null)
        return false;
    var _a = new advAJAX();
    args['form'] = form;
    _a.handleArguments(args);
    setTimeout(function() {
        _a.run()
    }, 0);
    return _a;
};
advAJAX.assign = function(form, args) {

    args = args || {};
    (typeof form == 'string') && (form = document.getElementById(form));
    if (typeof form == 'undefined' || form === null)
        return false;
    form['_advajax_args'] = args;
    var _onsubmit = function(event) {
        event = event || window.event;
        if (event.preventDefault) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            event.returnValue = false;
            event.cancelBubble = true;
        }
        var _e = event.target || event.srcElement;
        return !advAJAX.submit(_e, _e['_advajax_args']);
    }
    if (form.addEventListener) {
        form.addEventListener('submit', _onsubmit, false);
    } else if (form.attachEvent) {
        form.attachEvent('onsubmit', _onsubmit);
    }
    return true;
};
advAJAX.download = function(target, url) {

    (typeof target == 'string') && (target = document.getElementById(target));
    if (typeof target == 'undefined' || target === null)
        return false;
    advAJAX.get({
        'url': url,
        'onSuccess' : function(o) {
            target.innerHTML = o.responseText;
        }
    });
};
advAJAX.setDefaultParameters = function(args) {

    advAJAX._defaultParameters = new Object();
    for (var a in args)
        advAJAX._defaultParameters[a] = args[a];
};

advAJAX.ERROR_INVALID_EVAL_STRING = -1;
advAJAX.ERROR_NO_JSON = -2;