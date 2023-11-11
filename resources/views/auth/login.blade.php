<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title>Login</title>
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

<!--<div class="login-header">-->
<!--    <div class="page-logo">-->
<!--        <a href="{{url('/')}}" class="nav-item ajax_item item-1  " data-name="dashboard" data-item="1">-->
<!--        <img style="width: 100%;" src="{{asset('assets/layouts/layout/img/logo.png')}}" alt="Vujadetec logo" class="logo-default">-->
<!--        </a>-->
<!--    </div>-->
<!--</div>-->

<!-- BEGIN LOGIN -->
<div class="content">
    <!-- BEGIN LOGIN FORM -->
    <form id="login_form" class="login-form" action="{{ url('admin/login') }}" method="post">
        {{csrf_field()}}
        <!-- BEGIN LOGO -->
        <!-- <div class="logo">
            <img src="../../assets/global/img/logo-invert.png" alt="" />
        </div> -->
        <!-- END LOGO -->
        <div class="content-center" style="margin: 20px;">

        </div>
        <div class="content-center">
            <div class="row">
                 <div class="content-center col-12"
                style="text-align: center;
                background: #dde3ec;
                padding-bottom: 1px;
                margin-bottom: 30px;
                margin-left: -15px;
                margin-right: -15px;
                margin-top: -35px;
                padding-top: 15px;">
                    <img style="max-width: 140px;" src="{{asset('assets/layouts/layout/img/gas.png')}}" alt="GAS logo" class="login-logo">
                    <h4 class="form-title uppercase"><strong>Login</strong></h4>
                </div>

            </div>
        </div>

        <div class="alert alert-success" id="success_message" style="display:none"></div>
        <div class="alert alert-danger" id="error_message" style="display: none"></div>
        <div class="form-group">
            <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
            <label class="control-label visible-ie8 visible-ie9">Username</label>
            <input class="form-control form-control-solid placeholder-no-fix" type="text" autocomplete="off" placeholder="Email" name="username" id="username" />        </div>
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9">Password</label>
            <input class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="off" placeholder="password" name="password" id="password" />        </div>
        <div class="form-actions">

            <!--            <label class="rememberme check mt-checkbox mt-checkbox-outline">
                            <input type="checkbox" name="remember" id="remember" value="1" />Remember
                            <span></span>
                        </label>-->
            <button type="submit" class="btn uppercase login_button_lg btn-success" id="login_button">Login</button>

            <!-- <a href="javascript:;" id="forget-password" class="forget-password">Forgot Password?</a> -->
        </div>
        {{--<div class="create-account theme-bg">
            <p>
                <a href="{{ url('registration') }}" id="register-btn" class="uppercase">Register here</a>
            </p>
        </div>--}}
    </form>
    <!-- END LOGIN FORM -->
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
{{--<script src="{{asset('assets/global/scripts/custom.js')}}" type="text/javascript"></script>--}}
<!-- END GLOABL CUSTOM SCRIPTS -->

<script type="text/javascript">
    $(document).on("submit", "#login_form", function(event) {
        event.preventDefault();

        $("#login_button").attr("disabled", true);

        var username = $("#username").val();
        var password = $("#password").val();

        var validate = "";

        if (username.trim() == "") {
            validate = validate + "Username is required</br>";
        }
        if (password.trim() == "") {
            validate = validate + "Password is required</br>";
        }

        if (validate == "") {
            var formData = new FormData($("#login_form")[0]);
            var url = "{{ url('post_login') }}";

            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                success: function(data) {
                    if (data.status == 200) {
                        $("#success_message").show();
                        $("#error_message").hide();
                        $("#success_message").html(data.reason);
                        window.location.href="{{url('dashboard')}}";
                    } else {
                        $("#login_button").attr("disabled", false);
                        $("#success_message").hide();
                        $("#error_message").show();
                        $("#error_message").html(data.reason);
                    }
                },
                error: function(data) {
                    $("#login_button").attr("disabled", false);

                    $("#success_message").hide();
                    $("#error_message").show();
                    $("#error_message").html(data.reason);
                },
                cache: false,
                contentType: false,
                processData: false
            });
        } else {
            $("#login_button").attr("disabled", false);
            $("#success_message").hide();
            $("#error_message").show();
            $("#error_message").html(validate);
        }
    });
</script>

</body>

</html>
