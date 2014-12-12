include('chart.BaseChart');

window.GoogleApiChart = BaseChart.extend({
    encode: function (values, min, max) {
        min = min || 0;
        var map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.';
        var len = map.length;
        var base = len * len;
        var range =  max - min;
        var data = '';
        foreach(values, function (i, val) {
            // 0    -   XX  -   base
            // min  -   val -   max
            val = new Number(val);
            if (isNaN(val)) {
                val = 0;
            }
            var scaled = Math.floor((val - min) / range * base);
            if (scaled > base - 1) {
                data += '..';
            } else {
                var quotient = Math.floor(scaled / len);
                var remainder = scaled - len * quotient;
                data += map.charAt(quotient) + map.charAt(remainder);
            }
        });
        return data;
    },
    
    draw: function () {
        var values = [];
        var titles = [];
        var max_len = 10;
        foreach(self.data, function (i, row) {
            if (self.data.length < max_len || i % Math.ceil(self.data.length / max_len) == 0) { 
                titles.push(row['title']);
            } else {
                titles.push('');
            }
            row['value'] = new Number(row['value']);
            if (isNaN(row['value'])) {
                row['value'] = 0;
            }
            values.push(Math.round(row['value'], 2));
        });
        
        var max = Math.max.apply(Math, values);
        var min = Math.min.apply(Math, values);

        var options = {};
        var zero_point = min > 0 ? 0 : Math.round(-min / (max - min), 2);
        switch (self.type) {
        case 'line':
            options = {
                cht: 'lc', // type
                chd: 'e:' + self.encode(values, min, max),
                chxl: '0:|' + titles.join('|'),
                chxr: '1,' + min + ',' + max,
                chxt: 'x,y',
                chco: '1f77b4',
                chm: self.data.length < 40 ? 'h,000000,0,' + zero_point + ',1|o,1f77b4,0,-1,6' : 'h,000000,0,' + zero_point + ',1'
            };
            break;
            
        case 'area':
            options = {
                cht: 'lc', // type
                chd: 'e:' + self.encode(values, min, max) + ',' + self.encode([0, 0], min, max),
                chxl: '0:|' + titles.join('|'),
                chxr: '1,' + min + ',' + max,
                chxt: 'x,y',
                chco: '1f77b4',
                chds: min + ',' + max,
                chm: self.data.length < 40 ? 'h,000000,0,' + zero_point + ',1|o,1f77b4,0,-1,6|b,aec7e8,1,0,0' : 'h,000000,0,' + zero_point + ',1|b,aec7e8,1,0,0'
            };
            break;
            
        case 'column':
//            var bar_width = Math.ceil((400 - 10 * String(max).length) / data.length) - 3;
            options = {
                cht: 'bvs', // type
                chd: 'e:' + self.encode(values, min, max),
                chxl: '0:|' + titles.join('|'),
                chxr: '1,' + min + ',' + max,
                chxt: 'x,y',
                chco: '1f77b4',
                chbh: 'a', // even spacing
                chds: min + ',' + max,
                chm: 'h,000000,0,' + zero_point + ',1'
            };
            break;
            
        case 'pie':
            options = {
                cht: 'p', // type
                chd: 't:' + values.join(','),
                chl: 'No|Yes',
                chco: 'aec7e8|1f77b4'
            };
            break;
            
        default:
            return;
        }
        options.chs = self.width + 'x' + self.height;
        options.chtt = self.title;

        var canvas = ID(self.canvas);
        canvas.setChild(DOM.element('img', {
            src: URL.img + 'site/loading24.gif'
        }));
        var img = DOM.element('IMG', {
            src: 'http://chart.apis.google.com/chart?' + Ajax.prepare(options),
            onload: function () {
                canvas.setChild(img);
            },
            onerror: function () {
                canvas.setHTML(LNG.Error);
            }
        });
    }
});