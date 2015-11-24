$(function(){
    initialize_coursebox_state();

    $("div.search-results > div.courses > div.coursebox div.content > div.course-info-wrapper > span," +
        "div.search-results div.coursebox div.summary.itk-collapsible").click(function(){
        var wrapper = null;
        var collapsible = null;
        var __this = $(this);

        if(__this.prop('nodeName') == 'DIV'){
            wrapper = __this;
            collapsible = __this.parent().find('> span');
        }
        else{
            wrapper = __this.parent().find('div.summary');
            collapsible = __this;
        }

        wrapper.toggleClass('itk-collapsible', 200);
        //switch_icon('fa-angle-left', 'fa-angle-up', $(this));
        //$(this).toggleClass('rotate-down');
        toggle_text(collapsible);
    });
});

function initialize_coursebox_state(){
    $('div.search-results > div.courses > div.coursebox div.content > div.course-info-wrapper > div.summary').each(function(){
        var __this = $(this);

        if(calculate_number_of_lines(__this, 3)){
            __this.addClass('itk-collapsible');
        }
        else{
            __this.parent().find('> span').hide();
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
    console.log("Offset: " + element[0].offsetHeight + " | line height: " + parseInt(element.css('lineHeight'), 10));
    return element[0].offsetHeight / parseInt(element.css('lineHeight'), 10) > expected_lines;
}