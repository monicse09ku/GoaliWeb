@extends('layouts.master')
@section('title', 'View Ticket')
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
                        <a href="{{url('support_tickets')}}">Tickets</a>
                        <i class=""></i>
                    </li>
                    <li>
                        <span>Details</span>
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
                    <form  id="ticket_form" method="post" action="" enctype="multipart/form-data">
                        {{csrf_field()}}
                        <input type="hidden" name="ticket_id" value="{{$ticket->id}}">
                        <input type="hidden" name="name" value="Goali Support">
                        <div class="alert alert-success" id="success_message" style="display:none"></div>
                        <div class="alert alert-danger" id="error_message" style="display: none"></div>

                        <div class="row">
                            <div class="col-md-12">
                                <!-- BEGIN PORTLET -->

                                <div class="portlet light ">
                                    <div class="portlet-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    @if($ticket->status == 'closed')
                                                    <label for="" class="text-danger"><b>Closed ticket</b></label>
                                                    @else
                                                        <button type="button" class="btn yellow submit-btn" id="close_button" onclick="close_ticket({{$ticket->id}})"><i class="icon-close"></i>Mark this ticket closed</button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                {{--Adding reply--}}
                                @if($ticket->status == 'active')
                                <div class="portlet light ">
                                    <div class="portlet-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for=""><b>Message</b></label>
                                                    <textarea name="message" id="message"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group text-right">
                                            <button type="submit" class="btn green submit-btn" id="profile_button">Send Reply</button>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- Showing ticket replies --}}
                                @foreach($ticket->replies as $reply)
                                    <div class="portlet light ">
                                        <div class="portlet-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for=""><b>From: </b></label>
                                                        {{$reply->name}}
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for=""><b>Date: </b></label>
                                                        {{date('d/m/Y h:i a', strtotime($ticket->created_at))}}
                                                    </div>
                                                </div>
                                                {{--<div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for=""><b>Email:</b></label>
                                                        {{$reply->email}}
                                                    </div>
                                                </div>--}}
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        {{--<label for=""><b>Message</b></label>--}}
                                                        <div>
                                                            {!!$reply->message !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                {{-- Showing main ticket message--}}
                                <div class="portlet light ">
                                    <div class="portlet-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for=""><b>From: </b></label>
                                                    {{$ticket->name}}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for=""><b>Date: </b></label>
                                                    {{date('d/m/Y h:i a', strtotime($ticket->created_at))}}
                                                </div>
                                            </div>
                                            {{--<div class="col-md-6">
                                                <div class="form-group">
                                                    <label for=""><b>Email:</b></label>
                                                    {{$ticket->email}}
                                                </div>
                                            </div>--}}
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    {{--<label for=""><b>Message</b></label>--}}
                                                    <div>
                                                        {!!$ticket->message !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END PORTLET -->
                            </div>
                        </div>
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
            $('#message').summernote();
        });

        function close_ticket(id){
            $(".warning_message").text('Are you sure you want to close this ticket? This can not be undone.');
            $("#warning_modal").modal('show');
            $( "#warning_ok" ).on('click',function() {
                show_loader();
                var url = "{{ url('support_tickets/close') }}";
                $.ajax({
                    type: "POST",
                    url: url,
                    data: {ticket_id:id,'_token':'{{csrf_token()}}'},
                    success: function(data) {
                        hide_loader();
                        if (data.status == 200) {
                            location.reload();
                        } else {
                            show_error_message(data.reason);
                        }
                    },
                    error: function(data) {
                        hide_loader();
                        show_error_message(data);
                    }
                });
            });
        }

        $(document).on("submit", "#ticket_form", function(event) {
            event.preventDefault();
            show_loader();

            var message = $("#message").val();

            var validate = "";

            if (message.trim() == "") {
                validate = validate + "Message is required</br>";
            }

            if (validate == "") {
                var formData = new FormData($("#ticket_form")[0]);
                var url = "{{ url('support_tickets/send_reply') }}";

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
                            setTimeout(function(){
                                location.reload();
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
@endsection

