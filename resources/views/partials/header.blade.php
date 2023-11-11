<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
    <!--<![endif]-->
    <!-- BEGIN HEAD -->

    <head>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-175399171-1"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-175399171-1');
        </script>

        <meta charset="utf-8" />
        <title>@yield('title')</title>
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
        <!-- BEGIN THEME GLOBAL STYLES -->
        <link href="{{asset('assets/global/css/components-rounded.min.css')}}" rel="stylesheet" id="style_components" type="text/css" />
        <link href="{{asset('assets/global/css/plugins.min.css')}}" rel="stylesheet" type="text/css" />
        <!-- END THEME GLOBAL STYLES -->

        <!-- BEGIN GLOBAL PAGE STYLES -->
        <link href="{{asset('assets/pages/css/login.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/pages/css/profile.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/global/plugins/intl-tel-input-master/css/intlTelInput.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}" rel="stylesheet" type="text/css" />
        <!-- END GLOBAL PAGE STYLES-->

        <!-- START DATA TABLE STYLES-->
        <link href="{{asset('assets/global/plugins/datatables/dataTables.bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
        <!-- END DATA TABLE STYLES-->

        <!-- BEGIN THEME LAYOUT STYLES -->
        <link href="{{asset('assets/layouts/layout/css/layout.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/layouts/layout/css/themes/blue.min.css')}}" rel="stylesheet" type="text/css" id="style_color" />
        <link href="{{asset('assets/layouts/layout/css/custom.css')}}" rel="stylesheet" type="text/css" />
        <!-- END THEME LAYOUT STYLES -->

        <link href="{{asset('assets/holdon/holdon.min.css')}}" rel="stylesheet" />
        
        <link rel="stylesheet" href="{{asset('assets/global/plugins/bootstrap-select/bootstrap-select.min.css')}}" />
        <link href="{{asset('assets/custom/style.css')}}" rel="stylesheet" />

        <!-- BEGIN SELECT2 PLUGIN STYLES -->
        <link href="{{ asset('assets/global/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
        <!-- END SELECT2 PLUGIN STYLES -->
        
        <!-- BEGIN TIMEPICKER PLUGIN STYLES -->
        <!-- Timepicker documentation  https://github.com/jonthornton/jquery-timepicker#timepicker-plugin-for-jquery -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.14.1/jquery.timepicker.min.css" rel="stylesheet" type="text/css" />
        <!-- BEGIN TIMEPICKER PLUGIN STYLES -->

        <script src="{{asset('assets/global/plugins/jquery.min.js')}}" type="text/javascript"></script>

        <script src="https://code.highcharts.com/highcharts.js"></script>
         <script src="{{asset('assets/global/plugins/bootstrap-select/bootstrap-select.min.js')}}"></script>
        @yield('styles')
    </head>

    <!-- BEGIN SIDEBAR -->
    @include('partials.sidebar')
    <!-- END SIDEBAR -->
