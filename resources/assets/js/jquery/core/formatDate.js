import $ from 'jquery';

$.formatDate = function(string) {
    var date = new Date(string);
    var monthNames = [
        "Jan", "Feb", "Mar",
            "Apr", "May", "Jun", "Jul",
            "Aug", "Sep", "Oct",
            "Nov", "Dec"
        ],
        day = date.getDate(),
        monthIndex = date.getMonth(),
        year = date.getFullYear();

    return monthNames[monthIndex] + ' ' + day + ', ' + year;
};
