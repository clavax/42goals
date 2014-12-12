include('mohawk.kernel.XML');
Loader.includeTemplate('wysiwyg');

window.Wysiwyg = new Class({
    id: 0,
    mode: '',
    tidy: true,
    element: null,
    editor: null,
    textarea: null,
    doc: null,
    win: null,
    
    __construct: function () {
        self.id = Math.rand(1e15, 1e16);
    },
    
    set: function (textarea) {
        var element = DOM.element('div', {
            className: 'wysiwyg',
            html: Template.transform(WYSIWYG)
        });
        self.element = element;
        textarea.collapse();
        textarea.parentNode.insertAfter(element, textarea);
        
        var editor = DOM.element('iframe', {
            frameBorder: 1,
            src: URL.public + 'html/wysiwyg.html'
        });
        if (IE) {
            editor.contentEditable = true;
        }
        editor.object = self;
        
        editor.addEvent('load', function () {
            var frame = window.event ? window.event.srcElement : this;
            var win = frame.contentWindow;
            var doc = win.document;
            if (frame.offsetLeft) {
                doc.designMode = 'On';
                if (FF) {
                    doc.execCommand('styleWithCSS', false, false);
                }
            } else {
                if (!FF) {
                    doc.designMode = 'On';
                } else {
                    doc.onfocus = function () {
                        doc.designMode = 'On';
                        if (FF) {
                            doc.execCommand('styleWithCSS', false, false);
                        }
                        delete(doc.onfocus);
                    }
                }
            }
            
            frame.object.win = win;
            frame.object.doc = doc;
           
            var body = doc.body || doc.documentElement ? doc.documentElement.getElementsByTagName('BODY')[0] : null;
            if (body) {
                if (!doc.body) {
                    doc.body = body;
                }
                body.innerHTML = frame.object.textarea.value;
            }
            doc.frame = frame;
            //frame.object.switchMode();
            frame.object.switchHTML();

            DOM.enchaseNode(doc);
            win.frame = frame;
            if (WEBKIT) {
                win.onblur = function () {
                    this.frame.object.setText();
                };
            } else {
                doc.addEvent('blur', function () {
                    this.frame.object.setText(); 
                });
            }
            
            frame.object.init();
        });
        
        self.editor = editor;
        self.textarea = textarea;
        element.getElementsByClassName('textarea')[0].appendChild(textarea);
        element.getElementsByClassName('editor')[0].appendChild(editor);
        
        foreach(['BUTTON', 'SELECT', 'INPUT'], function () {
            var buttons = element.getElementsByTagName(this);
            for (var i = 0; i < buttons.length; i ++) {
                buttons[i].wg = self;
            }
        });
    },
    
    init: function () {
        /*self.doc.onkeypress = function (event) {
            event = DOM.event(event);
            if (event.key() == 13) {
                if (event.ctrlKey) {
                    self.pasteNode(self.doc.createElement('br'));
                } else {
                    self.pasteNode(self.doc.createElement('p'));
                }
                event.preventDefault();
            }
        };*/
        self.doc.onpaste = function (event) {
            setTimeout(function () {
                self.doc.body.innerHTML = self.toHtml(self.doc.body, null, null, true);
            }, 1);
        };
    },
    
    test: function () {
        self.doc.body.innerHTML = 'abc';
    },
    
    range: function () {
        var range, selection;
        self.win.focus();
        if (!IE && self.win.getSelection) {
            selection = self.win.getSelection()
            range = selection.getRangeAt(0);
        } else if (self.doc.selection) {
            selection = self.doc.selection;
            range = selection.createRange();
        }
        range.sel = selection;
        return range;
    },
    
    selection: function () {
        if (self.win.getSelection) {
            var range = self.win.getSelection().getRangeAt(0);
            var start = range.startContainer;
            var end   = range.endContainer;
            var root  = range.commonAncestorContainer;
    
            start = self.getTag(start);
            end   = self.getTag(end);
            root  = start == end ? start : root;
            
            return {range: range, root: root, start: start, end: end};
            
        } else if (self.doc.selection) {
            range = self.doc.selection.createRange();
            if (!range.duplicate) {
                return null;
            }
              
            var r1 = range.duplicate();
            var r2 = range.duplicate();
            r1.collapse(true);
            r2.moveToElementText(r1.parentElement());
            r2.setEndPoint('EndToStart', r1);
            start = r1.parentElement();
              
            r1 = range.duplicate();
            r2 = range.duplicate();
            r2.collapse(false);
            r1.moveToElementText(r2.parentElement());
            r1.setEndPoint('StartToEnd', r2);
            end = r2.parentElement();
              
            root = range.parentElement();
            if (start == end) {
                root = start;
            }
            
            return {range: range, root: root, start: start, end: end};
            
        } else {
            return null;
        }
    },
    
    pasteNode: function (node) {
        var sel, range;
        if (typeof self.win.getSelection != "undefined") {
            sel = self.win.getSelection();
            if (sel.getRangeAt && sel.rangeCount) {
                range = sel.getRangeAt(0);
                range.deleteContents();
                range.insertNode(node);
                range.setEndAfter(node);
                range.setStartAfter(node);
                sel.removeAllRanges();
                sel.addRange(range);
                range.selectNode(node);
            }
        } else if (typeof self.doc.selection != "undefined") {
            sel = self.doc.selection;
            if (sel.createRange) {
                range = sel.createRange();
                range.pasteHTML(node.outerHTML);
                range.select();
            }
        }
    },
    
    getTag: function (node) {
        while (node && !node.tagName) {
            node = node.parentNode;
        }
        return node;
    },
    
    bold: function () {
        self.doc.execCommand('Bold', false, null);
    },
    
    italic: function () {
        self.doc.execCommand('Italic', false, null);
    },
    
    underline: function () {
        self.doc.execCommand('Underline', false, null);
    },
    
    wrap: function (range, tag) {
        if (range.pasteHTML) {
            range.pasteHTML('<' + tag + '>' + range.htmlText + '</' + tag + '>');
        } else if (range.insertNode) {
            var fragment = range.cloneContents();
            range.deleteContents();
            var node = self.doc.createElement(tag);
            node.appendChild(fragment);
            range.insertNode(node);
        } else {
            Console.error('wrap is not supported');
        }
    
    },
    
    changeTag: function (node, tag) {
        Console.log('change tag to: ' + tag);
        var clone = self.doc.createElement(tag);
        clone.innerHTML = node.innerHTML;
        node.parentNode.replaceChild(clone, node);
    },
    
    paragraph: function (tag) {
        var sel = self.selection();
        var root_tag = sel.root.tagName ? sel.root.tagName.toLowerCase() : false;
        switch (tag) {
        case 'p':
        case 'h1':
        case 'h2':
        case 'h3':
        case 'h4':
            if (root_tag == tag) {
                break;
            }
            if (Array.find(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'], root_tag) !== false) {
                self.changeTag(sel.root, tag);
                break;
            }
            self.wrap(sel.range, 'p');
            break;
        }
        //self.doc.execCommand('FormatBlock', false, style);
    },
    
    align: function (align) {
        var cmd = '';
        switch (align) {
        case 'left':
            cmd = 'JustifyLeft';
            break;
        case 'right':
            cmd = 'JustifyRight';
            break;
        case 'center':
            cmd = 'JustifyCenter';
            break;
        case 'justify':
            cmd = 'JustifyFull';
            break;
        default:
            cmd = 'JustifyNone';
        }
        self.doc.execCommand(cmd, false, null);
    },
    
    removeFormat: function () {
        self.doc.execCommand('RemoveFormat', false, null);
    },
    
    insertList: function (type) {
        self.doc.execCommand(type == 'ordered' ? 'InsertOrderedList' : 'InsertUnorderedList', false, null);
    },
    
    insertLink: function () {
        var url = prompt('Enter the URL', '');
        self.doc.execCommand('CreateLink', false, url);
    },
    
    insertImage: function () {
        var url = prompt('Enter the image URL', '');
        var range = self.range();
        var img = self.doc.createElement('img');
        img.src = url;
        if (range.insertNode) {
            range.insertNode(img);
        } else {
            range.pasteHTML(img.outerHTML);
        }
        return false;
    },

    removeLink: function () {
        self.doc.execCommand('Unlink', false, null);
    },

    switchText: function () {
        self.mode = Wysiwyg.MODE_TEXT;
        self.setText();
        self.textarea.display();
        self.editor.collapse();
    },
    
    switchHTML: function () {
        self.mode = Wysiwyg.MODE_HTML;
        self.setHTML();
        self.textarea.collapse();
        self.editor.display();
    },
    
    setText: function () {
        if (self.tidy) {
            self.textarea.value = self.toHtml(self.doc.body, null, null, true);
        } else {
            self.textarea.value = self.doc.body.innerHTML;
        }
    },
    
    setHTML: function () {
        if (self.doc.body) {
            var html = self.textarea.value;
            self.doc.body.innerHTML = html;
        }
    },
    
    switchMode: function () {
        if (self.mode != Wysiwyg.MODE_HTML) {
            self.switchHTML();
        } else {
            self.switchText();
        }
    },
    
    allowed_tags: {
        h1: {}, 
        h2: {}, 
        h3: {}, 
        h4: {}, 
        h5: {}, 
        h6: {}, 
        p: {}, 
        b: {},
        strong: {},
        i: {},
        em: {},
        s: {},
        del: {},
        a: {href: true}, 
        img: {src: true, alt: true}, 
        ul: {}, 
        ol: {},
        li: {},
        div: {_replace_by: 'p'}
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
            var value = XML.escape(node.nodeValue);
            value = XML.clean(value);
            return value;

        case Mohawk.DOM.ELEMENT_NODE:
            var tag = node.nodeName.toLowerCase();
            var str = '';
            
            if (!(tag in self.allowed_tags)) {
                exclude_name = true;
            } else {
                if ('_replace_by' in self.allowed_tags[tag]) {
                    tag = self.allowed_tags[tag]._replace_by;
                }
            }
            
            if (!exclude_name) {
                var str = (!first && Array.find(XML.inline_elements, tag) === false ? padding : '') + '<' + tag;
                for (var i = 0; i < node.attributes.length; i ++) {
                    if (!(node.attributes[i].nodeName in self.allowed_tags[tag])) {
                        continue;
                    }
                    var value = XML.escape(node.attributes[i].nodeValue.toString());
                    str += ' ' + node.attributes[i].nodeName + '="' + value + '"';
                }
                if (!node.childNodes.length && Array.find(XML.empty_elements, tag) !== false) {
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
                    if (Array.find([Mohawk.DOM.TEXT_NODE, Mohawk.DOM.CDATA_SECTION_NODE], child.nodeType) === false && child.nodeName && Array.find(XML.inline_elements, child.nodeName.toLowerCase()) === false) {
                        padding_closing = padding;
                    }
                }
            }
            
            // close tag
            if (node.childNodes.length || Array.find(XML.empty_elements, tag) === false) {
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

Wysiwyg.MODE_TEXT = 'text';
Wysiwyg.MODE_HTML = 'html';
Wysiwyg.replace = function (textarea) {
    var wg = new Wysiwyg;
    wg.set(textarea);
    return wg;
};