jQuery('.variations_form').on('click', '.swatch', function(e){
   const el = jQuery(this); 
    const select = el.closest('.value').find('.selected');
    const value = el.data('value');
    el.addClass('selected').siblings('.selected').removeClass('selected');
    select.val(value);
    select.change();
});