/**
 * Implementing some SVG 2.0 DOM proposals
 * http://www.w3.org/Graphics/SVG/WG/wiki/SVG_2_DOM
 */
(function () {
    "use strict";

    Element.prototype.constructElementNS = function (namespace, elementName, attributeObj) {
        var el = document.createElementNS(namespace, elementName);
        for (var attr in attributeObj) {
            var attrValue = attributeObj[ attr ];
            if ("object" == typeof attrValue) {
                el.setAttributeNS(attrValue[0], attr, attrValue[1]);
            } else {
                el.setAttribute(attr, attrValue);
            }
        }
        return el;
    };

    Element.prototype.constructElement = function (elementName, attributeObj) {
        return this.constructElementNS(this.namespaceURI, elementName, attributeObj);
    };

    Element.prototype.insertElementNS = function (namespace, elementName, attributeObj, index) {
        var el = this.constructElementNS(namespace, elementName, attributeObj);
        if (null == index) {
            this.appendChild(el);
        } else {
            // insert child at requested index, or as last child
            // if index is too high or no index is specified
            var targetIndex = index + 1;
            if (0 == index) {
                targetIndex = 0;
            }
            var targetEl = this.childNodes[ targetIndex ];
            if (targetEl) {
                this.insertBefore(el, targetEl);
            }
            else {
                this.appendChild(el);
            }
        }
        return el;
    };

    Element.prototype.insertElement = function (elementName, attributeObj, index) {
        return this.insertElementNS(this.namespaceURI, elementName, attributeObj, index);
    };

    Element.prototype.setAttrValues = function (attributeObj) {
        for (var attr in attributeObj) {
            var attrValue = attributeObj[ attr ];
            if ("object" == typeof attrValue) {
                this.setAttributeNS(attrValue[0], attr, attrValue[1]);
            }
            else {
                this.setAttribute(attr, attrValue);
            }
        }
        return this;
    };
})();

