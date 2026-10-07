$(document).ready(function() {
    $('#dataTable').DataTable({
        stateSave: true,
        columnDefs: [
            { "targets": [2], "searchable": false, "orderable": false, "className": 'dt-right' }
        ]
    });

    $( "#printButton" ).click(function() {
        url = window.location.href.split('?')[0]
        lastChar = url.at(-1)
        if (lastChar == "#") {
            url = url.slice(0, -1)
        }
        window.open(url + collectQueryParameters("om=print"))
        return false
    })

    $( "#exportButton" ).click(function() {
        url = window.location.href.split('?')[0]
        lastChar = url.at(-1)
        if (lastChar == "#") {
            url = url.slice(0, -1)
        }
        window.open(url + collectQueryParameters("om=csv"))
        return false
    })

});


function collectQueryParameters(inQueryParameters = "") {

    queryParameters = ""

    if (document.getElementById('dataTable') != null) {
        searchTerm = $('#dataTable').DataTable().search().trim()
        if ((searchTerm != "undefined") && (searchTerm != "")) {
            queryParameters = queryParameters + "&search=" + encodeURIComponent(searchTerm)
        }
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


