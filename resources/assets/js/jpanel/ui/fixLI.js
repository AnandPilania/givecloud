import $ from 'jquery';

export default function() {
    $('li:last-child').addClass('last'); // add .last to all lists
    $('li').mouseover(function(){ $(this).addClass('over'); }); // add .over support when hovering any ul, li
    $('li').mouseout(function(){ $(this).removeClass('over'); }); // add .over support when hovering any ul, li
}
