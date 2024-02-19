<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white page-sidebar-fixed page-container-bg-solid ">
<!-- BEGIN HEADER -->
<?php
$current_url = $_SERVER['REQUEST_URI'];
$current_url = explode('?',$current_url);
$uri = explode('/',$current_url[0]);
$page = $uri[1];
?>
<div class="page-header navbar navbar-fixed-top">
    <!-- BEGIN HEADER INNER -->
    <div class="page-header-inner ">
        <!-- BEGIN LOGO -->
        <div class="page-logo">
            <a href="{{url('dashboard')}}" class="nav-item item-1  @if($page=='dashboard') active @endif" data-name="dashboard" data-item="1">
                <!--<img style="width: 114%;" src="{{asset('assets/layouts/layout/img/logo.png')}}" alt="logo" class="logo-default" />-->
                <p style="color: white; font-size: 19px; font-weight: 700;     margin: 11px auto;">Goali</p>
            </a>
            <div class="menu-toggler sidebar-toggler">
                <span></span>
            </div>
        </div>
        <!-- END LOGO -->
        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
            <span></span>
        </a>
        <!-- END RESPONSIVE MENU TOGGLER -->
        <!-- BEGIN TOP NAVIGATION MENU -->
        <div class="top-menu">
            <ul class="nav navbar-nav pull-left">
                <li class="dropdown dropdown-user">
                    <a href="javascript:;" class="dropdown-toggle">
                        @if(Session::get('user_photo') !='')
                            <img alt="" class="img-circle profile_image" src="{{asset(Session::get('user_photo'))}}">
                        @else
                            <img alt="" class="img-circle profile_image" src="{{asset('assets/layouts/layout/img/emptyuserphoto.jpg')}}">
                        @endif
                        <span class="username username-hide-on-mobile"> {{Session::get('username')}}</span>
                    </a>
                </li>
            </ul>
            <ul class="nav navbar-nav pull-right">

            </ul>
        </div>
        <!-- END TOP NAVIGATION MENU -->
    </div>
    <!-- END HEADER INNER -->
</div>
<!-- END HEADER -->
<!-- BEGIN HEADER & CONTENT DIVIDER -->
<div class="clearfix"> </div>
<!-- END HEADER & CONTENT DIVIDER -->
<!-- BEGIN CONTAINER -->
<div class="page-container">

    <div class="page-sidebar-wrapper">
        <!-- BEGIN SIDEBAR -->
        <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
        <!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
        <div class="page-sidebar navbar-collapse collapse">
            <!-- BEGIN SIDEBAR MENU -->
            <!-- DOC: Apply "page-sidebar-menu-light" class right after "page-sidebar-menu" to enable light sidebar menu style(without borders) -->
            <!-- DOC: Apply "page-sidebar-menu-hover-submenu" class right after "page-sidebar-menu" to enable hoverable(hover vs accordion) sub menu mode -->
            <!-- DOC: Apply "page-sidebar-menu-closed" class right after "page-sidebar-menu" to collapse("page-sidebar-closed" class must be applied to the body element) the sidebar sub menu mode -->
            <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
            <!-- DOC: Set data-keep-expand="true" to keep the submenues expanded -->
            <!-- DOC: Set data-auto-speed="200" to adjust the sub menu slide up/down speed -->
            <ul class="page-sidebar-menu  page-header-fixed " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200" style="padding-top: 20px">
                <!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
                <li class="sidebar-toggler-wrapper hide">
                    <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
                    <div class="sidebar-toggler">
                        <span></span>
                    </div>
                    <!-- END SIDEBAR TOGGLER BUTTON -->
                </li>

                <li class="nav-item item-1  @if($page=='dashboard') active @endif" data-name="dashboard" data-item="1">
                    <a href="{{url('dashboard')}}" class="nav-link">
                        <i class="icon-home"></i>
                        <span class="title">Dashboard</span>
                        <span class="selected"></span>
                    </a>
                </li>

                <li class="nav-item @if($page=='clients') active @endif">
                    <a href="{{url('clients')}}" class="nav-link">
                        <i class="icon-users"></i>
                        <span class="title">Clients</span>
                        <span class="selected"></span>
                    </a>
                </li>

                <li class="nav-item @if($page=='users') active @endif" data-name="users">
                    <a href="{{url('users')}}" class="nav-link">
                        <i class="icon-users"></i>
                        <span class="title">Users</span>
                        <span class="selected"></span>
                    </a>
                </li>

                <li class="nav-item @if($page=='support_tickets' || $page=='view_support_tickets') active @endif" data-name="support_tickets">
                    <a href="{{url('support_tickets')}}" class="nav-link">
                        <i class="icon-support"></i>
                        <span class="title">Support Tickets</span>
                        <span class="selected"></span>
                    </a>
                </li>

                @if(Session::get('role') == 1 || Session::get('role') == 2)
                <li class="nav-item @if($page=='general_settings' || $page=='items' || $page=='suppliers' || $page=='service_categories' || $page=='service_types' || $page=='package_uoms' || $page=='packages' || $page=='cheque_books' || $page=='parties' || $page=='party_categories' || $page=='banks' || $page=='bank_branches' || $page=='bank_accounts') open @endif">
                    <a href="javascript:;" class="nav-link nav-toggle">
                        <i class="icon-settings"></i>
                        <span class="title">Settings</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub-menu" @if($page=='general_settings' || $page=='terms_condition') style="display: block;" @endif>
                        <li class="nav-item @if($page=='general_settings') active @endif">
                            <a href="{{url('general_settings')}}" class="nav-link">
                                <!--<i class="icon-users"></i>-->
                                <span class="title">General</span>
                                <span class="selected"></span>
                            </a>
                        </li>
                        <li class="nav-item @if($page=='terms_condition') active @endif">
                            <a href="{{url('pages/terms_condition')}}" class="nav-link">
                                <!--<i class="icon-users"></i>-->
                                <span class="title">Terms & Conditions</span>
                                <span class="selected"></span>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                <li class="nav-item  @if($page=='profile') active @endif" data-name="profile" data-item="1">
                    <a href="{{url('profile')}}" class="nav-link">
                        <i class="icon-user"></i>
                        <span class="title">Profile</span>
                        <span class="selected"></span>
                    </a>
                </li>

                <li class="nav-item">
                    <a  href="{{ url('logout') }}" class="nav-link">
                        <i class="icon-logout"></i>
                        <span class="title">Log Out</span>
                        <span class="selected"></span>
                    </a>
                </li>
            </ul>
            <!-- END SIDEBAR MENU -->
            <!-- END SIDEBAR MENU -->
        </div>
        <!-- END SIDEBAR -->
    </div>
