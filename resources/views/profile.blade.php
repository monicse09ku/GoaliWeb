@extends('layouts.master')
@section('title', 'Profile')
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
                        <span>Profile</span>
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
                <div class="col-md-12">
                    <form  id="edit_user_form" method="post" action="" enctype="multipart/form-data">
                        {{csrf_field()}}
                        <input type="hidden" name="user_id" id="user-id" value="{{$user->id}}">
                        <div class="alert alert-success" id="success_message" style="display:none"></div>
                        <div class="alert alert-danger" id="error_message" style="display: none"></div>
                        <!-- BEGIN PROFILE SIDEBAR -->
                        <div class="profile-sidebar">
                            <!-- PORTLET MAIN -->
                            <div class="portlet light profile-sidebar-portlet ">
                                <!-- SIDEBAR USERPIC -->
                                <div class="profile-userpic">
                                    @if($user->photo !='')
                                        <img src="{{asset($user->photo)}}" id="image" class="img-responsive" alt="user image" style="max-height:150px; max-width:150px;">
                                    @else
                                        <img src="{{asset('assets/layouts/layout/img/emptyuserphoto.png')}}" id="image" class="img-responsive" alt="user image" style="max-height:150px; max-width:150px;">
                                    @endif
                                </div>
                                <input type="hidden" name="old_photo_path" value="{{$user->photo}}">

                                <input name="photo" id="image_change_hidden_btn" type="file" class="hidden">
                                <!-- END SIDEBAR USERPIC -->

                                <!-- SIDEBAR BUTTONS -->
                                <div class="profile-userbuttons">
                                    <button id="image_change_btn" type="button" class="btn blue btn-sm">Update Image</button>
                                    <a href="{{url('reset-password')}}" class="btn red btn-sm ajax_item item-5" data-name="reset-password" data-item="5">Reset password</a>
                                </div>
                                <!-- END SIDEBAR BUTTONS -->
                            </div>
                            <!-- END PORTLET MAIN -->
                        </div>
                        <!-- END BEGIN PROFILE SIDEBAR -->
                        <!-- BEGIN PROFILE CONTENT -->
                        <div class="profile-content">
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- BEGIN PORTLET -->
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption caption-md">
                                                <i class="icon-bar-chart theme-font hide"></i>
                                                <span class="caption-subject font-blue-madison bold uppercase">Profile Information</span>
                                            </div>
                                        </div>
                                        <div class="portlet-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for=""><b>Name</b></label>
                                                        <input type="text" class="form-control" name="name" id="name" value="{{$user->name}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for=""><b>Phone Number</b></label>
                                                        <div>
                                                            <input type="text" class="form-control" name="phone" id="phone" onkeyup="this.value=this.value.replace(/[^\d]/,'')" value="{{$user->phone}}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for=""><b>Email Address</b></label>
                                                        <input type="email" class="form-control" name="" id="" value="{{$user->email}}" disabled>
                                                        <input type="hidden" class="form-control" name="email" id="email" value="{{$user->email}}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group text-right">
                                                <button type="submit" class="btn green" id="profile_button">Update</button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END PORTLET -->
                                </div>
                            </div>
                        </div>
                        <!-- END PROFILE CONTENT -->
                    </form>
                </div>
            </div>

        </div>
        <!-- END CONTENT BODY -->
    </div>
    <!-- END CONTENT -->

@endsection

@section('js')

    <script>
        $(document).ready(function(){

        });

        $(document).on('click', '#image_change_btn', function(){
            $('#image_change_hidden_btn').trigger('click');
        });

        $(document).on('change', '#image_change_hidden_btn', function(){
            readURL(this);
        });

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#image')
                        .attr('src', e.target.result)
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        $(document).on("submit", "#edit_user_form", function(event) {
            event.preventDefault();
            show_loader();

            var name = $("#name").val();
            var email = $("#email").val();
            var phone = $("#phone").val();

            var re = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

            var validate = "";

            if (name.trim() == "") {
                validate = validate + "Name is required</br>";
            }
            if (email.trim() == "") {
                validate = validate + "Email is required</br>";
            }
            if(email.trim()!=''){
                if(!re.test(email)){
                    validate = validate+'Email is invalid<br>';
                }
            }
            if (phone.trim() == "") {
                validate = validate + "Phone is required</br>";
            }

            if (validate == "") {
                var formData = new FormData($("#edit_user_form")[0]);
                var url = "{{ url('update_user') }}";

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
                        } else {
                            $("#success_message").hide();
                            $("#error_message").show();
                            $("#error_message").html(data.reason);
                        }
                        setTimeout(function(){
                            $("#success_message").hide();
                        },2000)
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
@endsection

