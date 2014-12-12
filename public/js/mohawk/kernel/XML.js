Mohawk.XML = new Singletone({
	
    empty_elements: ['br', 'img', 'link', 'meta', 'input', 'hr'],
    
    // remove or add any to prevent line break for these elements
    inline_elements: ['a', 'abbr', 'acronym', 'b', 'basefont', 'bdo',
                      'big', 'br', 'cite', 'code', 'dfn', 'em', 'font',
                      'i', 'img', 'input', 'kbd', 'label', 'q', 's',
                      'samp', 'select', 'small', 'span', 'strike',
                      'strong', 'sub', 'sup', 'textarea', 'tt', 'u',
                      'var', 'applet', 'button', 'del', 'iframe',
                      'ins', 'map', 'object', 'script'],
                      
    toObject: function (node) {
        switch (node.nodeType) {
        
        case Mohawk.DOM.TEXT_NODE:
        case Mohawk.DOM.CDATA_SECTION_NODE:
            return node.nodeValue;
            
        case Mohawk.DOM.ELEMENT_NODE:
            var data = null;
            for (var i = 0; i < node.attributes.length; i ++) {
                if (node.attributes[i].nodeName != '_key') {
                    if (data === null) {
                        data = {};
                    }
                    data[node.attributes[i].nodeName] = node.attributes[i].nodeValue;
                } 
            }
            var allow_text_nodes = true;
            node.normalize(); // Mozilla breaks large text nodes into several small one, so we have to fix this
            for (var i = 0; i < node.childNodes.length; i ++) {
                var child = self.toObject(node.childNodes[i]);
                if (child instanceof Object) {
                    if (!(data instanceof Object)) {
                        data = {};
                    }
                    if (child.name in data) {
                        if (!(data[child.name] instanceof Array)) {
                            data[child.name] = [data[child.name]];
                        }
                        data[child.name].push(child.content);
                    } else if (node.childNodes[i].getAttribute('_key') != null) {
                        data[child.name] = [child.content];
                    } else {
                        data[child.name] = child.content;
                    }
                    allow_text_nodes = false;
                } else if (allow_text_nodes) {
                    data = child;
                }
            }
            return {name: node.nodeName, content: data};
            
        case Mohawk.DOM.DOCUMENT_NODE:
            var data = self.toObject(node.documentElement);
            return data.content;
            
        default:
            Console.log('Cannot handle type: ' + node.nodeType);
        }
    },
    
    parse: function (str) {
        var xmldoc;
        if (window.DOMParser) {
            parser = new DOMParser();
            xmldoc = parser.parseFromString(str, 'text/xml');
        } else  {
            xmldoc = new ActiveXObject('Microsoft.XMLDOM');
            xmldoc.async = 'false';
            xmldoc.loadXML(str);
        }
        return self.toObject(xmldoc);
    },
    
    escape: function (str) {
        str = str.replace('&', '&amp;');
        var table = {
//            38: 'amp',
            62: 'gt',
            60: 'lt',
            160: 'nbsp',
            169: 'copy',
            8212: 'mdash',
            8482: 'trade',
            8482: 'trade',
            8222: 'bdquo',
            171: 'laquo',
            8220: 'ldquo',
            8222: 'lsaquo',
            8249: 'lsquo',
            187: 'raquo',
            8221: 'rdquo',
            8250: 'rsaquo',
            8217: 'rsquo',
            8218: 'sbquo'
        };
        foreach(table, 
            function (code) {
                str = str.replace(new RegExp(String.fromCharCode(code), 'g'), '&' + this + ';');
            }
        );
        return str;
    },
    
    clean: function (str) {
		str = str.replace(new RegExp('^[\\s]+', 'gm'), ' ');
		str = str.replace(new RegExp('[\\s]+$', 'gm'), ' ');
        return str;
    },
    
    toHtml: function (node, padding, pad_with, exclude_name, first) {
        if (!padding) {
            padding = '\n';
        }
        if (!pad_with) {
            pad_with = '\t';
        }
        
        switch (node.nodeType) {
        
        case Mohawk.DOM.TEXT_NODE:
        case Mohawk.DOM.CDATA_SECTION_NODE:
            var value = self.escape(node.nodeValue);
            value = self.clean(value);
            return value;

        case Mohawk.DOM.ELEMENT_NODE:
            var tag = node.nodeName.toLowerCase();
            var str = '';
            
            
            if (!exclude_name) {
                var str = (!first && Array.find(self.inline_elements, tag) === false ? padding : '') + '<' + tag;
                for (var i = 0; i < node.attributes.length; i ++) {
                    if (node.attributes[i].nodeValue == '' || node.attributes[i].nodeValue == null) {
                        continue;
                    }
                    if (node.attributes[i].nodeValue.toString().match(new RegExp('^(_moz|apple|contentEditable)', 'i'))) {
                    	continue;
                    }
                    if (node.attributes[i].nodeName.match(new RegExp('^(_moz|apple|contentEditable)', 'i'))) {
                        continue;
                    }
                    var value = self.escape(node.attributes[i].nodeValue.toString());
                    str += ' ' + node.attributes[i].nodeName + '="' + value + '"';
                }
                if (!node.childNodes.length && Array.find(self.empty_elements, tag) !== false) {
                    str += ' />';
                } else {
                    str += '>';
                }
                
            }
            
            // convert children
            var padding_closing = '';
            if (tag == 'pre') {
                str += node.innerHTML;
            } else {
                node.normalize();
                var padding_children = !exclude_name ? padding + pad_with : padding;
                var first_child = true;
                for (var i = 0; i < node.childNodes.length; i ++) {
                	var child = node.childNodes[i];
                    var value = self.toHtml(child, padding_children, pad_with, false, exclude_name && first_child);
                    str += value;
                    if (value) {
                        first_child = false;
                    }
                    if (Array.find([Mohawk.DOM.TEXT_NODE, Mohawk.DOM.CDATA_SECTION_NODE], child.nodeType) === false && child.nodeName && Array.find(self.inline_elements, child.nodeName.toLowerCase()) === false) {
						padding_closing = padding;
					}
                }
            }
            
            // close tag
            if (node.childNodes.length || Array.find(self.empty_elements, tag) === false) {
                str += !exclude_name ? (padding_closing + '</' + tag + '>') : '';
            }
            
            return str;
            
        case Mohawk.DOM.DOCUMENT_NODE:
            return self.toHtml(node.documentElement, padding, pad_with, exclude_name, not_first);
            
        default:
            // TODO:
            return 'XML: unsupported type of node ' + node.nodeType;
        }

    }
});

window.XML = Mohawk.XML;