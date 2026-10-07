
$(document).ready(function() {

    $(document).on('change','#fl3dpm-student',function() {
        if (! this.checked) {
            $("#fl3dpm-student-lead").prop("checked", false)
            $("#fl3dpm-full-access").prop("checked", false)
        }
    })

    $(document).on('change','#fl3dpm-student-lead',function() {
        if (this.checked) {
            $("#fl3dpm-student").prop("checked", true)
        } else {
            $("#fl3dpm-full-access").prop("checked", false)
        }
    })

    $(document).on('change','#fl3dpm-full-access',function() {
        if (this.checked) {
            $("#fl3dpm-student").prop("checked", true)
            $("#fl3dpm-student-lead").prop("checked", true)
        }
    })

})


