@extends('layouts.master')
@section('title', 'Users')
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
                        <i class="fa fa-circle"></i>
                    </li>
                    <li>
                        <span>Users</span>
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
                <div class="col-md-12 col-sm-12">
                    <!-- BEGIN PORTLET-->
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="icon-share font-red-sunglo hide"></i>
                                <span class="caption-subject font-dark bold uppercase">Users</span>
                            </div>
                            <div class="actions" id="action_buttons">
                                <a href="{{url('users/create')}}" class="btn btn-transparent theme-btn btn-circle btn-sm" title="Create New User" id="create_new_user">Create New User</a>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table id="user_manage_table" class="table table-striped table-bordered table-hover data-table focus-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach($users as $key=>$user){
                                    ?>
                                    <tr id="user_{{$user->id}}">
                                        <td>{{$key+1}}</td>
                                        <td>{{$user->name}}</td>
                                        <td>{{$user->phone}}</td>
                                        <td>{{$roles[$user->role]}}</td>
                                        <td class="text-center">
                                            <a class="btn btn-success btn-sm" href="{{url('users',$user->id)}}" title="Edit"><i class="icon-pencil"></i></a>
                                            <a class="btn btn-danger btn-sm" href="javascript:void(0)" title="Delete" onclick="delete_user({{$user->id}})"><i class="icon-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- END PORTLET-->
                </div>
            </div>

        </div>
        <!-- END CONTENT BODY -->
    </div>
    <!-- END CONTENT -->

@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#user_manage_table').DataTable({
                "paging":   true,
                "lengthChange": false,
                "info":     false,
                "searching": true,
            });
        });

        function delete_user(id){
            $(".warning_message").text('Are you sure you delete this user? ');
            $("#warning_modal").modal('show');
            $( "#warning_ok" ).on('click',function() {
                event.preventDefault();

                show_loader();

                var url = "{{ url('users/delete')}}";
                $.ajax({
                    type: "POST",
                    url: url,
                    data: {user_id:id,'_token':'{{ csrf_token() }}'},
                    async: false,
                    success: function (data) {
                        hide_loader();

                        if(data.status == 200){
                            $('#warning_modal').modal('hide');
                            $('#user_'+id).remove();
                        }
                        else{
                            show_error_message(data);
                        }
                    },
                    error: function (data) {
                        show_error_message('Something went wrong.');
                    }
                });
            });
        }

    </script>
@endsection

