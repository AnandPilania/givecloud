import $ from 'jquery';

export default function(imgSrc) {
    var imgBox = $('<div class="imgBox"><div class="bg"></div><div class="box"><img src="'+imgSrc+'" /><div class="close"></div></div></div>');
    imgBox.find('.close').click(function(){ imgBox.fadeOut(function(){ $(this).remove(); }); });
    imgBox.appendTo(document.body).fadeIn();
}
