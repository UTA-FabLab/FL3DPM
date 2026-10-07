
// Establish the URL to the keepsession page

    const scriptUrl = document.currentScript.src;
    const keepSessionUrl = scriptUrl.replace('/assets/js/sessionTimeout.js','') + '/keepsession'


$(document).ready(function() {

    const timeoutVal = 1680000;

    const modal = document.getElementById("sessionTimeoutModal")
    const closeBtn = document.getElementById("closeSessionBtn")
    const extendBtn = document.getElementById("extendSessionBtn")

    // Use .close() to hide the dialog
    closeBtn.addEventListener("click", () => {
        modal.close()
    })

    // Use .close() to hide the dialog
    extendBtn.addEventListener("click", () => {
        modal.close()

        fetch(keepSessionUrl)
            .then((response) => {
                if (!response.ok) {
                  throw new Error(`HTTP error: ${response.status}`)
                }
                return response.text()
            })
            .then((text) => {
                console.log(text)
            })
            .catch((error) => {
                console.log('could not fetch')
            });
    })


    // 28 minutes = 1,680,000 milliseconds
    setTimeout(function() {
        modal.showModal()
        $('#extendBtn').focus()
    }, timeoutVal)

});

