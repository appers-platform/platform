$(document).ready(function(){
    $('.partialhide_solution').each(function(){
        var variables = $$.getVar('__solutions_partialhide' + $(this).data('instid'));
        var slideHeight = variables['height'];
        var $this = $(this);
        var $wrap = $this.children(".wrap");
        var defHeight = $wrap.outerHeight();
        
        if (defHeight >= slideHeight) {
            var $readMore = $this.find(".read-more");
            $wrap.css("height", slideHeight + "px");
            $readMore.append($("<a href='#'></a>").text(variables['title_more']));
            $readMore.children("a").bind("click", function(event) {
                var curHeight = $wrap.height();
                if (!$this.data('expand')) {
                    $wrap.animate({
                        height: defHeight
                    }, "normal");
                    $(this).text(variables['title_less']);
                    $this.data('expand', true);
                } else {
                    $wrap.animate({
                        height: slideHeight
                    }, "normal");
                    $(this).text(variables['title_more']);
                    $this.data('expand', false);
                }
                return false;
            });
        }
    });
});