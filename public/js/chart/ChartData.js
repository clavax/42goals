window.ChartData = new Singletone({
    setGoalId: function (id) {
        self.setGoal(self.getGoalById(id));
    },
    
    setGoal: function (goal) {
        self.goal = goal;
        self.setAggregate();
    },
    
    setGroup: function (group) {
        self.group = group;
    },
    
    setPeriod: function (period) {
        var today = new Date();
        var first = Object.keys(Data.data[self.goal.id]).sort()[0];
        var earliest = first ? Date.fromString(first) : today;
        var start;
        var end = today;
            
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
            
        case 'all':
            start = earliest;
            break;
        }
        if (start.lt(earliest)) {
            start = earliest;
        }
        
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
    
    getGoalById: function (id) {
        var goal = false;
        foreach(Data.goals, function (i, data) {
            if (data.id == id) {
                goal = Object.clone(data);
                return false;
            }
        });
        
        return goal;
    },
    
    data4protovis: function (grouped) {
        var data = [];
        foreach(grouped, function (i, val) {
            data.push([i, val.value, val.title, val.extended]);
        });
        return data;
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
            if (self.goal.type == 'time' || self.goal.type == 'timer') {
                actual /= 3600;
            }
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
            row.title = Format.date(day, 'n/j');
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
                        plan.value = [row.value, plan.period];
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

        var min, max, prev = 0;
        for (var day = self.start; day.le(self.end); day = day.addDay()) {
            var row = {};
            var day_id = day.getId();
            var actual = Goals.getData(self.goal.id, day_id).value;
            if (self.goal.type == 'time' || self.goal.type == 'timer') {
                actual /= 3600;
            }
            
            var value = actual || (self.take_last_value ? prev : 0);
            
            row.value = value;
            row.title = Format.date(day, 'n/j');
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
            prev = value;
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
        var prev = 0;
        for (var day = self.start; day.le(self.end); day = day.addDay()) {
            var value = Goals.getData(self.goal.id, day.getId()).value || (self.take_last_value ? prev : 0);
            if (self.goal.type == 'time' || self.goal.type == 'timer') {
                value /= 3600;
            }
            grouped[day.getDay()].value.push(value);
            prev = value;
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
        var prev = 0;
        for (var day = self.start, week = day.getWeek(); day.le(self.end); day = day.addDay()) {
            var value = Goals.getData(self.goal.id, day.getId()).value || (self.take_last_value ? prev : 0);
            if (self.goal.type == 'time' || self.goal.type == 'timer') {
                value /= 3600;
            }
            if (day.getWeek(1) != week) {
                var row = {};
                row.value = self.aggregate(values);
                row.title = Format.dateRange(week_start, day.addDay(-1), '-', {d: 'j', m: 'M'});
                row.extended = Format.dateRange(week_start, day.addDay(-1), '-', {d: 'j', m: 'M'});
                
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
                values = [value];
            } else {
                values.push(value);
            }
            prev = value;
        }
        if (values.length) {
            var row = {};
            row.value = self.aggregate(values);
            row.title = Format.dateRange(week_start, day.addDay(-1), '-', {d: 'j', m: 'M'});
            row.extended = Format.dateRange(week_start, day.addDay(-1), '-', {d: 'j', m: 'M'});

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
        var prev = 0;
        for (var day = self.start, month = day.getMonth(); day.lt(self.end); day = day.addDay()) {
            var value = Goals.getData(self.goal.id, day.getId()).value || (self.take_last_value ? prev : 0);
            if (self.goal.type == 'time' || self.goal.type == 'timer') {
                value /= 3600;
            }
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
                values = [value];
            } else {
                values.push(value);
            }
            prev = value;
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