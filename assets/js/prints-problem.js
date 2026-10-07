
$(document).ready(function() {

    $(document).on('change','#print_problem',function() {
        showHideNoteDiv()
    })

    showHideNoteDiv()
})

function showHideNoteDiv()
{
    if ( $('#print_problem').val() == "O" ) {
        $('#noteDiv').show()
    } else {
        $('#noteDiv').hide()
    }    
}

