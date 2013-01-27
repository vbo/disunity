var has = function (o, k) {
    return o.hasOwnProperty(k);
};
var ich = function (o, clb, save, save_keys, first) {
    var m = [];
    if (save_keys) {
        m = {};
    }
    for (var v in o) {
        if (has(o, v)) {
            var r = clb(v, o[v]);
            if (first && r) {
                return v;
            }
            if (save) {
                if (save_keys) {
                    m[v] = r;
                    continue;
                }
                m.push(r);
            }
        }
    }
    if (save) {
        return m;
    }
    return null;
};

var count_reduce = function (o, clb) {
    var count = 0;
    each(o, function (k, v) {
        if (clb(v)) {
            count++;
        }
    });
    return count;
};

var first = function (o, clb) {
    return ich(o, clb, false, false, true);
};

var pop_first = function (o, clb, return_key) {
    var key = ich(o, clb, false, false, true);
    if (key === null) {
        return null;
    }
    var value = o[key];
    delete o[key];
    return return_key ? key : value;
};
var each = function (o, clb) {
    ich(o, clb);
};
var reach = function (o, clb) {
    return ich(o, clb, true);
};
var reachs = function (o, clb) {
    return ich(o, clb, true, true);
};

var obj_length = function (o) {
    return reach(o, function (k, v) { return 1; }).length;
};

var number_format = function (b) {
    var a = '';
    var c = parseInt(b);
    if (c) {
        a = c;
        if (c > 0) {
            a = '+' + c;
        }
    }
    return a;
};

var obj_keys = function (o) {
    var keys = [];
    each(o, function (k, v) {
        keys.push(k);
    });
    return keys;
};

var location_hash = function (v) {
    var ret = location.href.split("#")[1] || "";
    if (v != undefined) {
        location.hash = v;
    }
    return ret;
};

var SmartHash = function (type, postMethod) {
    this.type = type;
    this.postMethod = postMethod;
};

SmartHash.prototype.each = function (clb, post) {
    var me = this;
    each(this, function (id, item) {
        if (item instanceof me.type) {
            clb(item);
            if (post) {
                item[me.postMethod]();
            }
        }
    });
};

SmartHash.prototype.eachFromObj = function (clb, obj, post) {
    var me = this;
    each(obj, function (id, other_item) {
        var item = me[id];
        if (item instanceof me.type) {
            clb(item, other_item);
            if (post) {
                item[me.postMethod]();
            }
        }
    });
};

SmartHash.prototype.eachFromList = function (clb, list, post) {
    var me = this;
    each(list, function (k, id) {
        var item = me[id];
        if (item instanceof me.type) {
            clb(item);
            if (post) {
                item[me.postMethod]();
            }
        }
    });
};

SmartHash.prototype.setProperty = function (list, key, value) {
    if (list) {
        this.eachFromList(function (item) {
            item[key] = value;
        }, list, true);
    }
};

// Production steps of ECMA-262, Edition 5, 15.4.4.18
// Reference: http://es5.github.com/#x15.4.4.18
if ( !Array.prototype.forEach ) {

    Array.prototype.forEach = function forEach( callback, thisArg ) {

        var T, k;

        if ( this == null ) {
            throw new TypeError( "this is null or not defined" );
        }

        // 1. Let O be the result of calling ToObject passing the |this| value as the argument.
        var O = Object(this);

        // 2. Let lenValue be the result of calling the Get internal method of O with the argument "length".
        // 3. Let len be ToUint32(lenValue).
        var len = O.length >>> 0; // Hack to convert O.length to a UInt32

        // 4. If IsCallable(callback) is false, throw a TypeError exception.
        // See: http://es5.github.com/#x9.11
        if ( {}.toString.call(callback) !== "[object Function]" ) {
            throw new TypeError( callback + " is not a function" );
        }

        // 5. If thisArg was supplied, let T be thisArg; else let T be undefined.
        if ( thisArg ) {
            T = thisArg;
        }

        // 6. Let k be 0
        k = 0;

        // 7. Repeat, while k < len
        while( k < len ) {

            var kValue;

            // a. Let Pk be ToString(k).
            //   This is implicit for LHS operands of the in operator
            // b. Let kPresent be the result of calling the HasProperty internal method of O with argument Pk.
            //   This step can be combined with c
            // c. If kPresent is true, then
            if ( Object.prototype.hasOwnProperty.call(O, k) ) {

                // i. Let kValue be the result of calling the Get internal method of O with argument Pk.
                kValue = O[ k ];

                // ii. Call the Call internal method of callback with T as the this value and
                // argument list containing kValue, k, and O.
                callback.call( T, kValue, k, O );
            }
            // d. Increase k by 1.
            k++;
        }
        // 8. return undefined
    };
}
if(!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(needle) {
        for(var i = 0; i < this.length; i++) {
            if(this[i] === needle) {
                return i;
            }
        }
        return -1;
    };
}

