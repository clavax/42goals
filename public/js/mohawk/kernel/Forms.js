Mohawk.FormsInterface = {
    getData: function () {
        var data = {};
        for (var i = 0; i < this.elements.length; i ++) {
            var element = this.elements[i];
            if (element && element.name) {
                if (!element.disabled) {
                    switch (element.type) {
                    case 'checkbox':
                    case 'radio':
                        if (!element.checked) {
                            break;
                        }
                    default:
                        switch (element.tagName.toLowerCase()) {
                        case 'select':
                            if (element.multiple) {
                                if (element.name in data) {
                                    if (!(data[element.name] instanceof Array)) {
                                        data[element.name] = [data[element.name]];
                                    }
                                } else {
                                    data[element.name] = [];
                                }
                                for (var j = 0; j < element.options.length; j ++) {
                                    if (element.options[j].selected) {
                                        data[element.name].push(element.options[j].value);
                                    }
                                }
                                break;
                            }
                        default:
                            if (element.name in data) {
                                if (!(data[element.name] instanceof Array)) {
                                    data[element.name] = [data[element.name]];
                                }
                                data[element.name].push(element.value);
                            } else {
                                data[element.name] = element.value;
                            }
                        }
                    }
                }
            }
        }
        return data;
    },

    setData: function (data, name) {
        var form = this;
        var changed = [];
        if (typeof name == 'undefined') {
            name = '';
        }
        function set_data(data, name, keys) {
            if (data instanceof Object) {
                if (data instanceof Array) {
                    for (var i = 0; i < data.length; i ++) {
                        set_data(data[i], name ? name : i, name ? (keys ? keys.concat(i) : [i]) : []);
                    }
                } else {
                    for (var i in data) {
                        if (typeof Object.prototype[i] != 'undefined') {
                            continue;
                        }
                        set_data(data[i], name ? name : i, name ? (keys ? keys.concat(i) : [i]) : []);
                    }
                }
            } else {
                var element_name = name + (keys.length ? '[' + keys.join('][')  + ']' : '');
                if (typeof form[element_name] == 'undefined') {
                    element_name = name + (keys.length > 1 ? '[' + keys.slice(0, -1).join('][')  + ']' : '') + '[]';
                    if (typeof form[element_name] == 'undefined') {
                        return false;
                    }
                }
                var element = form[element_name];
                if (!element.length || element.tagName) {
                    element = [element];
                }
                for (var i = 0; i < element.length; i ++) {
                    if (!element[i].tagName) {
                        continue;
                    }
                    switch (element[i].tagName.toLowerCase()) {
                    case 'select':
                        if (element[i].multiple) {
                            for (var j = 0; j < element[i].options.length; j ++) {
                                if (element[i].options[j].value == data) {
                                    element[i].options[j].selected = true;
                                    changed.push(element[i].options[j]);
                                } else if (Array.find(changed, element[i].options[j]) === false) {
                                    element[i].options[j].selected = false;
                                }
                            }
                            break;
                        }
                    case 'input':
                        if (element[i].type == 'checkbox' || element[i].type == 'radio') {
                            if (element[i].value == data) {
                                element[i].checked = true;
                                changed.push(element[i]);
                            } else if (Array.find(changed, element[i]) === false) {
                                element[i].checked = false;
                            }
                            break;
                        }
                    default:
                        element[i].value = data;
                    }
                    if (typeof(element[i].onchange) != 'undefined' && element[i].onchange instanceof Function) {
                        element[i].onchange();
                    }
                }
            }
            return true;
        }
        set_data(data, name, []);
    },
    
    createInput: function (type, name, value, options) {
        switch (type) {
        default:
        case 'hidden':
        case 'text':
        case 'password':
        case 'checkbox':
            if (IE && !IE9) {
                var input = document.createElement('<input name="' + name + '">');
            } else {
                var input = document.createElement('INPUT');
                input.name = name;
            }
            input.type = type;
            input.value = value;
            break;
            
        case 'textarea':
            if (IE && !IE9) {
                var input = document.createElement('<textarea name="' + name + '">');
            } else {
                var input = document.createElement('TEXTAREA');
                input.name = name;
            }
            input.value = value;
            break;
            
        case 'select':
            if (IE && !IE9) {
                var input = document.createElement('<select name="' + name + '">');
            } else {
                var input = document.createElement('SELECT');
                input.name = name;
            }
            foreach(options, function (i, val) {
                if (typeof(val) == 'undefined') {
                    return;
                }
                var option = document.createElement('OPTION');
                option.value = val.value;
                option.innerHTML = val.text;
                if (value == option.value) {
                    option.selected = true;
                }
                input.appendChild(option);
            });
            break;
        }
        return input;
    }
};

window.FormsInterface = Mohawk.FormsInterface;