@extends('layouts.master')
@section('title', 'Support Tickets')
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
                        <span>Support Tickets</span>
                    </li>
                </ul>
                <div class="page-toolbar">

                </div>
            </div>

            <!-- BEGIN PAGE TITLE-->
            <!-- <h3 class="page-title">
                clients
            </h3> -->
            <!-- END PAGE TITLE-->
            <!-- END PAGE BAR -->
            <!-- END PAGE HEADER-->

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="page-top-header">
                                    <h3 class="page-title">All Support Tickets</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="portlet light ">
                        <div class="row">
                            <div class="col-md-8">
                                <form id="search_form" method="get" action="">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <select class="form-control" name="is_read" id="is_read" >
                                                <option value="" @if(request('is_read')=='') selected @endif>All</option>
                                                <option value="Unread" @if(request('is_read')=='Unread') selected @endif>Unread</option>
                                                <option value="Read" @if(request('is_read')=='Read') selected @endif>Read</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <a href="{{url('support_tickets')}}" class="btn btn-success">Clear Filter</a>
                            </div>
                        </div>
                    </div>
                    <!-- BEGIN PROFILE SIDEBAR -->
                    <!-- <div class="profile-sidebar">

                        <div class="portlet light profile-sidebar-portlet ">

                        </div>

                    </div> -->
                    <!-- END BEGIN PROFILE SIDEBAR -->
                    <!-- BEGIN PROFILE CONTENT -->
                    <div class="profile-content">
                        <div class="row">
                            <div class="col-md-12">
                                <!-- BEGIN PORTLET -->
                                <div class="portlet light ">
                                    <!-- <div class="portlet-title" style="border: 1px solid #000; padding: 10px;">

                                    </div> -->
                                    <div class="portlet-body">
                                        <div class="row">
                                            <table id="client_table" class="table table-striped table-bordered table-hover data-table focus-table">
                                                    <thead>
                                                    <tr>
                                                        <th class="text-center">Date</th>
                                                        <th class="text-center">Name</th>
                                                        <th class="text-center">Email</th>
                                                        <th class="text-center">Message</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($tickets as $key=>$ticket)
                                                        <tr id="ticket_{{$ticket->id}}">
                                                            <td class="text-center">{{date('d/m/Y h:i a',strtotime($ticket->created_at))}}</td>
                                                            <td class="text-center">{{$ticket->name}}</td>
                                                            <td class="text-center">{{$ticket->email}}</td>
                                                            <td class="text-center">{{(strlen($ticket->message) > 80) ? substr($ticket->message,0,80).'...' : $ticket->message}}</td>
                                                            <td class="text-center">
                                                                <a class="btn btn-success btn-sm" href="{{url('view_support_tickets',$ticket->id)}}" title="View Details"><i class="icon-eye"></i></a>
                                                                <a class="btn btn-danger btn-sm" href="javascript:void(0)" title="Delete" onclick="delete_ticket({{$ticket->id}})"><i class="icon-trash"></i></a>
                                                                @if($ticket->status == 'active')
                                                                <a class="btn btn-warning btn-sm" href="javascript:void(0)" title="Close" onclick="close_ticket({{$ticket->id}})"><i class="icon-close"></i></a>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>

                                            {{-- Pagination --}}
                                            <div class="">
                                                {{ $tickets->appends($_GET)->links() }}
                                            </div>
                                        </div>
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
    <script>

        function delete_ticket(id){
            $(".warning_message").text('Are you sure you delete this ticket? This can not be undone.');
            $("#warning_modal").modal('show');
            $( "#warning_ok" ).on('click',function() {
                show_loader();
                var url = "{{ url('support_tickets/delete') }}";
                $.ajax({
                    type: "POST",
                    url: url,
                    data: {ticket_id:id,'_token':'{{csrf_token()}}'},
                    success: function(data) {
                        hide_loader();
                        if (data.status == 200) {
                            $('#ticket_'+id).remove();
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
                            $('#ticket_'+id).remove();
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
    </script>
@endsection

