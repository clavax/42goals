include('chart.BaseChart');

window.ProtovisChart = BaseChart.extend({
    grid: function (id, data, w, h, linear_x) {
        var max = false, min = false;
        var labels_length = 0;
        foreach(data, function (i, row) {
            labels_length += row[2].length;
            var value = row[1];
            if (max === false || max < value) {
                max = value;
            }
            if (min === false || min > value) {
                min = value;
            }
        });
        
        // min = Math.min(0, min);
        if (min > 0) {
            min -= Math.ceil(Math.log(max - min) / Math.log(10));
        }
        self.min = min;
        self.max = max;
        
        var x;
        if (linear_x) {
            x = pv.Scale.linear(0, data.length - 1).range(0, w - 70);
        } else {
            x = pv.Scale.ordinal(pv.range(data.length)).splitBanded(0, w - 70, 4/5);
        }
        var y = pv.Scale.linear(min, max).range(0, h - 50);
        
        self.vis = new pv.Panel()
            .def("i", -1)
            .canvas(id)
            .width(w - 70)
            .height(h - 50)
            .bottom(25)
            .left(35)
            .right(35)
            .top(25);
        
        self.vis.add(pv.Rule)
            .data(y.ticks(4))
            .bottom(function(d) {return Math.round(y(d)) - .5})
            .strokeStyle(function (d) {return d == Math.max(0, min) ? '#000' : '#eee'})
            .anchor("left")
        .add(pv.Label)
            .text(function(d) {return (d && d > -10 && d < 10 ? d.toFixed(1) : d)});
        
        var n_labels = Math.floor(w / (labels_length / data.length)) / 10;
        
        self.vis.add(pv.Label)
            .data(data)
            .left(function(d) {return x(d[0])})
            .bottom(y(min))
            .visible(function (d) {return data.length <= n_labels || !(d[0] % Math.round(data.length / n_labels))})
            .textStyle("#000")
            .textBaseline('top')
            .textAlign('left')
            .textMargin(10)
            .text(function (d) {return d[2]});

        self.x = x, self.y = y;
    },
    
    column: function (id, data, w, h) {
        self.grid(id, data, w, h);
        var vis = self.vis, x = self.x, y = self.y, min = self.min, max = self.max;
        
        
        var bar = vis.add(pv.Bar)
            .data(data)
            .left(function(d) {return x(d[0])})
            .width(x.range().band)
            .bottom(function(d) {var bh = y(d[1]); return (d[1] >= 0 ? Math.max(y(0), y(min)) : bh)})
            .height(function(d) {var bh = y(d[1]) - Math.max(y(0), y(min)); return Math.abs(bh) - 1})
//            .title(function(d) {return d[1]})
//            .fillStyle(function() {return (vis.i() == this.index ? '#aec7e8' : '#1f77b4')})
            .fillStyle(function (d) {return (d[1] >= 0 ? pv.color((vis.i() == this.index ? '#aec7e8' : '#1f77b4')).alpha(0.8): pv.color((vis.i() == this.index ? '#ff9896' : '#d62728')).alpha(0.8))})
            .event('mouseover', function() {return vis.i(this.index)})
            .event('mouseout', function() {return vis.i(-1)})
        .add(pv.Label)
            .bottom(function(d) {return y(d[1])})
            .visible(function() {return vis.i() == this.index})
            .text(function(d) {return d[1].toFixed(1) + ': ' + d[3]})
            .textAlign(function() {return (this.index > 0.5 * data.length ? 'right' : 'left')})
            .textStyle("#000000")
            .textMargin(10)
            .font("12px Arial");
//            .textShadow('0.1em 0.1em 0.1em rgb(255, 255, 255)');
        
        vis.render();
    },
    
    line: function (id, data, w, h, interpolate) {
        self.grid(id, data, w, h, true);
        var vis = self.vis, x = self.x, y = self.y, min = self.min, max = self.max, idx = -1;

        var line = vis.add(pv.Line)
            .data(data)
            .left(function(d) {return x(d[0])})
            .bottom(function(d) {return y(d[1]) - 1})
            .lineWidth(1)
            .interpolate(interpolate ? 'cardinal' : 'none')
        .add(pv.Label)
            .bottom(function(d) {return y(d[1])})
            .visible(function(d) {return vis.i() == d[0]})
            .text(function(d) {return d[1].toFixed(1) + ': ' + d[3]})
            .textAlign(function() {return (this.index > 0.5 * data.length ? 'right' : 'left')})
            .textStyle("#000000")
            .textMargin(10)
            .font("12px Arial");
//            .textShadow('0.1em 0.1em 0.1em rgba(0, 0, 0, 0.4)');
        
        if (data.length < 40) {
            line.add(pv.Dot)
                .visible(true)
                .bottom(function(d) {return y(d[1]) - 1})
                .shapeSize(10)
//                .size(10)
                .fillStyle("#1f77b4")
        }
        
        vis.add(pv.Rule)
            .visible(function() {return idx >= 0})
            .left(function() {return x(idx)})
            .top(-4)
            .bottom(-4)
            .strokeStyle("#000");
        
        vis.add(pv.Panel)
            .events("all")
            .event("mousemove", function() { idx = x.invert(vis.mouse().x) >> 0; return vis.i(idx)})
            .event("mouseout", function() { idx = -1; return vis.i(-1)});
        
        vis.render();
    },
    
    area: function (id, data, w, h, interpolate) {
        self.grid(id, data, w, h, true);
        var vis = self.vis, x = self.x, y = self.y, min = self.min, max = self.max, idx = -1, prev;

        var new_data = [];
        var prev;
        foreach(data, function (i, row) {
            var value = row[1];
            if (prev && prev * value < 0) {
                var x2 = row[0], x1 = x2 - 1;
                var y2 = value, y1 = prev;
                var x = x1 - y1 * ((x2 - x1) / (y2 - y1));
                new_data.push([x, 0, '', '']);
            }
            new_data.push(row);
            prev = value;
        });
        
        var area = vis.add(pv.Area)
            .data(new_data)
            .left(function(d) {return x(d[0])})
            .bottom(Math.max(y(0), y(min)))
            .height(function(d) {return y(d[1]) - Math.max(y(0), y(min) )})
            .interpolate(interpolate ? 'cardinal' : 'none')
            .segmented(true)
//            .fillStyle(pv.color('#aec7e8').alpha(0.5))
            .fillStyle(function (d) {return pv.color(d[1] < 0 || (d[1] == 0 && new_data[this.index + 1] && new_data[this.index + 1][1] < 0) ? '#ff9896': '#aec7e8').alpha(0.5)})
//            .title(function(d) {return d[1]});
        
        var line = vis.add(pv.Line)
            .data(new_data)
            .left(function(d) {return x(d[0])})
            .bottom(function(d) {return y(d[1]) - 1})
            .lineWidth(1)
            .segmented(true)
            .strokeStyle(function (d) {return pv.color(d[1] < 0 || (d[1] == 0 && new_data[this.index + 1] && new_data[this.index + 1][1] < 0) ? '#d62728': '#1f77b4').alpha(0.5)})
            .interpolate(interpolate ? 'cardinal' : 'none')
        .add(pv.Label)
            .bottom(function(d) {return y(d[1])})
            .visible(function(d) {return vis.i() == d[0]})
            .text(function(d) {return d[1].toFixed(1) + ': ' + d[3]})
            .textAlign(function() {return (this.index > 0.5 * data.length ? 'right' : 'left')})
            .textStyle("#000000")
            .textMargin(10)
            .font("12px Arial");
//            .textShadow('0.1em 0.1em 0.1em rgba(0, 0, 0, 0.4)');
    
        if (data.length < 40) {
            line.add(pv.Dot)
                .visible(function (d) {return !!d[2]})
                .bottom(function(d) {return y(d[1]) - 1})
//                .size(10)
                .shapeSize(10)
//                .fillStyle("#1f77b4");
                .fillStyle(function (d) {return (d[1] >= 0 ? pv.color('#1f77b4'): pv.color('#d62728'))});
        }

        vis.add(pv.Rule)
            .visible(function() {return idx >= 0})
            .left(function() {return x(idx)})
            .top(-4)
            .bottom(-4)
            .strokeStyle("#000");
        
        vis.add(pv.Panel)
            .events("all")
            .event("mousemove", function() { idx = x.invert(vis.mouse().x) >> 0; return vis.i(idx)})
            .event("mouseout", function() { idx = -1; return vis.i(-1)});

        vis.render();
    },
    
    pie: function (id, data, w, h) {
        var sum = 0;
        var vals = [];
        
        foreach(data, function (i, row) {
            var value = row[1];
            sum += value;
            vals.push(value);
        });
        
        var
            r = (h - 50) / 2,
            a = pv.Scale.linear(0, sum).range(0, 2 * Math.PI);

        var vis = new pv.Panel()
            .canvas(id)
            .width(w)
            .height(h);
        
        vis.add(pv.Wedge)
            .data(vals)
            .bottom(h / 2)
            .left(w / 2)
            .outerRadius(r)
            .angle(a)
            .title(function(d) {p = d / sum * 100; return p.toFixed(2) + '%'})
        .add(pv.Wedge) // invisible wedge to offset label
            .visible(function(d) {return d > .15})
            .innerRadius(r / 6)
            .outerRadius(r)
            .fillStyle(null)
        .anchor("center").add(pv.Label)
            .textAngle(0)
            .textStyle('white')
            .textShadow('0.1em 0.1em 0.1em rgba(0, 0, 0, 0.5)')
            .font('14px Arial')
            .text(function(d) {p = d / sum * 100; return p.toFixed(2) + '%'});

        vis.render();
        
    },
    
    data4protovis: function (grouped) {
        var data = [];
        foreach(grouped, function (i, val) {
            if (isNaN(val.value)) {
                val.value = 0;
            }
            data.push([i, val.value, val.title, val.extended]);
        });
        return data;
    },
    
    draw: function () {
        switch (self.type) {
        case 'column':
            var data = self.data4protovis(self.data);
            self.column(self.canvas, data, self.width, self.height);
            break;
        case 'line':
            var data = self.data4protovis(self.data);
            self.line(self.canvas, data, self.width, self.height, self.interpolate);
            break;
        case 'area':
            var data = self.data4protovis(self.data);
            self.area(self.canvas, data, self.width, self.height, self.interpolate);
            break;
        case 'pie':
            var data = [
                [0, self.data[1].value, 'Yes'],
                [1, self.data[0].value, 'No'],
            ];
            self.pie(self.canvas, data, self.width, self.height);
        }
    }
});