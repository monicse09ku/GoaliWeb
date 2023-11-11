<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title>Forgot Password</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    <!-- BEGIN Favicon -->
    <link rel="shortcut icon" href="{{asset('assets/global/img/icons/favicon.ico')}}" type="image/x-icon">
    <!-- END Favicon -->
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="{{asset('assets/global/plugins/simple-line-icons/simple-line-icons.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/global/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css')}}" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{asset('assets/global/plugins/morris/morris.css')}}" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="{{asset('assets/global/css/components-rounded.min.css')}}" rel="stylesheet" id="style_components" type="text/css" />
    <link href="{{asset('assets/global/css/plugins.min.css')}}" rel="stylesheet" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->

    <!-- BEGIN GLOBAL PAGE STYLES -->
    <link href="{{asset('assets/pages/css/login.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/global/plugins/intl-tel-input-master/css/intlTelInput.min.css')}}" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL PAGE STYLES-->

    <!-- BEGIN THEME LAYOUT STYLES -->
    <link href="{{asset('assets/layouts/layout/css/layout.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/layouts/layout/css/themes/blue.min.css')}}" rel="stylesheet" type="text/css" id="style_color" />
    <link href="{{asset('assets/layouts/layout/css/custom.css')}}" rel="stylesheet" type="text/css" />
    <!-- END THEME LAYOUT STYLES -->
</head>

<body class=" login">

<!-- BEGIN LOGIN -->
<div class="content">

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form id="reset_password_form" method="POST" class="login-form"  action="{{ url('email_password_link') }}">
        {{csrf_field()}}
            <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}">
    <!-- BEGIN LOGO -->
        <div class="logo">
            <img src="{{asset('assets/layouts/layout/img/logoVujade.jpg')}}" alt="Vujadetec logo" />
        </div>
        <!-- END LOGO -->

        <h3 class="form-title font-theme">Reset Password</h3>

        <div class="alert alert-success" id="success_message" style="">
            Password reset OTP have been sent to your email and phone number. Use that OTP here to reset your password.
        </div>
        <div class="alert alert-danger" id="error_message" style="display: none"></div>

        <div class="form-group">
            <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
            <label class="control-label visible-ie8 visible-ie9">OTP</label>
            <input class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="off" placeholder="OTP" name="otp" id="otp" value="" autofocus/>
        </div>

        <div class="form-group">
            <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
            <label class="control-label visible-ie8 visible-ie9">New Password</label>
            <input class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="off" placeholder="New Password" name="password" id="password" value="" autofocus/>
        </div>

        <div class="form-group">
            <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
            <label class="control-label visible-ie8 visible-ie9">Confirm Password</label>
            <input class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="off" placeholder="Confirm Password" name="conf_password" id="conf_password" value="" autofocus/>
        </div>


        <div class="form-group row mb-0">
            <div class="col-md-12">
                <button type="submit" class="btn uppercase theme-btn pull-right" id="submit_button">Submit</button>
            </div>
        </div>
    </form>
</div>

<!--[if lt IE 9]>
<script src="{{asset('assets/global/plugins/respond.min.js')}}"></script>
<script src="{{asset('assets/global/plugins/excanvas.min.js')}}"></script>
<![endif]-->
<!-- BEGIN CORE PLUGINS -->
<script src="{{asset('assets/global/plugins/jquery.min.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/global/plugins/bootstrap/js/bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/global/plugins/jquery.blockui.min.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js')}}" type="text/javascript"></script>
<!-- END CORE PLUGINS -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="{{asset('assets/global/plugins/moment.min.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/global/plugins/morris/morris.min.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/global/plugins/counterup/jquery.waypoints.min.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/global/plugins/counterup/jquery.counterup.min.js')}}" type="text/javascript"></script>

<!--For chart START-->
<script src="{{asset('assets/global/plugins/flot/jquery.flot.min.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/global/plugins/flot/jquery.flot.categories.min.js')}}" type="text/javascript"></script>
<!--For chart END-->
<!-- END PAGE LEVEL PLUGINS -->

<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="{{asset('assets/global/scripts/app.min.js')}}" type="text/javascript"></script>
<!-- input with country flag & code-->
<script src="{{asset('assets/global/plugins/intl-tel-input-master/js/intlTelInput-jquery.min.js')}}" type="text/javascript"></script>
<!-- For Documentation https://github.com/jackocnr/intl-tel-input#demo-and-examples-->
<!-- END THEME GLOBAL SCRIPTS -->

<!-- BEGIN THEME LAYOUT SCRIPTS -->
<script src="{{asset('assets/layouts/layout/scripts/layout.min.js')}}" type="text/javascript"></script>
<!-- END THEME LAYOUT SCRIPTS -->

<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="{{asset('assets/pages/scripts/dashboard.js')}}" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->

<!-- BEGIN GLOABL CUSTOM SCRIPTS -->
<script src="{{asset('assets/global/scripts/custom.js')}}" type="text/javascript"></script>
<!-- END GLOABL CUSTOM SCRIPTS -->

<script type="text/javascript">

</script>

</body>

<script>
    $(document).on("submit", "#reset_password_form", function(event) {
        event.preventDefault();

        $('#submit_button').prop('disabled',true);

        var otp = $("#otp").val();
        var password = $("#password").val();
        var conf_password = $("#conf_password").val();

        var validate = "";

        if (otp.trim() == "") {
            validate = validate + "OTP is required</br>";
        }
        if (password.trim() == "") {
            validate = validate + "Password is required</br>";
        }
        if(password.trim()!=conf_password.trim()){
            validate = validate+'Password and confirm password does not matched<br>';
        }

        if (validate == "") {
            var formData = new FormData($("#reset_password_form")[0]);
            var url = "{{ url('reset_password') }}";

            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                success: function(data) {
                    if (data.status == 200) {
                        $("#success_message").show();
                        $("#error_message").hide();
                        $("#success_message").html(data.reason);
                        setTimeout(function(){
                            window.location.href="{{url('login')}}";
                        },2000)
                    } else {
                        $('#submit_button').prop('disabled',false);
                        $("#success_message").hide();
                        $("#error_message").show();
                        $("#error_message").html(data.reason);
                    }
                },
                error: function(data) {
                    $('#submit_button').prop('disabled',false);
                    $("#success_message").hide();
                    $("#error_message").show();
                    $("#error_message").html(data.reason);
                },
                cache: false,
                contentType: false,
                processData: false
            });
        } else {
            $('#submit_button').prop('disabled',false);
            $("#success_message").hide();
            $("#error_message").show();
            $("#error_message").html(validate);
        }
    });
</script>

</html>
