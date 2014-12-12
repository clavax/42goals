include('chart.BaseChart');

window.GoogleVisChart = BaseChart.extend({
    __construct: function () {
//        window.loadGoogleVisualiation = function () {
////            DOM.element('script', {
////                type: 'text/javascript',
////                html: "google.load('gdata', '1'))",
////                appendTo: document.getElementsByTagName('head')[0]
////            });
////            DOM.element('script', {
////                type: 'text/javascript',
////                html: "google.load('visualization', '1', {packages: ['corechart']}))",
////                appendTo: document.getElementsByTagName('head')[0]
////            });
//             google.load('visualization', '1', {packages: ['corechart']});            
//        };
//        var url = 'http://www.google.com/jsapi';
//        Mohawk.Loader.importJs(url);
    },
    
    draw: function () {
    
        var len = 0;
        
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', 'Value');
        
        if (self.type == 'pie') {
            data.addRows(2);
            data.setValue(0, 0, 'Yes');
            data.setValue(0, 1, 1 * self.data[1].value);
            data.setValue(1, 0, 'No');
            data.setValue(1, 1, 1 * self.data[0].value);
        } else {
            foreach(self.data, function (i, row) {
                len ++;
                data.addRows(1);
                row.value = new Number(row.value);
                if (isNaN(row.value)) {
                    row.value = 0;
                }
                row.value = Math.round(row.value, 2);
                data.setValue(i, 0, row.title);
                data.setValue(i, 1, row.value);
            });
        }
        
        var options = {
            width: self.width - 1,
            height: self.height - 1,
            chartArea: {
                left: 40,
                top: 20,
                width: self.width - 80,
                height: self.height - 50
            },
            legend: 'none', 
            enableTooltip: true,
            colors: ['#1f77b4'],
            axisTitlesPosition: 'in',
            hAxis: {
                slantedText: false,
                textStyle: {
                    fontSize: 10
                }
            },
            vAxis: {
                textStyle: {
                    fontSize: 10
                }
            }
        };
        
        switch (self.type) {
        default:
        case 'column':
            var Graph = new google.visualization.ColumnChart(ID(self.canvas));
            Graph.draw(data, options);
            break;

        case 'line':
            var Graph = new google.visualization.LineChart(ID(self.canvas));
            options.lineWidth = 1;
            options.pointSize = len < 40 ? 4 : 0;
            options.curveType = self.interpolate ? 'function' : 'none';
            Graph.draw(data, options);
            break;
            
        case 'area':
            var Graph = new google.visualization.AreaChart(ID(self.canvas));
            options.lineWidth = 1;
            options.curveType = self.interpolate ? 'function' : 'none';
            options.pointSize = len < 40 ? 4 : 0;
            Graph.draw(data, options);
            break;
            
        case 'pie':
            var Graph = new google.visualization.PieChart(ID(self.canvas));
            options.colors = ['#1f77b4', '#aec7e8'];
            Graph.draw(data, options);
            break;
        }
    }
});
