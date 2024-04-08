@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Checkin Assets
    @parent
@stop

{{-- Page content --}}
@section('content')
    <style>
        .input-group {
            padding-left: 0px !important;
        }
    </style>
    <div class="row">
        <!-- left column -->
        <div class="col-md-7">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Bulk Checkin Assets </h3>
                </div>
                <div class="box-body">
                    <form class="form-horizontal" method="post" action="" autocomplete="off">
                    {{ csrf_field() }}
                        @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user', 'required'=>'true'])
                    <!-- Checkout/Checkin Date -->
                        <div class="form-group {{ $errors->has('checkin_at') ? 'error' : '' }}">
                            {{ Form::label('name','Checkin Date', array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group date col-md-5" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-end-date="0d">
                                    <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="checkin_at" id="checkin_at" value="{{ old('checkin_at') }}">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                                {!! $errors->first('checkout_at', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('assets') ? 'error' : '' }}">
                            {{ Form::label('name','Assets', array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <select class="form-control select-bulk-checkin-assets" name="assets[]" multiple="multiple">
                                </select>
                                {!! $errors->first('assets', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-md-3 control-label">Bulk asset tag assets</label>
                            <div class="col-md-8" style="padding-top:7px;">
                                <textarea class="form-control" name="bulk_assettag_assets"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label"></label>
                                <div class="col-md-8" style="padding-top: 15px;">
                                    <button type="button" class="btn btn-primary" id="preview-handover-paper">Preview Handover Paper</button>
                                </div>
                        </div>


                        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div id="modal">
                                        <h1 style="text-align: center;">CÔNG TY TNHH SGS VIỆT NAM</h1>
                                        <p style="text-align: center;">Tầng 7, 9, 10 Toà nhà VTC 18 Tam Trinh, Phường
                                            Minh Khai, Quận Hai Bà Trưng, Hà Nội</p>
                                        <h2 style="text-align: center;">Handover Paper</h2>
                                        <p style="text-align: center;">Số: <span id="numberOfReport"></span>
                                        </p>
                                        <table>
                                            <tr>
                                                <th>Handover Details</th>
                                                <th>Information</th>
                                            </tr>
                                            <tr>
                                                <td>Date of Handover:</td>
                                                <td>
                                                    <span id="dateOfHandover"></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Handover From:</td>
                                                <td>
                                                    <span id="handoverFrom"></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Handover To:</td>
                                                <td>
                                                    <span id="handoverTo"></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Reason for Handover:</td>
                                                <td>[Reason for handover, e.g., End of project, Change in management]
                                                </td>
                                            </tr>
                                        </table>
                                        <h2>List of Items Handed Over</h2>
                                        <table id="dynamicTable">
                                            <tr>
                                                <th>Asset_tag</th>
                                                <th>Item Name</th>
                                                <th>Quantity</th>
                                                <th>Description</th>
                                            </tr>
                                            <tr>

                                            </tr>
                                            <!-- Add more items here -->
                                        </table>
                                        <h2>Additional Notes</h2>
                                        <p>[Additional notes about the handover process or any other relevant information]</p>
                                        <div class="signatures">

                                        </div>
                                        <div class="col-md-12">
                                            <h4 style="padding-top: 10px">{{trans('general.sign_tos')}}</h4>
                                            <div id="signature-pad" class="m-signature-pad">
                                                <div
                                                    class="m-signature-pad--body col-md-12 col-sm-12 col-lg-12 col-xs-12">
                                                    <canvas></canvas>
                                                    <div class="m-signature-pad--title--signer"></div>
                                                    <div class="m-signature-pad--title--signer2"></div>
                                                    < <input type="hidden" name="signature_output"
                                                        id="signature_output">
                                                </div>
                                                <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 text-center">
                                                    <button type="button" class="btn btn-sm btn-default clear"
                                                        data-action="clear"
                                                        id="clear_button">{{trans('general.clear_signature')}}</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="button" id="print-button" class="btn btn-default">Save file
                                                PDF</button>
                                        </div>
                                        <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 text-center">
                                            <input type="file" id="pdf-file" name="pdf-file" accept=".pdf">
                                        </div>
                                        <div class="box-footer text-right">
                                            <button type="button" class="btn btn-success" id="upload-PDF">
                                                <i class="fa fa-check icon-white" aria-hidden="true"></i>
                                                {{ trans('general.submit') }}
                                            </button>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                        <!-- <div class="form-group">
                            <label class="col-md-3 control-label">Export handover paper</label>
                            <div class="col-md-8" style="padding-top:7px;">
                                <input type="checkbox" name="export_handover_paper" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Export QR Code list</label>
                            <div class="col-md-8" style="padding-top:7px;">
                                <input type="checkbox" name="export_qr_code" />
                            </div>
                        </div> -->

                </div> <!--./box-body-->
                <div class="box-footer">
                    <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                    <button type="submit" class="btn btn-success pull-right"><i class="fa fa-check icon-white"></i> Check in</button>
                </div>
            </div>
            </form>
        </div> <!--/.col-md-7-->

        <!-- right column -->
        <div class="col-md-5" id="current_assets_box" style="display:none;">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('admin/users/general.current_assets') }}</h3>
                </div>
                <div class="box-body">
                    <div id="current_assets_content">
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
    @include('partials/assets-assigned')

<script>

    $('#myModal').on('shown.bs.modal', function () {
        var wrapper = document.getElementById("signature-pad"),
        canvasWrapper = wrapper.querySelector(".m-signature-pad--body"),
        clearButton = wrapper.querySelector("[data-action=clear]"),
        saveButton = wrapper.querySelector("[data-action=save]"),
        canvas = wrapper.querySelector("canvas"),
        signaturePad;

    function resizeCanvas() {
        var ratio =  Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = -2 + canvasWrapper.offsetWidth * ratio;
        canvas.height = -2+canvasWrapper.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
    }

    // Call resizeCanvas when the modal is shown
    resizeCanvas();

    signaturePad = new SignaturePad(canvas);
        $('#clear_button').on("click", function (event) {
            signaturePad.clear();
        });

        $('#upload-PDF').on("click", function (event) {
            if (signaturePad.isEmpty()) {
                alert("Please provide signature first.");
                return false;
            } else {
                $('#signature_output').val(signaturePad.toDataURL());
            }
        });

        $('#print-button').on("click", function (event) {
            window.print();
        });

    });





$('#preview-handover-paper').on('click', function() {
    // Tạo một form mới
    var form = $('<form>', {
        'method': 'post',
        'action': '{{ route('preview.bulkcheckin') }}'
    });

    // Thêm CSRF token vào form
    form.append($('<input>', {
        'name': '_token',
        'value': '{{ csrf_token() }}',
        'type': 'hidden'
    }));

    // Thu thập dữ liệu từ các trường input, select, và textarea của form hiện tại
    $('.form-horizontal').find('input, select, textarea').each(function() {
        // Tạo một trường input ẩn mới với cùng tên và giá trị
        var input = $('<input>', {
            'type': 'hidden',
            'name': $(this).attr('name'),
            'value': $(this).val()
        });
        // Thêm trường input vào form mới
        form.append(input);
        console.log("Input Name:", $(this).attr('name'), "Value:", $(this).val());

    });

    // Gửi form bằng AJAX
    $.ajax({
        url: form.attr('action'),
        type: form.attr('method'),
        data: form.serialize(), // Serialize dữ liệu của form mới để gửi
        success: function(response) {
            // Xử lý khi yêu cầu thành công
            if(response.iserror == 2)
            {
                $('#errodModal').modal('show');
                $('#ErrorTable tr').not(':first').remove();
                //console.log(response.error_info);   
                //console.log(response.length);
                console.log(response.errorTagsUndeploy);

                for (var i = 0; i < response.lengtherrAssettOtherCompany; i++) {
                    var newRow = '<tr>' +
                        '<td>' + response.errAssettOtherCompany[i] + '</td>' +
                        '<td>' + 'Assets owned by another company' + '</td>' + 

                        '</tr>';
                    $('#ErrorTable').append(newRow);

                }
                for (var i = 0; i < response.lengtherrorTags; i++) {
                    var newRow = '<tr>' +
                        '<td>' + response.errorTagsUndeploy[i] + '</td>' +
                        '<td>' + 'Assets Un-Deployable' + '</td>' + 

                        '</tr>';
                    $('#ErrorTable').append(newRow);

                }
            }else if (response.iserror == 1)
            {
                $('#errodModal').modal('show');
                $('#ErrorTable tr').not(':first').remove();
                //console.log(response.error_info);   
                //console.log(response.length);
                console.log(response.errorTagsUndeploy);

                for (var i = 0; i < response.lengtherrAssettOtherCompany; i++) {
                    var newRow = '<tr>' +
                        '<td>' + response.errAssettOtherCompany[i] + '</td>' +
                        '<td>' + 'Assets owned by another company' + '</td>' + 

                        '</tr>';
                    $('#ErrorTable').append(newRow);

                }

            }else if(response.iserror == 0)
            {
                $('#myModal').modal('show');
                //console.log(response.fullNameUserTarget);
                //console.log(response.asset_ids_arr);
                //console.log(response.asset_tags);
                //console.log(response.asset_tags[0]);
                assetId = response.asset_ids_arr[0];
                checkoutUserID = response.checkoutUserID;
                targetID = response.targetUserID;

                $('#dynamicTable tr').not(':first').remove();

                // Duyệt qua mảng response và thêm dữ liệu vào bảng
                for (var i = 0; i < response.length; i++) {
                    var assetNote = response.asset_notes[i] ? response.asset_notes[i] : '';
                    var newRow = '<tr>' +
                        '<td>' + response.asset_tags[i] + '</td>' +
                        '<td>' + response.asset_names[i] + '</td>' +
                        '<td>' + '1' + '</td>' + 
                        '<td>' + assetNote + '</td>' +
                        '</tr>';
                    $('#dynamicTable').append(newRow);

                }

                $('#numberOfReport').text(response.numberOfReport);
                $('#dateOfHandover').text(response.checkoutDate);
                $('#handoverTo').text(response.fullNameUserTarget);
                $('#handoverFrom').text(response.fullNameUserCheckout);
                $('.m-signature-pad--title--signer').text(response.fullNameUserTarget);
                $('.m-signature-pad--title--signer2').text(response.fullNameUserCheckout);
            }
   
        },
        error: function(xhr, status, error) {
            // Xử lý lỗi
            console.error("Có lỗi xảy ra: " + status + " " + error);
        }
    });
});

</script>
@stop
