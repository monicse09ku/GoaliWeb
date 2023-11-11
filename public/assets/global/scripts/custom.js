// init input for phone with code and flag
// $(".telephone").intlTelInput({
//     initialCountry:"BD",
//     separateDialCode: true
//     //dialCode: "+88",
// });

//$('#iti-0__item-bd').find('.iti__dial-code').text('+88');
//$('.iti__selected-dial-code').text('+88');

var input = document.querySelector("#telephone");
if(input != null){
    var iti = window.intlTelInput(input, {
        initialCountry: "bd",
        separateDialCode: true,
        utilsScript: "../assets/global/plugins/intl-tel-input-master/js/utils.js"
    });

    errorMsg = document.querySelector("#error-msg"),
        validMsg = document.querySelector("#valid-msg");

// here, the index maps to the error code returned from getValidationError - see readme
    var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];

    var reset = function() {
        input.classList.remove("error");
        errorMsg.innerHTML = "";
        errorMsg.classList.add("hide");
        validMsg.classList.add("hide");
    };

// on blur: validate
    input.addEventListener('blur', function() {
        reset();
        if (input.value.trim()) {
            if (iti.isValidNumber()) {
                validMsg.classList.remove("hide");
            } else {
                input.classList.add("error");
                var errorCode = iti.getValidationError();
                errorMsg.innerHTML = errorMap[errorCode];
                errorMsg.classList.remove("hide");
            }
        }
    });

// on keyup / change flag: reset
    input.addEventListener('change', reset);
    input.addEventListener('keyup', reset);
}


//summernote
$(document).ready(function() {
    $('.summernote').summernote({
        height: 170,
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            //['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
});


//User registration show password
$('.show-password').click(function(){
    if($(this).is(":checked") == true){
        $(this).parents('form').find('.password-field').prop("type", "text");
    }
    else{
        $(this).parents('form').find('.password-field').prop("type", "password");
    }
});


//project selection action
$(document).on('click','.project-item .add_to_fav',function(){
    var check_status = $(this).parents('.project-item').find('.project-item-check').val();
    if(check_status == '0'){
        $(this).parents('.project-item').find('.project-item-check').val('1');
        $(this).parents('.project-item').addClass('project_added');
    }
    else{
        $(this).parents('.project-item').find('.project-item-check').val('0');
        $(this).parents('.project-item').removeClass('project_added');
    }
});

//Select shipment date modal
$(window).on('load',function(){
    $('#select_ship_date').modal('show');
});
$('#select_ship_date').on('show.bs.modal', function (e) {
    $('body').addClass('shipment_modal-open');
});
$('#select_ship_date').on('hidden.bs.modal', function (e) {
    $('body').removeClass('shipment_modal-open');
});

//Add for menu click trigger
if($(window).width() < 980){
    $(document).on('click','.page-sidebar-menu>.nav-item>.nav-link',function(){
        $('.menu-toggler').trigger('click');
    });
}

