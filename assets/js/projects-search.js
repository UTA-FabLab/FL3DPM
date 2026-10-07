
const DATATABLE_NAME = 'projectSearchList';

$(document).ready(function() {

    $('#' + DATATABLE_NAME).DataTable({
        stateSave: true,
    });

    $("#searchTerm").focus()

    $("#searchMode").on( "change", function() {
        $("#searchTerm").val('')
        $("#searchTerm").focus()
    })

    $("#searchTerm").on( "keypress", function( event ) {
        if ( event.which == 13 ) {
            event.preventDefault()
            $sval = $("#searchTerm").val().trim()
            if ($sval != "") {
                const url = window.location.href.split('?')[0]
                window.location.href = url + '?searchMode=' + $('#searchMode').val() + '&searchTerm=' + $sval
            }
        }
    });

    $("#searchButton").on( "click", function( event ) {
        event.preventDefault()
        $sval = $("#searchTerm").val().trim()
        const url = window.location.href.split('?')[0]
        window.location.href = url + '?searchMode=' + $('#searchMode').val() + '&searchTerm=' + $sval
        return false;
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

    if (document.getElementById(DATATABLE_NAME) != null) {
        searchTerm = $('#' + DATATABLE_NAME).DataTable().search().trim()
        if ((searchTerm != "undefined") && (searchTerm != "")) {
            queryParameters = queryParameters + "&search=" + encodeURIComponent(searchTerm)
        }
    }

    searchMode = $('#searchMode').val()

    if ((searchMode != "undefined") && (searchMode != "")) {
        queryParameters = queryParameters + '&searchMode=' + searchMode
    }

    searchTerm = $('#searchTerm').val()

    if ((searchTerm != "undefined") && (searchTerm != "")) {
        queryParameters = queryParameters + '&searchTerm=' + searchTerm
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


