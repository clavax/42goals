include('chart.ChartData');
// include('chart.BaseChart');
window.ChartFactory = {
    factory: function (type) {
        if (type == 'protovis') {
            include('chart.ProtovisChart');
            return new ProtovisChart;
        } else if (type == 'googleapi') {
            include('chart.GoogleApiChart');
            return new GoogleApiChart;
        } else if (type == 'googlevis') {
            include('chart.GoogleVisChart');
            return new GoogleVisChart;
        } else {
            console.log(type + ' is undefined');
        }
    }
};