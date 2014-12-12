document.addLoader(function () {
    if (document.body.id != 'report-page') {
        return;
    }
    
    Goals = {
		getData: function (id, date) {
    		if (date in Data.data) {
    			return {value: parseFloat(Data.data[date].value)};
    		} else {
    			return {value: undefined};
    		}
    	}
    };
    
    graph = new Singletone ({
    	draw: function (id, type, group, period, w, h) {
	        self.goal = Object.clone(Data.goal);
	        self.setAggregate();
	        self.group = group;
	        self.setPeriod(period);
            var data = [];
	        if (group == 'year') {
	            var grouped = self.getBooleanData();
	            data = [['All time', [grouped[0].value, grouped[1].value], 'All time']];
	        } else {
	            var grouped = self.getGroupedData();
	            if (type == 'pie') {
    	            foreach(grouped, function (i, val) {
    	                data.push([val.extended, [val.value, 1 - val.value], val.title]);
    	            });
                } else {
                    foreach(grouped, function (i, val) {
                        data.push([val.extended, val.value, val.title]);
                    });
	            }
	        }
	        if (type == 'pie') {
	            chart.pie(id, data, w, h);
	        } else if (type == 'bar') {
	            chart.bar(id, data, w, h);
            } else if (type == 'line') {
                chart.line(id, data, w, h);
            }
    	},
    	
    	draw_barline: function (id, w, h) {
            self.goal = Object.clone(Data.goal);
            self.setAggregate();
            
            var data = {bar: [], line: []};
            // get bar data
            self.group = 'month';
            self.setPeriod('year');
            var grouped = self.getGroupedData();
            foreach(grouped, function (i, val) {
                data.bar.push([val.extended, val.value, val.title]);
            });
            
            // get line data
            self.group = 'day';
            self.setPeriod('year');
            var grouped = self.getGroupedData();
            foreach(grouped, function (i, val) {
                data.line.push([val.extended, val.value, val.title, val.month]);
            });
            
            chart.barline(id, data, w, h);
    	},
    	
    	draw_stacked_area: function (id, w, h) {
    	    self.goal = Object.clone(Data.goal);
    	    self.setAggregate();
    	    
    	    var data = [];
    	    self.group = 'day';
    	    foreach([7, 8], function (i, m) {
                self.start = Date.fromString('2010-' + m.toString().pad(2, '0') + '-01');
                self.end = self.start.addDay(self.start.getDaysInMonth() - 1);
                var grouped = self.getGroupedData();
                var data2 = [];
                foreach(grouped, function (j, val) {
                    data2.push([val.extended, val.value, val.title, val.month]);
                });
                data.push(data2);
    	    });
    	    
    	    chart.stacked_area(id, data, w, h);
    	},
    	
	    setPeriod: function (period) {
	        // set period
	        var earliest = Date.fromString(Object.keys(Data.data).sort()[0]);
	        var start;
	        var end = Date.fromString(Data.today).addDay(7);
	        var today = new Date();
	        if (end.gt(today)) {
	            end = today;
	        }
	            
	        switch (period) {
	        case 'week':
	            start = end.addDay(-7);
	            break;
	            
	        default:
	        case 'month':
	            start = end.addDay(-31);
	            break;
	            
	        case 'quarter':
	            start = end.addDay(-122);
	            break;
	            
	        case 'year':
	            start = end.addDay(-366);
	            break;
	        }
	        if (start.lt(earliest)) {
	            start = earliest;
	        }
	        
	        // group data
	        self.start = start;
	        self.end = end;
	    },
	    
	    setAggregate: function () {
	        var aggregate = function () {};
	        if (self.goal.type == 'boolean') {
	            self.goal.aggregate = 'boolean';
	        }
	        switch (self.goal.aggregate) {
	        default:
	        case 'sum':
	            aggregate = function (array) {
	                var sum = 0;
	                for (var i = 0; i < array.length; i ++) {
	                    sum += array[i];
	                }
	                return sum;
	            };
	            break;
	            
	        case 'min':
	            aggregate = function (array) {
	                var min;
	                for (var i = 0; i < array.length; i ++) {
	                    if (min == undefined || min > array[i]) {
	                        min = array[i];
	                    }
	                }
	                return min;
	            };
	            break;
	            
	        case 'max':
	            aggregate = function (array) {
	                var max = undefined;
	                for (var i = 0; i < array.length; i ++) {
	                    if (max == undefined || max < array[i]) {
	                        max = array[i];
	                    }
	                }
	                return max;
	            };
	            break;
	            
	        case 'avg':
	            aggregate = function (array) {
	                var sum = 0;
	                for (var i = 0; i < array.length; i ++) {
	                    sum += array[i];
	                }
	                return sum / i;
	            };
	            break;
	            
	        case 'boolean':
	            aggregate = function (array) {
	                var yes = 0;
	                var no = 0;
	                for (var i = 0; i < array.length; i ++) {
	                    if (array[i] === 1) {
	                        yes ++;
	                    } else if (array[i] === 0) {
	                        no ++;
	                    }
	                }
	                return (yes + no > 0) ? yes / (yes + no) : 0;
	            };
	            break;
	        }
	        self.aggregate = aggregate;
	    },
	    
	    getGroupedData: function () {
	        switch (self.group) {
	        default:
	        case 'day':
	            return self.groupByDay();
	            
	        case 'week':
	            return self.groupByWeek();
	            
	        case 'weekday':
	            return self.groupByWeekDay();
	            
	        case 'month':
	            return self.groupByMonth();
	        }
	    },
	    
	    getBooleanData: function () {
	        var values = [
	            {extended: 'no', title: 'no', value: 0, plan: '-'},
	            {extended: 'yes', title: 'yes', value: 0, plan: '-'}
	        ];
	        for (var day = self.start; day.le(self.end); day = day.addDay()) {
	            var value = Goals.getData(self.goal.id, Format.date(day, 'Y-m-d')).value;
	            if (value === 1) {
	                values[1].value ++;
	            } else if (value === 0) {
	                values[0].value ++;
	            }
	        }
	        return values;
	    },
	    
	    getAccumulatedData: function () {
	        var grouped = [];

	        var plans = [];
	        if (!self.goal.day_plans) {
	            if (Data.plan[self.goal.id]) {
	                foreach(Data.plan[self.goal.id], function (i, row) {
	                    var plan = {};
	                    plan.start = Date.fromString(row.startdate);
	                    plan.end = Date.fromString(row.enddate);
	                    plan.period = plan.end.daysFrom(plan.start) + 1;
	                    if (self.goal.aggregate == 'sum') {
	                        plan.value = row.value + ' in ' + plan.period + 'days';
	                    } else {
	                        plan.value = row.value;
	                    }
	                    plans.push(plan);
	                });
	            }
	            self.goal.day_plans = plans;
	        } else {
	            plans = self.goal.day_plans;
	        }
	        
	        var value = 0, min, max;
	        for (var day = self.start; day.le(self.end); day = day.addDay()) {
	        	var actual = Goals.getData(self.goal.id, Format.date(day, 'Y-m-d')).value;
	            if (self.goal.aggregate == '') {
	                value = actual || value;
	            } else {
	                value += actual || 0;
	            }
	            if ((min === undefined || min < actual) && actual !== '') {
	            	min = actual;
	            }
	            if ((max === undefined || max > actual) && actual !== '') {
	            	max = actual;
	            }
	            var row = {};
	            row.value = value;
	            row.title = Format.date(day, 'm/j');
	            row.extended = Format.date(day, 'l, F j');
	            
	            var day_plan = false;
	            foreach(plans, function (i, plan) {
	                if (day.isBetween(plan.start, plan.end) && (!day_plan || day_plan.period > plan.period)) {
	                    day_plan = plan;
	                    return true;
	                }
	            });
	            row.plan = day_plan ? day_plan.value : '-';
	            grouped.push(row);
	        }
	        self.min = min;
	        self.max = max;
	        return grouped;
	    },
	    
	    groupByDay: function () {
	        var grouped = [];

	        var plans = [];
	        if (!self.goal.day_plans) {
	            if (Data.plan[self.goal.id]) {
	                foreach(Data.plan[self.goal.id], function (i, row) {
	                    var plan = {};
	                    plan.start = Date.fromString(row.startdate);
	                    plan.end = Date.fromString(row.enddate);
	                    plan.period = plan.end.daysFrom(plan.start) + 1;
	                    if (self.goal.aggregate == 'sum') {
	                        plan.value = row.value + ' in ' + plan.period + 'days';
	                    } else {
	                        plan.value = row.value;
	                    }
	                    plans.push(plan);
	                });
	            }
	            self.goal.day_plans = plans;
	        } else {
	            plans = self.goal.day_plans;
	        }

	        var min, max;
	        self.start = self.start.addDay(1 - self.start.getDate());
	        self.end.setDate(self.end.getDaysInMonth());
	        for (var day = self.start; day.le(self.end); day = day.addDay()) {
	            var row = {};
	            var day_id = day.getId();
	            var actual = Goals.getData(self.goal.id, day_id).value;
	            
	            row.value = actual || 0;
//	            row.title = Format.date(day, day.getDay() == 1 ? 'M j' : '');
	            row.title = Format.date(day, 'j');
	            row.month = Format.date(day, 'm');
	            row.extended = Format.date(day, 'l, F j');

	            if ((min === undefined || min < actual) && actual !== '') {
	            	min = actual;
	            }
	            if ((max === undefined || max > actual) && actual !== '') {
	            	max = actual;
	            }
	            
	            var day_plan = false;
	            foreach(plans, function (i, plan) {
	                if (day.isBetween(plan.start, plan.end) && (!day_plan || day_plan.period > plan.period)) {
	                    day_plan = plan;
	                    return true;
	                }
	            });
	            row.plan = day_plan ? day_plan.value : '-';
	            grouped.push(row);
	        }
	        
	        self.min = min;
	        self.max = max;
	        
	        return grouped;
	    },
	    
	    groupByWeekDay: function () {
	        var grouped = [
	            {value: [], title: 'Sun', extended: 'Sunday', 'plan': ''},
	            {value: [], title: 'Mon', extended: 'Monday', 'plan': ''},
	            {value: [], title: 'Tue', extended: 'Tueday', 'plan': ''},
	            {value: [], title: 'Wed', extended: 'Wednesday', 'plan': ''},
	            {value: [], title: 'Thu', extended: 'Thursday', 'plan': ''},
	            {value: [], title: 'Fri', extended: 'Friday', 'plan': ''},
	            {value: [], title: 'Sat', extended: 'Saturday', 'plan': ''}
	        ];
	        for (var day = self.start; day.le(self.end); day = day.addDay()) {
	            grouped[day.getDay()].value.push(Goals.getData(self.goal.id, day.getId()).value || 0);
	        }
	        for (var i = 0; i < grouped.length; i ++) {
	            grouped[i].value = self.aggregate(grouped[i].value);
	        }
	        grouped.push(grouped.shift());
	        return grouped;
	    },

	    groupByWeek: function () {
	        var plans = [];
	        if (!self.goal.week_plans) {
	            if (Data.plan[self.goal.id]) {
	                foreach(Data.plan[self.goal.id], function (i, row) {
	                    var period = Date.fromString(row.enddate).daysFrom(Date.fromString(row.startdate)) + 1;
	                    if (period != 7) {
	                        return;
	                    }
	                    var plan = {};
	                    plan.startdate = row.startdate;
	                    plan.value = row.value;
	                    plans.push(plan);
	                });
	            }
	            self.goal.week_plans = plans;
	        } else {
	            plans = self.goal.week_plans;
	        }

	        var grouped = [];
	        var week_start = self.start;
	        var values = [];
	        var prev_month = null;
	        for (var day = self.start, week = day.getWeek(); day.le(self.end); day = day.addDay()) {
	            var value = Goals.getData(self.goal.id, day.getId()).value || 0;
	            if (day.getWeek(1) != week) {
	                var row = {};
	                row.value = self.aggregate(values);
	                var month = Format.date(week_start, 'M');
	                row.title = month != prev_month ? month : '';
	                prev_month = month;
//	                row.title = Format.dateRange(week_start, day.addDay(-1), '-');
	                row.extended = Format.dateRange(week_start, day.addDay(-1), ' &ndash; ');
	                
	                // find plan
	                var week_plan = false;
	                foreach(plans, function (i, plan) {
	                    if (week_start.getId() == plan.startdate) {
	                        week_plan = plan;
	                        return true;
	                    }
	                });
	                row.plan = week_plan ? week_plan.value : '-';
	                
	                grouped.push(row);
	                week = day.getWeek(1);
	                week_start = day;
	                values = [];
	            } else {
	                values.push(value);
	            }
	        }
	        if (values.length) {
	            var row = {};
	            row.value = self.aggregate(values);
                var month = Format.date(week_start, 'M');
                row.title = month != prev_month ? month : '';
//	            row.title = Format.dateRange(week_start, day.addDay(-1), '-');
	            row.extended = Format.dateRange(week_start, day.addDay(-1), ' &ndash; ');

	            // find plan
	            var week_plan = false;
	            foreach(plans, function (i, plan) {
	                if (week_start.getId() == plan.startdate) {
	                    week_plan = plan;
	                    return true;
	                }
	            });
	            row.plan = week_plan ? week_plan.value : '-';
	            
	            grouped.push(row);
	        }
	        return grouped;
	    },
	    
	    groupByMonth: function () {
	        var plans = [];
	        if (!self.goal.week_plans) {
	            if (Data.plan[self.goal.id]) {
	                foreach(Data.plan[self.goal.id], function (i, row) {
	                    var start = Date.fromString(row.startdate);
	                    if (start.getDate() != 1) {
	                        return;
	                    }
	                    var period = Date.fromString(row.enddate).daysFrom(start) + 1;
	                    if (period != start.getDaysInMonth()) {
	                        return;
	                    }
	                    
	                    var plan = {};
	                    plan.month = start.getMonth();
	                    plan.value = row.value;
	                    plans.push(plan);
	                });
	            }
	            self.goal.week_plans = plans;
	        } else {
	            plans = self.goal.week_plans;
	        }

	        var grouped = [];
	        var values = [];
	        for (var day = self.start, month = day.getMonth(); day.lt(self.end); day = day.addDay()) {
	            var value = Goals.getData(self.goal.id, day.getId()).value || 0;
	            if (day.getMonth() != month) {
	                var row = {};
	                row.value = self.aggregate(values);
	                row.title = Format.date(day.addDay(-1), 'M');
	                row.extended = Format.date(day.addDay(-1), 'F');

	                // find plan
	                var month_plan = false;
	                foreach(plans, function (i, plan) {
	                    if (month == plan.month) {
	                        month_plan = plan;
	                        return true;
	                    }
	                });
	                row.plan = month_plan ? month_plan.value : '-';
	                
	                grouped.push(row);
	                month = day.getMonth();
	                values = [];
	            } else {
	                values.push(value);
	            }
	        }
	        if (values.length) {
	            var row = {};
	            row.value = self.aggregate(values);
	            row.title = Format.date(day.addDay(-1), 'M');
	            row.extended = Format.date(day.addDay(-1), 'F');

	            // find plan
	            var month_plan = false;
	            foreach(plans, function (i, plan) {
	                if (month == plan.month) {
	                    month_plan = plan;
	                    return true;
	                }
	            });
	            row.plan = month_plan ? month_plan.value : '-';

	            grouped.push(row);
	        }        
	        return grouped;
	    }    	
    });
    
    chart = {
		pie: function (id, data, w, h) {
//	    	var
//		        w = 400,
//		        h = 400;
	    	
	    	var vis = new pv.Panel()
		    		.canvas(id)
			        .width(w)
			        .height(h);
		        
	        var pie = vis.add(pv.Wedge)
	        		.data([])
				    .bottom(w / 2)
				    .left(w / 2)
			        .angle(function(d) {return d * 2 * Math.PI});
	
	        var r = 1, p = 0, q = w / 2 / data.length, g = 1;
	        foreach(data, function (i, val) {
	        	p = r;
	        	r += q;
	        	
	        	var t = 1, a = 0;
	        	
	            pie.add(pv.Wedge)
		    		.data(pv.normalize(val[1]))
			        .innerRadius(p)
			        .outerRadius(r - g)
			        .title(function (d) {return d})
	            
	            .add(pv.Label)
			    	.left(w / 2)
			    	.bottom((r + p) / 2 + w / 2 - 10)
	//		    	.left((r + p) / 2 + w / 2 - 10)
	//		    	.bottom(w / 2 - 12)
	            	.textAngle(0)
	            	.textStyle('#fff')
	            	.text(function(d) {if (t) {t = 0; return val[0];} else {return '';}});
	        });
		    
		    vis.root.render();
	    	
	    },
	    
	    line: function (id, data, w, h) {
	        var max, min;
            var n = 0;
            foreach(data, function (i, row) {
                var value = row[1];
                if (max === undefined || max < value) {
                    max = value;
                }
                if (min === undefined || min > value) {
                    min = value;
                }
            });
            
            min = Math.min(0, min);
            
            var
                x = pv.Scale.ordinal(data).splitBanded(0, w, 4/5),
                y = pv.Scale.linear(min, max).range(0, h - 100);
            
            
            var vis = new pv.Panel()
                .canvas(id)
                .width(w)
                .height(h)
                .bottom(20)
                .left(30)
                .right(5)
                .top(5);
            
            vis.add(pv.Rule)
                .data(y.ticks())
                .visible(function() {return !(this.index % 2)})
                .bottom(function(d) {return Math.round(y(d)) - .5})
                .strokeStyle("#eee")
                .anchor("left")
            .add(pv.Label)
                .text(function(d) {return d.toFixed(1)});
//            
//            vis.add(pv.Rule)
//                .data(x.ticks())
//                .visible(function(d) {return d > 0})
//                .left(function(d) {return Math.round(x(d)) - .5})
//                .strokeStyle("#eee")
//                .anchor("bottom")
//            .add(pv.Label)
//                .text(function(d) {return d.toFixed()});

            vis.add(pv.Line)
                .data(data)
                .left(function(d) {return x(d[0])})
                .bottom(function(d) {return y(d[1])})
                .lineWidth(1)
            .add(pv.Dot)
                .size(1);
            
            vis.render();
	    },
	    
        bar: function (id, data, w, h) {
            var max, min;
            var n = 0;
            foreach(data, function (i, row) {
                var value = row[1];
                if (max === undefined || max < value) {
                    max = value;
                }
                if (min === undefined || min > value) {
                    min = value;
                }
            });
            
            min = Math.min(0, min);
            
            /* Sizing and scales. */
            var
                x = pv.Scale.linear(min, max).range(0, w);
                y = pv.Scale.ordinal(data).splitBanded(0, h, 4/5);
            
            var vis = new pv.Panel()
                .canvas(id)
                .width(w)
                .height(h)
                .bottom(20)
                .left(30)
                .right(5)
                .top(5);
             
            var bar = vis.add(pv.Bar)
                .data(data)
                .height(y.range().band)
                .left(0)
                .top(function(d) {return y(d[0])})
                .width(function(d) {return x(d[1])})
                .title(function(d) {return d[1]});

            bar.anchor('left').add(pv.Label)
                .textStyle("#666")
                .data(data)
                .textBaseline('middle')
                .textAlign('right')
                .textMargin(5)
                .text(function (d) {return d[2]});
             
            vis.add(pv.Rule)
                .data(x.ticks())
                .left(function(d) {return Math.round(x(d)) - 0.5})
                .bottom(0)
                .strokeStyle(function(d) {return d == 0 ? '#666' : 'none'})
                .add(pv.Rule)
                .height(5)
                .strokeStyle("#666")
                .anchor("right")
                .add(pv.Label)
                .text(function(d) {return Math.round(d * 100) + '%'})
                .textStyle("#666");
             
            vis.render();
        },
        
        column: function (id, data, w, h) {
            var max, min;
            var n = 0;
            foreach(data, function (i, row) {
                var value = row[1];
                if (max === undefined || max < value) {
                    max = value;
                }
                if (min === undefined || min > value) {
                    min = value;
                }
            });
            
            min = Math.min(0, min);
            
            /* Sizing and scales. */
            var
//              w = 400,
//              h = 300,
            x = pv.Scale.ordinal(data).splitBanded(0, w, 4/5),
            y = pv.Scale.linear(min, max).range(0, h - 100);
            
            var vis = new pv.Panel()
            .canvas(id)
            .width(w)
            .height(h)
            .bottom(20)
            .left(30)
            .right(5)
            .top(5);
            
            var bar = vis.add(pv.Bar)
            .data(data)
            .left(function(d) {return x(d[0])})
            .width(x.range().band)
            .bottom(function(d) {var bh = y(d[1]); return (d[1] >= 0 ? y(min) : -bh)})
            .height(function(d) {var bh = y(d[1]) - y(min); return Math.abs(bh)})
            .title(function(d) {return d[1]});
            
//          bar.add(pv.Label)
//              .textStyle("gray")
//              .bottom(function (d) {var bh = y(d[1]); return (d[1] >= 0 ? bh : -bh - 15) + b})
//              .title(function(d) {return d[1] || ''});
            
            bar.anchor('bottom').add(pv.Label)
//              .bottom(10)
            .textStyle("#666")
            .data(data)
//              .textAngle(-Math.PI / 8)
            .textBaseline('top')
            .textAlign('center')
            .textMargin(5)
            .text(function (d) {return d[2]})
//              .left(function(d) {return x(d[0]) + 20})
//              .text(function (d) {return Format.date(Date.fromString(d[0]), 'j')})
//              .textStyle(function (d) {var dd = Date.fromString(d[0]); return (dd.getDay() in {0: 1, 6: 1} ? 'red' : 'black')})
            //          .bottom(150);
            
            vis.add(pv.Rule)
            .data(y.ticks())
            .bottom(function(d) {return Math.round(y(d)) - 0.5})
            .strokeStyle(function(d) {return d == 0 ? '#666' : 'none'})
            .add(pv.Rule)
            .width(5)
            .strokeStyle("#666")
            .anchor("left")
            .add(pv.Label)
            .text(function(d) {return Math.round(d * 100) + '%'})
            .textStyle("#666");
            
            vis.render();
        },
    
	    barline: function (id, data, w, h) {
            var bar_data = data.bar;
            var line_data = data.line;
            
            var vis = new pv.Panel()
                .canvas(id)
                .width(w)
                .height(h)
                .bottom(20)
                .left(30)
                .right(5)
                .top(5);

            // draw bar
            var data = bar_data;
	    	var max, min;
	    	var n = 0;
		    foreach(data, function (i, row) {
		    	var value = row[1];
		    	if (max === undefined || max < value) {
		    		max = value;
		    	}
		    	if (min === undefined || min > value) {
		    		min = value;
		    	}
		    });
		    
		    min = Math.min(0, min);
		    
		    /* Sizing and scales. */
		    var
		        x2 = pv.Scale.ordinal(data).splitBanded(0, w, 4/5),
		        y2 = pv.Scale.linear(min, max).range(0, h - 100);
		     
		    var bar = vis.add(pv.Bar)
		        .data(data)
		        .left(function(d) {return x2(d[0])})
		        .width(x2.range().band)
		        .bottom(function(d) {var bh = y2(d[1]); return (d[1] >= 0 ? y2(min) : -bh)})
		        .height(function(d) {var bh = y2(d[1]) - y2(min); return Math.abs(bh)})
		        .title(function(d) {return d[1]});
		    
            bar.anchor('bottom').add(pv.Label)
                .textStyle("#666")
                .data(data)
                .textBaseline('top')
                .textAlign('center')
                .textMargin(5)
                .text(function (d) {return d[2]});

            // draw line
            var data = line_data;
            var max = undefined, min = undefined;
            var n = 0;
            foreach(data, function (i, row) {
                var value = row[1];
                if (max === undefined || max < value) {
                    max = value;
                }
                if (min === undefined || min > value) {
                    min = value;
                }
            });
            
            min = Math.min(0, min);
            max *= 2;
            
            var
                x = pv.Scale.ordinal(data).splitBanded(0, w, 8/9),
                y = pv.Scale.linear(min, max).range(0, h - 100);
            
            vis.add(pv.Line)
                .data(data)
                .left(function(d) {return x(d[0])/* + 10 * (d[3] - 3)*/})
                .bottom(function(d) {return y(d[1])})
                .fillStyle("rgb(121,173,210)")
                .lineWidth(2)
                .interpolate('basis')
                .strokeStyle('#eee');

		    vis.render();
	    },
	    
	    stacked_area: function (id, data, w, h) {

            var vis = new pv.Panel()
                .canvas(id)
                .width(w)
                .height(h)
                .bottom(20)
                .left(30)
                .right(5)
                .top(5); 
            

            // draw line
            var max = undefined, min = undefined;
            var n = 0;
            foreach(data, function (i, col) {
                foreach(col, function (j, row) {
                    var value = row[1];
                    if (max === undefined || max < value) {
                        max = value;
                    }
                    if (min === undefined || min > value) {
                        min = value;
                    }
                });
            });
            
            min = Math.min(0, min);
            
            var
//                x = pv.Scale.ordinal(data).splitBanded(0, w, 8/9),
                x = pv.Scale.linear(1, 31).range(0, w),
                y = pv.Scale.linear(min, max).range(0, h);
            
            /* X-axis and ticks. */
            vis.add(pv.Rule)
                .data(x.ticks())
                .visible(function(d) {return d})
                .left(x)
                .bottom(-5)
                .height(5)
              .anchor("bottom").add(pv.Label)
                .text(x.tickFormat);

            var colors = ["#1f77b4", "#ff7f0e", "#2ca02c", "#d62728", "#9467bd",
                          "#8c564b", "#e377c2", "#7f7f7f", "#bcbd22", "#17becf"];
            foreach(data, function (i, row) {
                vis.add(pv.Line)
                    .data(row)
                    .left(function(d) {return x(d[2])})
                    .bottom(function(d) {if (d === undefined) {return 0} else {return y(d[1])}})
                    .strokeStyle(function () {return colors[i]})
                    .interpolate('basis')
                    ;
            });
//            
//            
//            vis.add(pv.Layout.Stack)
//            .layers(data)
//            //.order("inside-out")
//            //.offset("wiggle")
//            .x(function(d) {return x(d[2])})
//            .y(function(d) {if (d === undefined) {return 0} else {return y(d[1])}})
//            .layer
//            .add(pv.Line)
//            .strokeStyle(pv.ramp("#AEC7E8", "#1F77B4").by(function (d) {return (d[3] - 3) / 7}))
//            .interpolate('cardinal')
//            ;
//            
            /* Y-axis and ticks. */
            vis.add(pv.Rule)
                .data(y.ticks(3))
                .bottom(y)
                .strokeStyle(function(d) {return (d ? "rgba(128,128,128,.2)" : "#000")})
              .anchor("left").add(pv.Label)
                .text(y.tickFormat);
            
            vis.render();
	    }
    };
    
//	var data = [];
//    foreach(Data.data, function (i, val) {
//    	data.push([val.date, parseInt(val.value)]);
//    });
//    data[4][1] = -2;
//    bar('graph', data);
    
//    var data = [['Jan', [56, 44]], ['Feb', [64, 36]], ['Mar', [87, 13]], ['Apr', [99, 1]], ['Mai', [89, 11]], ['Jun', [79, 21]], ['Jul', [69, 31]], ['Mar', [87, 13]], ['Apr', [99, 1]], ['Mai', [89, 11]], ['Jun', [79, 21]], ['Jul', [69, 31]]]; 
//    pie('graph', data);
    
    //graph.draw('graph1', 'day', 'month');
//    graph.draw('graph1', 'pie', 'month', 'year', 500, 500);
//    graph.draw('graph1', 'line', 'day', 'year', 500, 500);
    graph.draw_barline('graph1', 500, 250);
    graph.draw_stacked_area('graph2', 200, 100);
//    graph.draw('graph2', 'pie', 'year', 'year', 200, 200);
    graph.draw('graph3', 'bar', 'weekday', 'year', 200, 200);
//    graph.draw('graph4', 'weekday', 'quarter');
});