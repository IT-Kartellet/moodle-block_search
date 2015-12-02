$(function(){
    initialize_coursebox_state();

    $(".search-results > div.courses > div.coursebox .content .course-info-wrapper span.read-more," +
        ".search-results div.coursebox .summary.itk-collapsible").click(function(){

        var wrapper = null;
        var collapsible = null;
        var __this = $(this);

        if(__this.prop('nodeName') == 'TR'){
            wrapper = __this;
            collapsible = __this.parent().find('span.read-more');
        }
        else{
            wrapper = __this.parent().parent().parent().find('.summary');
            collapsible = __this;
        }

        wrapper.toggleClass('itk-collapsible', 200);
        //switch_icon('fa-angle-left', 'fa-angle-up', $(this));
        //$(this).toggleClass('rotate-down');
        toggle_text(collapsible);
    });
});

function initialize_coursebox_state(){
    $('.search-results > div.courses > div.coursebox .content .course-info-wrapper .summary').each(function(){
        var __this = $(this);
        if(calculate_number_of_lines(__this, 3)){
            __this.addClass('itk-collapsible');
        }
        else{
            __this.parent().find('span.read-more').hide();
        }
    });
}

function toggle_text(element){
    if(element.hasClass('read-more')){
        element.removeClass('read-more');
        element.addClass('read-less');
        element.html('read less');
    }
    else{
        element.removeClass('read-less');
        element.addClass('read-more');
        element.html('read more');
    }
}

//Whether a DOM element has more lines than expected
function calculate_number_of_lines(element, expected_lines){
    return element[0].offsetHeight / parseInt(element.css('lineHeight'), 10) > expected_lines;
}