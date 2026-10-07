$(document).ready(function() {

    $('#printsList').DataTable({
        stateSave: true
    });

    $( "#showAll" ).change(function() {
        adjustCriteria()
    })

});


function collectQueryParameters(inQueryParameters = "") {

    queryParameters = ""

    if (document.getElementById('prints') != null) {
        searchTerm = $('#prints').DataTable().search().trim()
        if ((searchTerm != "undefined") && (searchTerm != "")) {
            queryParameters = queryParameters + "&search=" + encodeURIComponent(searchTerm)
        }
    }

    if ( $('#showAll').is(':checked') ) {
        queryParameters = queryParameters + "&showAll=Y"
    } else {
        queryParameters = queryParameters + "&showAll=N"
    }

    if (inQueryParameters != "") {
        queryParameters = queryParameters + "&" + inQueryParameters
    }

    if (queryParameters == "") {
        return ""
    }

    queryParameters = "?" + queryParameters.substr(1)

    return queryParameters
}


function adjustCriteria() {
    const url = window.location.href.split('?')[0]
    window.location.href = url + collectQueryParameters()
    return false;
}


