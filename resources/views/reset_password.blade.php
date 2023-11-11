@extends('layouts.master')
@section('title', 'Reset Password')
@section('content')

    <!-- BEGIN CONTENT -->
    <div class="page-content-wrapper">
        <!-- BEGIN CONTENT BODY -->
        <div class="page-content">
            <!-- BEGIN PAGE HEADER-->
            <!-- BEGIN PAGE BAR -->
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li>
                        <a href="{{url('/')}}">Home</a>
                        <i class=""></i>
                    </li>
                    <li>
                        <a href="{{url('profile')}}">Profile</a>
                        <i class=""></i>
                    </li>
                    <li>
                        <span>Reset Password</span>
                    </li>
                </ul>
                <div class="page-toolbar">

                </div>
            </div>

            <!-- BEGIN PAGE TITLE-->
            <!-- <h3 class="page-title"> Projects
                <small>dashboard &amp; statistics</small>
            </h3> -->
            <!-- END PAGE TITLE-->
            <!-- END PAGE BAR -->
            <!-- END PAGE HEADER-->

            <div class="row mt-3">
                <div class="col-md-6 col-md-offset-3">
                    <!-- BEGIN PROFILE CONTENT -->
                    <div class="profile-content">
                        <div class="row">
                            <div class="col-md-12">
                                <!-- BEGIN PORTLET -->
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption caption-md">
                                            <i class="icon-bar-chart theme-font hide"></i>
                                            <span class="caption-subject font-blue-madison bold uppercase">Reset Password</span>
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <form id="reset_password_form" method="post" action="">
                                            {{csrf_field()}}
                                            <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}">

                                            <div class="alert alert-success" id="success_message" style="display:none"></div>
                                            <div class="alert alert-danger" id="error_message" style="display: none"></div>

                                            <div class="form-group">
                                                <label for=""><b>Current password</b></label>
                                                <input type="password" class="form-control password-field" name="current_password" id="current_password" value="">
                                            </div>

                                            <div class="form-group">
                                                <label for=""><b>New password</b></label>
                                                <input type="password" class="form-control password-field" name="password" id="password" value="">
                                            </div>

                                            <div class="form-group">
                                                <label for=""><b>Confirm password</b></label>
                                                <input type="password" class="form-control password-field" name="confirm_password" id="confirm_password" value="">
                                            </div>
                                            <div class="form-group margin-top-20 margin-bottom-20">
                                                <label class="mt-checkbox mt-checkbox-outline mb-0">
                                                    <input type="checkbox" class="show-password" name="show_password" /> Show password
                                                    <span></span>
                                                </label>
                                            </div>

                                            <div class="form-group text-right">
                                                <button type="submit" class="btn green">Reset</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- END PORTLET -->
                            </div>
                        </div>
                    </div>
                    <!-- END PROFILE CONTENT -->
                </div>
            </div>

        </div>
        <!-- END CONTENT BODY -->
    </div>
    <!-- END CONTENT -->
@endsection

@section('js')
    <!-- Scripts for registration START-->
    <script>
        $(document).ready(function(){

        });

        $(document).on("submit", "#reset_password_form", function(event) {
            event.preventDefault();
            show_loader();

            var current_password = $("#current_password").val();
            var password = $("#password").val();
            var confirm_password = $("#confirm_password").val();
            var from = $('#from').val();

            var validate = "";

            if (current_password.trim() == "") {
                validate = validate + "Current password is required</br>";
            }
            if (password.trim() == "") {
                validate = validate + "Password is required</br>";
            }
            if (password.trim() != "" && password.trim() != confirm_password.trim()) {
                validate = validate + "Password and confirm password not matched</br>";
            }

            if (validate == "") {
                var formData = new FormData($("#reset_password_form")[0]);
                var url = "{{ url('update_password') }}";

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    success: function(data) {
                        hide_loader();
                        if (data.status == 200) {
                            $("#success_message").show();
                            $("#error_message").hide();
                            $("#success_message").html(data.reason);
                            $('#reset_password_form')[0].reset();
                            setTimeout(function(){
                                window.location.href="{{url('profile')}}";
                            },1000)
                        } else {
                            $("#success_message").hide();
                            $("#error_message").show();
                            $("#error_message").html(data.reason);
                        }
                    },
                    error: function(data) {
                        hide_loader();
                        $("#success_message").hide();
                        $("#error_message").show();
                        $("#error_message").html(data);
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            } else {
                hide_loader();
                $("#success_message").hide();
                $("#error_message").show();
                $("#error_message").html(validate);
            }
        });

    </script>
    <!-- Scripts for registration END-->
@endsection

