
$(document).on('mouseenter','#customer_id_hidden', function (event) {
    console.log('hover in')
    $("#customer_id").show()
}).on('mouseleave','#customer_id_hidden',  function(){
    console.log('hover out')
    $("#customer_id").hide()
});

