@extends('layouts.admin.master')

@section('content')
    <ol class="breadcrumb page-breadcrumb">
        {{-- <li class="breadcrumb-item"><a href="javascript:void(0);">SmartAdmin</a></li>
    <li class="breadcrumb-item">Datatables</li>
    <li class="breadcrumb-item active">Basic</li> --}}
        <li class="position-absolute pos-top pos-right d-none d-sm-block"><span class="js-get-date"></span></li>
    </ol>
    <div class="subheader">
        <h1 class="subheader-title">
            <i class='subheader-icon fal fa-tachometer'></i>Dashboard<span class='fw-300'></span>
        </h1>
        {{-- <div class="subheader-block d-lg-flex align-items-center">
        <div class="d-flex mr-4">
            <div class="mr-2">
                <span class="peity-donut" data-peity="{ &quot;fill&quot;: [&quot;#967bbd&quot;, &quot;#ccbfdf&quot;],  &quot;innerRadius&quot;: 14, &quot;radius&quot;: 20 }">7/10</span>
            </div>
            <div>
                <label class="fs-sm mb-0 mt-2 mt-md-0">New Sessions</label>
                <h4 class="font-weight-bold mb-0">70.60%</h4>
            </div>
        </div>
        <div class="d-flex mr-0">
            <div class="mr-2">
                <span class="peity-donut" data-peity="{ &quot;fill&quot;: [&quot;#2196F3&quot;, &quot;#9acffa&quot;],  &quot;innerRadius&quot;: 14, &quot;radius&quot;: 20 }">3/10</span>
            </div>
            <div>
                <label class="fs-sm mb-0 mt-2 mt-md-0">Page Views</label>
                <h4 class="font-weight-bold mb-0">14,134</h4>
            </div>
        </div>
    </div> --}}
    </div>
    <div class="row">
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('admin.service_item') }}" >
            <div class="p-3 bg-success-300 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $data->total_service_item }}
                        <small class="m-0 l-h-n">Total Products</small>
                    </h3>
                </div>
                <i class="fal fa-poll-h position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1"
                    style="font-size:6rem"></i>
            </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('admin.customer') }}" >
            <div class="p-3 bg-warning-400 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $data->total_customer }}
                        <small class="m-0 l-h-n">Total Customers</small>
                    </h3>
                </div>
                <i class="fal fa-user-crown position-absolute pos-right pos-bottom opacity-15  mb-n1 mr-n4"
                    style="font-size: 6rem;"></i>
            </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('admin.staff') }}" >
            <div class="p-3 bg-danger-200 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $data->total_staff }}
                        <small class="m-0 l-h-n">Total Staff</small>
                    </h3>
                </div>
                <i class="fal fa-users position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n4"
                    style="font-size: 6rem;"></i>
            </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('admin.category') }}" >
            <div class="p-3 bg-info-200 rounded overflow-hidden position-relative text-white mb-g">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">

                        {{ $data->total_category }}
                        <small class="m-0 l-h-n">Total Categories</small>
                    </h3>
                </div>
                <i class="fal fa-cubes position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n4"
                    style="font-size: 6rem;"></i>
            </div>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div id="panel-1" class="panel">
                <div class="panel-hdr">
                    <h2>
                        Users
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content bg-subtlelight-fade">
                        <div id="js-checkbox-toggles" class="d-flex mb-3">
                            <div class="custom-control custom-switch mr-2">
                                <input type="checkbox" class="custom-control-input" name="gra-0" id="gra-0" checked="checked">
                                <!-- <label class="custom-control-label" for="gra-0">Users</label> -->
                            </div>
                            {{-- <div class="custom-control custom-switch mr-2">
                                <input type="checkbox" class="custom-control-input" name="gra-1" id="gra-1" checked="checked">
                                <label class="custom-control-label" for="gra-1">Actual Profit</label>
                            </div>
                            <div class="custom-control custom-switch mr-2">
                                <input type="checkbox" class="custom-control-input" name="gra-2" id="gra-2" checked="checked">
                                <label class="custom-control-label" for="gra-2">User Signups</label>
                            </div> --}}
                        </div>
                        <div id="flot-toggles" class="w-100 mt-4" style="height: 300px"></div>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="col-lg-6">
        <div id="panel-2" class="panel panel-locked" data-panel-sortable data-panel-collapsed data-panel-close>
            <div class="panel-hdr">
                <h2>
                    Returning <span class="fw-300"><i>Target</i></span>
                </h2>
            </div>
            <div class="panel-container show">
                <div class="panel-content poisition-relative">
                    <div class="p-1 position-absolute pos-right pos-top mt-3 mr-3 z-index-cloud d-flex align-items-center justify-content-center">
                        <div class="border-faded border-top-0 border-left-0 border-bottom-0 py-2 pr-4 mr-3 hidden-sm-down">
                            <div class="text-right fw-500 l-h-n d-flex flex-column">
                                <div class="h3 m-0 d-flex align-items-center justify-content-end">
                                    <div class='icon-stack mr-2'>
                                        <i class="base base-7 icon-stack-3x opacity-100 color-success-600"></i>
                                        <i class="base base-7 icon-stack-2x opacity-100 color-success-500"></i>
                                        <i class="fal fa-arrow-up icon-stack-1x opacity-100 color-white"></i>
                                    </div>
                                    $44.34 / GE
                                </div>
                                <span class="m-0 fs-xs text-muted">Increased Profit as per redux margins and estimates</span>
                            </div>
                        </div>
                        <div class="js-easy-pie-chart color-info-400 position-relative d-inline-flex align-items-center justify-content-center" data-percent="35" data-piesize="95" data-linewidth="10" data-scalelength="5">
                            <div class="js-easy-pie-chart color-success-400 position-relative position-absolute pos-left pos-right pos-top pos-bottom d-flex align-items-center justify-content-center" data-percent="65" data-piesize="60" data-linewidth="5" data-scalelength="1" data-scalecolor="#fff">
                                <div class="position-absolute pos-top pos-left pos-right pos-bottom d-flex align-items-center justify-content-center fw-500 fs-xl text-dark">78%</div>
                            </div>
                        </div>
                    </div>
                    <div id="flot-area" style="width:100%; height:300px;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div id="panel-3" class="panel panel-locked" data-panel-sortable data-panel-collapsed data-panel-close>
            <div class="panel-hdr">
                <h2>
                    Effective <span class="fw-300"><i>Marketing</i></span>
                </h2>
            </div>
            <div class="panel-container show">
                <div class="panel-content poisition-relative">
                    <div class="pb-5 pt-3">
                        <div class="row">
                            <div class="col-6 col-xl-3 d-sm-flex align-items-center">
                                <div class="p-2 mr-3 bg-info-200 rounded">
                                    <span class="peity-bar" data-peity="{&quot;fill&quot;: [&quot;#fff&quot;], &quot;width&quot;: 27, &quot;height&quot;: 27 }">3,4,5,8,2</span>
                                </div>
                                <div>
                                    <label class="fs-sm mb-0">Bounce Rate</label>
                                    <h4 class="font-weight-bold mb-0">37.56%</h4>
                                </div>
                            </div>
                            <div class="col-6 col-xl-3 d-sm-flex align-items-center">
                                <div class="p-2 mr-3 bg-info-300 rounded">
                                    <span class="peity-bar" data-peity="{&quot;fill&quot;: [&quot;#fff&quot;], &quot;width&quot;: 27, &quot;height&quot;: 27 }">5,3,1,7,9</span>
                                </div>
                                <div>
                                    <label class="fs-sm mb-0">Sessions</label>
                                    <h4 class="font-weight-bold mb-0">759</h4>
                                </div>
                            </div>
                            <div class="col-6 col-xl-3 d-sm-flex align-items-center">
                                <div class="p-2 mr-3 bg-success-300 rounded">
                                    <span class="peity-bar" data-peity="{&quot;fill&quot;: [&quot;#fff&quot;], &quot;width&quot;: 27, &quot;height&quot;: 27 }">3,4,3,5,5</span>
                                </div>
                                <div>
                                    <label class="fs-sm mb-0">New Sessions</label>
                                    <h4 class="font-weight-bold mb-0">12.17%</h4>
                                </div>
                            </div>
                            <div class="col-6 col-xl-3 d-sm-flex align-items-center">
                                <div class="p-2 mr-3 bg-success-500 rounded">
                                    <span class="peity-bar" data-peity="{&quot;fill&quot;: [&quot;#fff&quot;], &quot;width&quot;: 27, &quot;height&quot;: 27 }">6,4,7,5,6</span>
                                </div>
                                <div>
                                    <label class="fs-sm mb-0">Clickthrough</label>
                                    <h4 class="font-weight-bold mb-0">19.77%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="flotVisit" style="width:100%; height:208px;"></div>
                </div>
            </div>
        </div>
    </div> --}}
    </div>
@endsection

@section('page_js')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script type="text/javascript">
        $(document).ready(function()
        {
    
            /* init datatables */
            $('#dt-basic-example').dataTable(
            {
                responsive: true,
                dom: "<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'f><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    {
                        extend: 'colvis',
                        text: 'Column Visibility',
                        titleAttr: 'Col visibility',
                        className: 'btn-outline-default'
                    },
                    {
                        extend: 'csvHtml5',
                        text: 'CSV',
                        titleAttr: 'Generate CSV',
                        className: 'btn-outline-default'
                    },
                    {
                        extend: 'copyHtml5',
                        text: 'Copy',
                        titleAttr: 'Copy to clipboard',
                        className: 'btn-outline-default'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fal fa-print"></i>',
                        titleAttr: 'Print Table',
                        className: 'btn-outline-default'
                    }
    
                ],
                columnDefs: [
                    {
                        targets: -1,
                        title: '',
                        orderable: false,
                        render: function(data, type, full, meta)
                        {
    
                            /*
                            -- ES6
                            -- convert using https://babeljs.io online transpiler
                            return `
                            <a href='javascript:void(0);' class='btn btn-sm btn-icon btn-outline-danger rounded-circle mr-1' title='Delete Record'>
                                <i class="fal fa-times"></i>
                            </a>
                            <div class='dropdown d-inline-block dropleft '>
                                <a href='#'' class='btn btn-sm btn-icon btn-outline-primary rounded-circle shadow-0' data-toggle='dropdown' aria-expanded='true' title='More options'>
                                    <i class="fal fa-ellipsis-v"></i>
                                </a>
                                <div class='dropdown-menu'>
                                    <a class='dropdown-item' href='javascript:void(0);'>Change Status</a>
                                    <a class='dropdown-item' href='javascript:void(0);'>Generate Report</a>
                                </div>
                            </div>`;
                                
                            ES5 example below:	
    
                            */
                            return "\n\t\t\t\t\t\t<a href='javascript:void(0);' class='btn btn-sm btn-icon btn-outline-danger rounded-circle mr-1' title='Delete Record'>\n\t\t\t\t\t\t\t<i class=\"fal fa-times\"></i>\n\t\t\t\t\t\t</a>\n\t\t\t\t\t\t<div class='dropdown d-inline-block dropleft'>\n\t\t\t\t\t\t\t<a href='#'' class='btn btn-sm btn-icon btn-outline-primary rounded-circle shadow-0' data-toggle='dropdown' aria-expanded='true' title='More options'>\n\t\t\t\t\t\t\t\t<i class=\"fal fa-ellipsis-v\"></i>\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t<div class='dropdown-menu'>\n\t\t\t\t\t\t\t\t<a class='dropdown-item' href='javascript:void(0);'>Change Status</a>\n\t\t\t\t\t\t\t\t<a class='dropdown-item' href='javascript:void(0);'>Generate Report</a>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>";
                        },
                    },
    
                ]
    
            });
    
    
            /* flot toggle example */
            var flot_toggle = function()
            {
    
                var data = [
                {
                    label: "Users",
                    data: JSON.parse(`<?php echo $data->chart_data; ?>`),
                    color: color.info._400,
                    bars:
                    {
                        show: true,
                        align: "center",
                        barWidth: 30 * 30 * 60 * 20 * 80,
                        lineWidth: 0,
                        fillColor: {
                            colors: [color.primary._500, color.primary._900]
                        },
                        fillColor:
                        {
                            colors: [
                            {
                                opacity: 0.9
                            },
                            {
                                opacity: 0.1
                            }]
                        }
                    },
                    highlightColor: 'rgba(255,255,255,0.3)',
                    shadowSize: 0
                },
                ]
    
                var options = {
                    grid:
                    {
                        hoverable: true,
                        clickable: true,
                        tickColor: 'rgba(0,0,0,0.05)',
                        borderWidth: 1,
                        borderColor: 'rgba(0,0,0,0.05)'
                    },
                    tooltip: true,
                    tooltipOpts:
                    {
                        cssClass: 'tooltip-inner',
                        defaultTheme: false,
    
                    },
                    xaxis:
                    {
                        mode: "time",
                        tickColor: 'rgba(0,0,0,0.05)',
                    },
                    yaxes:
                    {
                        tickColor: 'rgba(0,0,0,0.05)',
                        tickFormatter: function(val, axis)
                        {
                            return "$" + val;
                        },
                        max: 1200,
                        
    
                    }
    
                };
    
                var plot2 = null;
    
                function plotNow()
                {
                    var d = [];
                    $("#js-checkbox-toggles").find(':checkbox').each(function()
                    {
                        if ($(this).is(':checked'))
                        {
                            d.push(data[$(this).attr("name").substr(4, 1)]);
                        }
                    });
                    if (d.length > 0)
                    {
                        if (plot2)
                        {
                            plot2.setData(d);
                            plot2.draw();
                        }
                        else
                        {
                            plot2 = $.plot($("#flot-toggles"), d, options);
                        }
                    }
    
                };
    
                $("#js-checkbox-toggles").find(':checkbox').on('change', function()
                {
                    plotNow();
                });
                plotNow()
            }
            flot_toggle();
            /* flot toggle example -- end*/
    
            /* flot area */
            var flotArea = $.plot($('#flot-area'), [
            {
                data: dataSet1,
                color: color.success._200,
    
            }],
            {
                // series:
                // {
                //     lines:
                //     {
                //         show: true,
                //         lineWidth: 2,
                //         fill: true,
                //         fillColor:
                //         {
                //             colors: [
                //             {
                //                 opacity: 0
                //             },
                //             {
                //                 opacity: 0.5
                //             }]
                //         }
                //     },
                //     shadowSize: 0
                // },
                // points:
                // {
                //     show: true,
                // },
                // legend:
                // {
                //     noColumns: 1,
                //     position: 'nw'
                // },
                // grid:
                // {
                //     hoverable: true,
                //     clickable: true,
                //     borderColor: '#ddd',
                //     tickColor: 'rgba(0,0,0,0.05)',
                //     aboveData: true,
                //     borderWidth: 0,
                //     labelMargin: 5,
                //     backgroundColor: 'transparent'
                // },
                // yaxis:
                // {
                //     tickLength: 1,
                //     min: 0,
                //     max: 15,
                //     color: '#eee',
                //     tickColor: 'rgba(0,0,0,0.05)',
                //     font:
                //     {
                //         size: 0,
                //         color: '#999'
                //     },
                // },
                // xaxis:
                // {
                //     tickLength: 1,
                //     color: '#eee',
                //     tickColor: 'rgba(0,0,0,0.05)',
                //     font:
                //     {
                //         size: 10,
                //         color: '#999'
                //     }
                // }
    
            });
        });
    </script>
@endsection
