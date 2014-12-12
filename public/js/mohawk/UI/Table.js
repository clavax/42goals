Mohawk.UI.Table = new Class ({
    id: '',
    element: null,
    head: null,
    body: null,
    cols: [],

    __construct: function (id, data, head) {
        self.id = id;

        self.element = document.createElement('TABLE');
        self.element.id = id;
        self.element.object = self;

        if (head) {
            self.cols = Object.keys(head);
            self.head = self.createHead(head);
            self.element.appendChild(self.head);
        }
        
        self.body = self.createBody();
        self.element.appendChild(self.body);
        self.setRows(data);
    },
    
    createHead: function (structure) {
        var head = document.createElement('THEAD');
        var row  = document.createElement('TR');
        head.appendChild(row);
        foreach(structure, 
            function (name, value) {
            	cell = self.createHeadCell(name, value);
                row.appendChild(cell);
            }
        );
        return head;
    },
    
    createHeadCell: function (name, content) {
        var cell = document.createElement('TH');
        cell.data = {name: name};
        cell.addClass(name);
        cell.innerHTML = content;
        return cell;
    },
    
    setHeadTitles: function (titles) {
        var cells = self.head.firstTag('TR').childNodes;
        for (var i = 0; i < cells.length; i ++) {
            if (cells[i].tagName && cells[i].tagName.toUpperCase() == 'TH') {
                cells[i].innerHTML = titles[cells[i].data.name];
            }
        }
    },
    
    createBody: function () {
        var body = document.createElement('TBODY');
        return body;
    },

    setRows: function (structure) {
        self.body.removeChildren();
        foreach(structure, function (i, data) {
            var row = self.createRow(data);
            self.body.appendChild(row);
        });
    },
    
    createCell: function (row_id, col_id, content, as_th) {
        var cell = document.createElement(as_th ? 'TH' : 'TD');
        cell.id = self.getCellId(row_id, col_id);
        cell.row = row_id;
        cell.col = col_id;
        cell.data = {row: row_id, col: col_id, content: content};
        cell.addClass(col_id);
        cell.innerHTML = content;
        
        cell.onmouseover = function (event) {
            cell.addClass('cell-over');
        };
        
        cell.onmouseout = function (event) {
            cell.removeClass('cell-over');
        };
        
        return cell;
    },
    
    createRow: function (cells) {
        var row = DOM.element('TR');
        row.id = self.getRowId(cells.id);
        row.data = cells;
        foreach(self.cols, 
            function (key, col) {
                var data = cells[col];
                var cell = null;
                var handler = self['createCell' + col.ucfirst()];
                if (handler instanceof Function) {
                    cell = handler.apply(self, [cells.id, col, data]);
                } else {
                    cell = self.createCell(cells.id, col, data);
                }
                row.appendChild(cell);
            }
        );
        
        row.onmouseover = function () {
            for (var i = 0; i < row.childNodes.length; i ++) {
                row.childNodes[i].addClass('row-over');
            }
        };
        
        row.onmouseout = function () {
            for (var i = 0; i < row.childNodes.length; i ++) {
                row.childNodes[i].removeClass('row-over');
            }
        };
        
        return row;
    },

    getRowId: function (id) {
        return self.id + '-' + id;
    },

    getCellId: function (row_id, col_id) {
        return self.id + '-' + row_id + '-' + col_id;
    },

    getCell: function (row_id, col_id) {
        return ID(self.getCellId(row_id, col_id));
    },

    getRow: function (id) {
        return ID(self.getRowId(id));
    },

    addRow: function (data) {
        var row = self.createRow(data);
        self.body.appendChild(row);

        return row;
    },

    editRow: function (row, data) {
        var row_data = row.data;
        foreach(data, function (i) {
            row_data[i] = data[i];
        });
        var new_row = self.createRow(row_data);
        row.setClassesTo(new_row);
        row.replace(new_row);
        return new_row;
    },

    removeRow: function (row) {
        row.remove();
    },
    
    appendTo: function (node) {
        node.appendChild(self.element);
    }
});