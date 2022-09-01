@extends('layouts.admin.master')
@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            <i class=" subheader fal fa-poll-h"></i></i> Products <span class='fw-300'></span> <sup
                class='badge badge-primary fw-500'></sup>
            {{-- <small>
            Insert page description or punch line
        </small> --}}
        </h1>
    </div>
    <!-- Your main content goes below here: -->
    <div class="row">
        <div class="col-xl-12">
            <div id="panel-1" class="panel">
                <div class="panel-hdr">
                    <h2>
                        Products <span class="fw-300"><i></i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10"
                            data-original-title="Collapse"></button>
                        <button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip"
                            data-offset="0,10" data-original-title="Fullscreen"></button>
                        {{-- <button class="btn btn-panel" data-action="panel-close" data-toggle="tooltip" data-offset="0,10" data-original-title="Close"></button> --}}
                    </div>
                </div>
                <div class="panel-container show">
                    <button type="button" id="btn-add" class="btn btn-primary float-right m-3" data-toggle="modal"
                        data-target="#default-example-modal">Add Product</button>
                    <div class="panel-content">
                        <div id="categoryData">
                            <table id="service_item-table"
                                class="table table-bordered table-hover table-striped w-100 dataTable dtr-inline">
                                <thead class="bg-primary-600">
                                    <tr>
                                        <th>Id </th>
                                        <th>Service </th>
                                        <th>Name </th>
                                        <th>Image</th>
                                        <th>Active</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('admin.service_item.create')
    </div>
@endsection

@section('page_js')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#btn-add').click(function() {
                $('#catForm').trigger("reset");
                $('#service_id').empty();
                $('#service_item_id').val();
            });

            var table = $('#service_item-table').DataTable({
                responsive: true,
                serverSide: true,
                ajax: "{{ route('admin.service_item.list') }}",
                columns: [{
                        data: 'id',
                        name: 'Id'
                    },
                    {
                        data: 'service_id',
                        name: 'service_name'
                    },
                    {
                        data: 'name',
                        name: 'Name'
                    },
                    {
                        data: 'image',
                        name: 'Image',
                        orderable: false
                    },
                    {
                        data: 'status',
                        name: 'Active',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'Action',
                        orderable: false,
                        searchable: false
                    },
                ],
                lengthChange: true,
                dom: '<"float-left"B><"float-right"f>rt<"row"<"col-sm-4"l><"col-sm-4"i><"col-sm-4"p>>',
                // "<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'f><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'lB>>" +
                // "<'row'<'col-sm-12'tr>>" +
                // "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [{
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        titleAttr: 'Generate PDF',
                        className: 'btn-outline-primary btn-sm mr-1'
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Excel',
                        titleAttr: 'Generate Excel',
                        className: 'btn-outline-primary btn-sm mr-1'
                    },
                    {
                        extend: 'csvHtml5',
                        text: 'CSV',
                        titleAttr: 'Generate CSV',
                        className: 'btn-outline-primary btn-sm mr-1'
                    },
                    {
                        extend: 'copyHtml5',
                        text: 'Copy',
                        titleAttr: 'Copy to clipboard',
                        className: 'btn-outline-primary btn-sm mr-1'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        titleAttr: 'Print Table',
                        className: 'btn-outline-primary btn-sm'
                    }
                ],
                language: {
                    searchPlaceholder: "Search By Name & Service"
                },
            });
            // upload image 

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#category_id').on('change', function() {

                var categoryID = $(this).val();
                let formData = {
                    cat_id: categoryID
                };
                var ajaxurl = "{{ route('admin.service.get.list') }}";
                if (categoryID) {
                    $.ajax({
                        type: 'GET',
                        url: ajaxurl,
                        data: formData,
                        success: (data) => {

                            if (data.status == 'success') {
                                var subcat =  $('#service_id').empty();
                                $.each(data.services, function(id, service) {
                                    subcat.append('<option value ="' + service.id +
                                        '">' + service.name + '</option>');
                                });

                            } 
                        },
                    });
                }
            });
            $('#catForm').submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);

                var ajaxurl = "{{ route('admin.service_item.store') }}";
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: (data) => {
                        if (data.status == 'success') {
                            $('#image').val('');
                            $('#default-example-modal').modal('hide');
                            $('#catForm').trigger("reset");
                            toastr['success']('Saved successfully!');
                            $('#service_item_id').val();
                            table.ajax.reload(null, false);
                        } else {
                            toastr['error'](data.message);
                        }
                    },
                    error: function(response) {
                        toastr['error']('Something went wrong, Please try again!');
                        console.log('Error:', data);
                    }
                });
            });

            $(document).on("click", ".edit-service_item", function() {
                var ajaxurl = $(this).data('url');

                $.ajax({
                    type: "GET",
                    url: ajaxurl,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        $('#name').val(data.data.name);
                        $('#service_item_id').val(data.data.id);
                        
                        $('#amount').val(data.data.amount);
                        $('#description').val(data.data.description);
                        $('#status').val(data.data.status);
                        var subcat =  $('#service_id').empty();
                        $.each(data.services, function(id, service) {
                            subcat.append('<option value ="' + service.id +
                                '">' + service.name + '</option>');
                        });
                        $('#service_id').val(data.data.service_id);
                        $('#category_id').val(data.service.category_id);
                    },
                    error: function(data) {
                        console.log('Error:', data);
                    }
                });

            });

            $(document).on('click', '.active', function() {
                var ajaxurl = $(this).data('url');
                $.ajax({
                    type: "GET",
                    url: ajaxurl,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            toastr['success']('Status changed successfully');
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function(data) {
                        console.log('Error:', data);
                    }
                });
            });

        });
    </script>
@endsection
