jQuery(document).ready(function() {
    jQuery('.breadcrumbs .expandable').each(function() {
        var container = jQuery(this);
        var link = container.find('>a');
        var opener = jQuery('<span class="breadcrumbs-opener">&xdtri;</span>');
        var list = container.find('.expanded');
        
        link.after(opener);
        
        opener.click(function(event) {
            event.preventDefault();
            
            list.toggle();
        });
    });
});