import $ from 'jquery';

export default {
    init : function () {
        var el, data, currency_symbol;

        if ($('#product-sales-chart').length > 0) {
            el = $('#product-sales-chart-data');
            currency_symbol = el.data('currency-symbol');
            data = $.parseJSON(el.html());

            window.Morris.Area({
                element   : 'product-sales-chart',
                data      : data,
                xkey      : 'createddatetime',
                ykeys     : ['price'],
                labels    : ['Sales'],
                pointSize : 2,
                hideHover : 'auto',
                resize    : true,
                yLabelFormat: function (y) { return (currency_symbol ? currency_symbol : '$') + y.formatMoney(); },
                lineColors: ['#428bca', '#8064A2'],
                axes: true
            });
        }

        if ($('#member-signup-chart-data').length > 0) {
            data = $.parseJSON($('#member-signup-chart-data').html());

            window.Morris.Area({
                element   : 'member-signup-chart',
                data      : data,
                xkey      : 'month',
                ykeys     : ['total_count'],
                labels    : ['Total Accounts'],
                pointSize : 2,
                hideHover : 'auto',
                resize    : true,
                lineColors: ['#5cb85c', '#8064A2','#F79646','#4F81BD','#C0504D','#9BBB59','#2C4D75','#4BACC6'],
                axes      : false
            });
        }

        if ($('#member-types-chart').length > 0) {
            window.Morris.Donut({
                element   : 'member-types-chart',
                data      : $.parseJSON($('#member-types-chart-data').html()),
                colors    : ['#8064A2','#F79646','#4F81BD','#C0504D','#9BBB59','#2C4D75','#4BACC6'],
                resize    : true
            });
        }

        if ($('#recurring_payment_status_breakdown-chart').length > 0) {
            window.Morris.Donut({
                element   : 'recurring_payment_status_breakdown-chart',
                data      : $.parseJSON($('#recurring_payment_status_breakdown-chart-data').html()),
                colors    : ['#5cb85c', '#f0ad4e', '#d9534f'],
                resize    : true
            });
        }

        if ($('#sponsorships_breakdown-chart').length > 0) {
            window.Morris.Donut({
                element   : 'sponsorships_breakdown-chart',
                data      : $.parseJSON($('#sponsorships_breakdown-chart-data').html()),
                colors    : ['#5cb85c', '#d3d4d3'],
                resize    : true
            });
        }

        if ($('#sponsors_breakdown-chart').length > 0) {
            window.Morris.Donut({
                element   : 'sponsors_breakdown-chart',
                data      : $.parseJSON($('#sponsors_breakdown-chart-data').html()),
                colors    : ['#5cb85c', '#d9534f'],
                resize    : true
            });
        }

        if ($('#30day-sales-chart').length > 0) {
            el = $('#30day-sales-chart-data');
            currency_symbol = el.data('currency-symbol');
            data = $.parseJSON(el.html());

            window.Morris.Area({
                element   : '30day-sales-chart',
                data      : data,
                xkey      : 'order_date',
                ykeys     : ['one_time', 'recurring'],
                labels    : ['One-Time', 'Recurring'],
                pointSize : 2,
                hideHover : 'auto',
                resize    : true,
                yLabelFormat : function (y) { return (currency_symbol ? currency_symbol : '$') + y.formatMoney(); },
                lineColors: ['#428bca', '#8064A2'],
                //axes: false
            });
        }

        if ($('#account-growth-chart').length > 0) {
            data = $.parseJSON($('#account-growth-chart-data').html());

            window.Morris.Area({
                element   : 'account-growth-chart',
                data      : data,
                xkey      : 'created_at',
                ykeys     : ['growth'],
                labels    : ['New Accounts'],
                pointSize : 2,
                hideHover : 'auto',
                resize    : true,
                lineColors: ['#d9534f'],
                axes: false
            });
        }

        if ($('#engagement-chart').length > 0) {
            window.Morris.Donut({
                element   : 'engagement-chart',
                data      : $.parseJSON($('#engagement-chart-data').html()),
                colors    : ['#d9534f', '#f0ad4e', '#428bca', '#5cb85c'],
                resize    : true
            });
        }

        if ($('#best-sellers-chart').length > 0) {
            data = $.parseJSON($('#best-sellers-chart-data').html());

            window.Morris.Bar({
                element   : 'best-sellers-chart',
                data      : data,
                xkey      : 'name',
                ykeys     : ['sales_count'],
                labels    : ['Sales'],
                hideHover : 'auto',
                resize    : true,
                barColors : ['#f0ad4e'],
                axes      : false
            });
        }

        if ($('#category-revenue-chart').length > 0) {
            window.Morris.Donut({
                element: 'category-revenue-chart',
                data: [{
                    label: "General Tithe",
                    value: 232235
                }, {
                    label: "Missions",
                    value: 6434
                }, {
                    label: "Youth",
                    value: 3464
                }, {
                    label: "Evangelism",
                    value: 23346
                }],
                resize: true,
                colors: ['#F79646','#4F81BD','#C0504D','#9BBB59','#8064A2','#2C4D75','#4BACC6'],
                formatter: function (y) { return '$'+y; }
            });
        }
    }
};
