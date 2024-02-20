

@extends('layouts/default')
{{-- Page title --}}
@section('title')
    {{ trans('admin/hardware/general.checkout') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
<script src="{{ asset('js/jspdf.min.js') }}"></script>
    <style>

        h1 {
            text-align: center;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signatures div {
            text-align: center;
            width: 30%;
        }
        .modal-dialog {
            width: 21cm; /* Kích thước chiều rộng của trang A4 */
            height: 29.7cm; /* Kích thước chiều cao của trang A4 */
            max-width: 100%; /* Đảm bảo modal không vượt quá kích thước trang A4 */
            margin: auto; /* Canh giữa modal trên trang */
            }

        .m-signature-pad--body {
            border: solid 1px gray;
            border-radius:4px;
            height:200px;
            padding: 0px !important;
          
        }
        .m-signature-pad--title--signer {
            position: absolute;
            right: 20px;
            opacity:0.5;
            top:40px;
        }
        .m-signature-pad--title--signer2 {
            position: absolute;
            left: 20px;
            opacity:0.5;
            top:40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        @media print {

            body * {
            visibility: hidden;
            }
            #clear_button, #upload-PDF, #print-button,#pdf-file {
            display: none;
            }
            .modal-footer {
                display: none;
            }
            .modal-dialog {
                width: 21cm;
                height: 29.7cm;
                max-width: 100%;
                margin: 0 auto;
            }
            .modal-content * {
                visibility: visible;
            }
            .modal-content {
                height: 100%;
                border: none;
                box-shadow: none;
            }
   
        #clear_button, #upload-PDF, #print-button,#gerarPDF {
            display: none;
        }
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
<div class="row">
        <!-- left column -->
        <div class="col-md-7">
            <div class="box box-default">
                <form class="form-horizontal" method="post" action="" autocomplete="off">
                    <div class="box-header with-border">
                        <h2 class="box-title"> {{ trans('admin/hardware/form.tag') }} {{ $asset->asset_tag }}</h2>
                    </div>
                    <div class="box-body">
                    {{csrf_field()}}
                        @if ($asset->company && $asset->company->name)
                            <div class="form-group">
                                {{ Form::label('model', trans('general.company'), array('class' => 'col-md-3 control-label')) }}
                                <div class="col-md-8">
                                    <p class="form-control-static">
                                        {{ $asset->company->name }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    <!-- AssetModel name -->
                        <div class="form-group">
                            {{ Form::label('model', trans('admin/hardware/form.model'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <p class="form-control-static">
                                    @if (($asset->model) && ($asset->model->name))
                                        {{ $asset->model->name }}
                                    @else
                                        <span class="text-danger text-bold">
                  <i class="fas fa-exclamation-triangle"></i>{{ trans('admin/hardware/general.model_invalid')}}
                  <a href="{{ route('hardware.edit', $asset->id) }}"></a> {{ trans('admin/hardware/general.model_invalid_fix')}}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Asset Name -->
                        <div class="form-group {{ $errors->has('name') ? 'error' : '' }}">
                            {{ Form::label('name', trans('admin/hardware/form.name'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <input class="form-control" type="text" name="name" id="name" value="{{ old('name', $asset->name) }}" tabindex="1">
                                {!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group {{ $errors->has('status_id') ? 'error' : '' }}">
                            {{ Form::label('status_id', trans('admin/hardware/form.status'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-7 required">
                                {{ Form::select('status_id', $statusLabel_list, $asset->status_id, array('class'=>'select2', 'style'=>'width:100%','', 'aria-label'=>'status_id')) }}
                                {!! $errors->first('status_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                    @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true'])

                    @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user', 'required'=>'true'])
                    

                    <!-- We have to pass unselect here so that we don't default to the asset that's being checked out. We want that asset to be pre-selected everywhere else. -->
                    @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.asset'), 'fieldname' => 'assigned_asset', 'unselect' => 'true', 'style' => 'display:none;', 'required'=>'true'])

                    @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'style' => 'display:none;', 'required'=>'true'])



                    <!-- Checkout/Checkin Date -->
                        <div class="form-group {{ $errors->has('checkout_at') ? 'error' : '' }}">
                            {{ Form::label('checkout_at', trans('admin/hardware/form.checkout_date'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group date col-md-7" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-end-date="0d" data-date-clear-btn="true">
                                    <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="checkout_at" id="checkout_at" value="{{ old('checkout_at', date('Y-m-d')) }}">
                                    <span class="input-group-addon"><i class="fas fa-calendar" aria-hidden="true"></i></span>
                                </div>
                                {!! $errors->first('checkout_at', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Expected Checkin Date -->
                        <div class="form-group {{ $errors->has('expected_checkin') ? 'error' : '' }}">
                            {{ Form::label('expected_checkin', trans('admin/hardware/form.expected_checkin'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group date col-md-7" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-start-date="0d" data-date-clear-btn="true">
                                    <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="expected_checkin" id="expected_checkin" value="{{ old('expected_checkin') }}">
                                    <span class="input-group-addon"><i class="fas fa-calendar" aria-hidden="true"></i></span>
                                </div>
                                {!! $errors->first('expected_checkin', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                            {{ Form::label('note', trans('admin/hardware/form.notes'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <textarea class="col-md-6 form-control" id="note" name="note">{{ old('note', $asset->note) }}</textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Preview handover Paper -->
                        <div class="form-group">
                            <label class="col-md-3 control-label"></label>
                            <div class="col-md-8">
                            <button type="button" class="btn btn-primary" id="preview-handover-paper">Preview Handover Paper</button>
                            </div>
                        </div>


                        <div class="form-group">
                        <label class="col-md-3 control-label">The recipient is not ready to sign</label>
                        <div class="col-md-8" style="padding-top:7px;">
                            <input type="checkbox" id="disable-submit" onchange="toggleSubmitButton()" >
                            </div>
                        </div>

                        <!-- Preview handover Paper
                        <div class="form-group">
                            <label class="col-md-3 control-label"></label>
                            <div class="col-md-8">
                            <div class="checkbox">
                          <input type="checkbox" id="disable-submit" onchange="toggleSubmitButton()"> 
                        <label for="disable-submit">The recipient is not ready to sign</label>
                        </div>
                            </div>
                        </div> -->

                                <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-xl" role="document">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <div id="modal">
                                                    <h1 style="text-align: center;">CÔNG TY TNHH SGS VIỆT NAM</h1>
                                                    <p style="text-align: center;">Tầng 7, 9, 10 Toà nhà VTC 18 Tam Trinh, Phường Minh Khai, Quận Hai Bà Trưng, Hà Nội</p>
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
                                                            <td>[Reason for handover, e.g., End of project, Change in management]</td>
                                                        </tr>
                                                    </table>
                                                    <h2>List of Items Handed Over</h2>
                                                    <table>
                                                        <tr>
                                                            <th>Asset_tag</th>
                                                            <th>Item</th>
                                                            <th>Quantity</th>
                                                            <th>Description</th>
                                                        </tr>
                                                        <tr>
                                                            <td>{{ $asset->asset_tag}}</td>
                                                            <td>{{ $asset->name}}</td>
                                                            <td>1</td>
                                                            <td>[Description of Item]</td>
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
                                                            <div class="m-signature-pad--body col-md-12 col-sm-12 col-lg-12 col-xs-12">
                                                                <canvas></canvas>
                                                                <div class="m-signature-pad--title--signer"></div>
                                                                <div class="m-signature-pad--title--signer2"></div>
                                                                < <input type="hidden" name="signature_output" id="signature_output">
                                                            </div>
                                                            <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 text-center">
                                                                <button type="button" class="btn btn-sm btn-default clear" data-action="clear" id="clear_button">{{trans('general.clear_signature')}}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <button type="button" id="print-button" class="btn btn-default">Save file PDF</button>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 text-center">
                                                        <input type="file" id="pdf-file" name="pdf-file" accept=".pdf">
                                                    </div>
                                                    <div class="box-footer text-right">
                                                        <button type="button" class="btn btn-success" id="upload-PDF">
                                                            <i class="fa fa-check icon-white" aria-hidden="true"></i> {{ trans('general.submit') }}
                                                        </button>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            

                        @if ($asset->requireAcceptance() || $asset->getEula() || ($snipeSettings->webhook_endpoint!=''))
                            <div class="form-group notification-callout">
                                <div class="col-md-8 col-md-offset-3">
                                    <div class="callout callout-info">

                                        @if ($asset->requireAcceptance())
                                            <i class="far fa-envelope" aria-hidden="true"></i>
                                            {{ trans('admin/categories/general.required_acceptance') }}
                                            <br>
                                        @endif

                                        @if ($asset->getEula())
                                            <i class="far fa-envelope" aria-hidden="true"></i>
                                            {{ trans('admin/categories/general.required_eula') }}
                                            <br>
                                        @endif

                                        @if ($snipeSettings->webhook_endpoint!='')
                                            <i class="fab fa-slack" aria-hidden="true"></i>
                                            {{ trans('general.webhook_msg_note') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div> <!--/.box-body-->
                    <div class="box-footer">
                        <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-primary pull-right" id="checkout-button" disabled><i class="fas fa-check icon-white" aria-hidden="true"></i> {{ trans('general.checkout') }}</button>
                    </div>
                </form>
            </div>
        </div> <!--/.col-md-7-->

        <!-- right column -->
        <div class="col-md-5" id="current_assets_box" style="display:none;">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('admin/users/general.current_assets') }}</h2>
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

        $(document).ready(() => {
            $('input[name=checkout_to_type]').on("change", e => {
                if ($(e.target).val() === 'user') {
                    $('#preview-handover-paper').show();
                } else {
                    $('#preview-handover-paper').hide();
                }
            })
        })

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
    </script>

<script>

var assetId;
var checkoutUserID;
var targetID;

function toggleSubmitButton() {
    var disableSubmit = document.getElementById('disable-submit').checked;
    var submitButton = document.getElementById('checkout-button');

    if (disableSubmit) {
        submitButton.disabled = false;
    } else {
        submitButton.disabled = true;
    }
}


$('#preview-handover-paper').on('click', function() {
    // Tạo một form mới
    var form = $('<form>', {
        'method': 'post',
        'action': '{{ route('preview.handover') }}'   
     });

    // Thêm CSRF token vào form
    form.append($('<input>', {
        'name': '_token',
        'value': '{{ csrf_token() }}',
        'type': 'hidden'
    }));

    // Thêm ID của $asset vào form
    form.append($('<input>', {
        'name': 'asset_id',
        'value': '{{ $asset->id }}',
        'type': 'hidden'
    }));

    // Thu thập dữ liệu từ các trường input của form hiện tại
    $('.form-horizontal').find('input, select, textarea').each(function() {
        // Tạo một trường input ẩn mới với cùng tên và giá trị
        var input = $('<input>', {
            'name': $(this).attr('name'),
            'value': $(this).val(),
            'type': 'hidden'
        });

        // Thêm trường input vào form
        form.append(input);
    });

    // Gửi form bằng AJAX
    $.ajax({
        url: form.attr('action'),
        type: form.attr('method'),
        data: form.serialize(),
        success: function(response) {
            // Cập nhật nội dung modal với kết quả trả về
            assetId = response.assetId;
            checkoutUserID = response.checkoutUserID;
            targetID = response.targetUserID;
            
            $('#numberOfReport').text(response.numberOfReport);

            $('#dateOfHandover').text(response.checkoutDate);
            $('#handoverTo').text(response.fullNameUserTarget);
            $('#handoverFrom').text(response.fullNameUserCheckout);
            $('.m-signature-pad--title--signer').text(response.fullNameUserTarget);
            $('.m-signature-pad--title--signer2').text(response.fullNameUserCheckout);
       
            // Hiển thị modal
            $('#myModal').modal('show');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Xử lý lỗi ở đây
        }
    });
});

$('#upload-PDF').on('click', function(event) {
    event.preventDefault(); // Ngăn chặn hành vi mặc định của nút submit

    // Tạo đối tượng FormData
    var formData = new FormData();

    // Thêm CSRF token vào FormData
    formData.append('_token', '{{ csrf_token() }}');

    // Thêm các giá trị vào FormData
    formData.append('assetId', assetId);
    formData.append('checkoutUser', checkoutUserID);
    formData.append('target', targetID);

    // Lấy file từ trường input file và thêm vào FormData
    var fileInput = document.getElementById('pdf-file');
    if (fileInput.files.length > 0) {
        var file = fileInput.files[0];
        formData.append('pdf-file', file);
    } else {
        alert('Please select a PDF file to upload.');
        return;
    }

    // Gửi FormData qua AJAX
    $.ajax({
        url: '{{ route('handover.submit') }}', // URL đến hàm xử lý trong controller
        type: 'POST',
        data: formData,
        contentType: false, // Đặt contentType và processData thành false để gửi FormData
        processData: false,
        success: function(response) {
            // Xử lý khi gửi thành công
            console.log(response);
            var Linkfile = response.filelink;
            var message = 'View file upload ?';
            if (confirm(message)) {
                window.open(Linkfile, '_blank');
            }
            $('#checkout-button').prop('disabled', false);
            // $('#checkout-form').submit();


            // Có thể chuyển hướng hoặc hiển thị thông báo thành công tại đây
        },
        error: function(xhr, status, error) {
            // Xử lý khi có lỗi
            console.error('Error:', error);
            // Hiển thị thông báo lỗi tại đây
        }
    });
});

</script>

@stop
