(function () {

    "use strict";

    var NS = 'http://www.w3.org/2000/svg';

    var createSVGElement = function (name) {
        return document.createElementNS(NS, name);
    };

    SVGSVGElement.create = function () {
        return createSVGElement('svg');
    };

    SVGElement.prototype.setAttrValues = function (dic) {
        for (var k in dic) {
            this.setAttribute(k, dic[k]);
        }
    };

    SVGElement.prototype.createChild = function (name, attrs) {
        var el = createSVGElement(name);
        if (attrs) {
            el.setAttrValues(attrs);
        }
        this.appendChild(el);
        return el;
    };

})();

