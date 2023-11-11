
</div>
<!-- END CONTAINER -->


<div class="modal fade global-warning error" id="warning_modal">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <!-- <button data-dismiss="modal" aria-label="Close" class="btn btn-xs btn-default btn-circle btn-close">&times;</button> -->
            <div class="modal-body text-center">
                <i class="fa fa-info-circle text-danger fa-3x"></i>
                <p class="warning_message"> Are you sure you want to delete this record? </p>
                <input type="hidden" id="item_id">
                <input type="hidden" id="item_type">
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-light">No</button>
                <button type="submit" data-dismiss="modal" class="btn btn-primary target"  id="warning_ok">Yes</button>
            </div>
        </div>
    </div>
</div>

<!-- alert message START -->
<div class="modal fade alert global-warning" role="dialog" id="alert-modal" style="z-index: 99999">
    <div class="modal-dialog modal-sm modal-dialog-centered">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body text-center">
                <div id="alert-error-msg">
                    <i class="fa fa-exclamation-triangle fa-3x text-danger"></i> <br>  <br>
                    <p class="text-danger"></p>
                </div>

                <div id="alert-success-msg">
                    <i class="fa fa-check-circle text-success fa-3x"></i>
                    <p class="text-success"></p>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" data-dismiss="modal" id="alert-ok">ok</button>
            </div>
        </div>

    </div>
</div>
<!-- alert message End -->

<input type="hidden" id="refreshed" value="no">


<div class="page-footer">
<div class="page-footer-inner text-center w-100"> © All rights reserved to <a target="_blank" href="{{url('/')}}"><strong>Goali</strong></a></div>
<div class="scroll-to-top">
    <i class="icon-arrow-up"></i>
</div>
</div>

<!-- BEGIN FOOTER scripts-->
@include('partials.scripts')
<script>
    $(document).ready(function(){


    });

    function show_success_message($message){
        $('#alert-modal').modal('show');
        $('#alert-error-msg').hide();
        $('#alert-success-msg').show();
        $('#alert-success-msg p').html($message);
    }
    function show_error_message(message){
        $('#alert-modal').modal('show');
        $('#alert-error-msg').show();
        $('#alert-success-msg').hide();
        $('#alert-error-msg p').html(message);
    }

    function show_loader(message=''){
        if(message==''){
            message = 'Please wait while saving all data.....'; // Showing default message
        }
        var options = {
            theme: "sk-cube-grid",
            message: message,
            backgroundColor: "#1847B1",
            textColor: "white"
        };

        HoldOn.open(options);
    }

    function hide_loader(){
        HoldOn.close();
    }

    function adjust_page_height(){
        var content = $('.page-content');
        var sidebar = $('.page-sidebar');
        var body = $('body');
        var height;


        var headerHeight = $('.page-header').outerHeight();
        var footerHeight = $('.page-footer').outerHeight();


        height = App.getViewPort().height - headerHeight - footerHeight;

        content.attr('style', 'min-height:' + height + 'px');
    }

    /*
    * Reloading page on browser back and forth button click
    * */
    $(window).on('popstate', function(event) {
        window.location.reload();
    });

    function renInitTeliphoneValidationForProfile(){
        //Telephone number validation
        var input = document.querySelector("#telephone01");
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
    }

    function reInitDatepicker(){
        $('.datepicker').datepicker({
            format: 'mm/dd/yyyy'
        });
    }
</script>
<!-- END FOOTER scripts-->
@yield('js')

@include('partials.js.common_js')

</body>

</html>
