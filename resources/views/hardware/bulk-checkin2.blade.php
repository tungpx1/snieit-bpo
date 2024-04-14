@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Checkin Assets
    @parent
@stop

{{-- Page content --}}
@section('content')
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <style>
 .input-group {
    padding-left: 0px !important;
  }

  .m-signature-pad--body {
            border-style: solid;
            border-color: grey;
            border-width: thin;
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
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
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

        #errodModal {
        .modal-confirm {		
		color: #434e65;
		width: 525px;
		margin: 30px auto;
        }
        .th {
            text-align: center; /* Căn giữa nội dung trong cột */
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            background-color: #f2f2f2;

        }

        .td {
            text-align: center; /* Căn giữa nội dung trong cột */
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .modal-confirm .modal-content {
            padding: 20px;
            font-size: 16px;
            border-radius: 5px;
            border: none;
        }
        .modal-confirm .modal-header {
            background: #e85e6c;
            border-bottom: none;   
            position: relative;
            text-align: center;
            margin: -20px -20px 0;
            border-radius: 5px 5px 0 0;
            padding: 5px;
        }
        .modal-confirm h4 {
            text-align: center;
            font-size: 36px;
            margin: 10px 0;
        }
        .modal-confirm .form-control, .modal-confirm .btn {
            min-height: 40px;
            border-radius: 3px; 
        }
        .modal-confirm .close {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #fff;
            text-shadow: none;
            opacity: 0.5;
        }
        .modal-confirm .close:hover {
            opacity: 0.8;
        }
        .modal-confirm .icon-box {
            color: #fff;		
            width: 95px;
            height: 95px;
            display: inline-block;
            border-radius: 50%;
            z-index: 9;
            border: 5px solid #fff;
            padding: 15px;
            text-align: center;
        }
        .modal-confirm .icon-box i {
            font-size: 58px;
            margin: -2px 0 0 -2px;
        }
        .modal-confirm.modal-dialog {
            margin-top: 80px;
        }
        .modal-confirm .btn {
            color: #fff;
            border-radius: 4px;
            background: #eeb711;
            text-decoration: none;
            transition: all 0.4s;
            line-height: normal;
            border-radius: 30px;
            margin-top: 10px;
            padding: 6px 20px;
            min-width: 150px;
            border: none;
        }
        .modal-confirm .btn:hover, .modal-confirm .btn:focus {
            background: #eda645;
            outline: none;
        }
        .trigger-btn {
            display: inline-block;
            margin: 100px auto;
        }

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


                        <div id="errodModal" class="modal fade">
                        <div class="modal-dialog modal-confirm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div class="icon-box">
                                        <i class="material-icons">&#xE5CD;</i>
                                    </div>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                </div>
                                <div class="modal-body text-center">
                                    <h3>Ooops!</h3>	
                                    <p>Hi guy, Something went wrong. Pleas check again</p>
                                    <h>List of Items Error</h4>
                                                    <table id="ErrorTable">
                                                        <tr>
                                                            <th>Asset_tag</th>
                                                            <th>Error Details</th>
                                                        </tr>
                                                        <tr>

                                                        </tr>
                                                        <!-- Add more items here -->
                                                    </table>
                                    <button class="btn btn-success" data-dismiss="modal">Try Again</button>
                                </div>
                            </div>
                        </div>
                    </div>  


                        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div id="modal">
                                        <h1 style="text-align: center;">{{ env('Company_name') }}</h1>
                                        <p style="text-align: center;">{{ env('Company_Addr') }}</p>
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


                </div>
                <div class="box-footer">
                    <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                    <button type="submit" class="btn btn-success pull-right" id="checkin-button" ><i class="fa fa-check icon-white"></i> Check in</button>
                </div>
            </div>
            </form>
        </div>

       
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
    $('#checkin-button').prop('disabled', true);
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
        //console.log("Input Name:", $(this).attr('name'), "Value:", $(this).val());

    });

    // Gửi form bằng AJAX
    $.ajax({
        url: form.attr('action'),
        type: form.attr('method'),
        data: form.serialize(), // Serialize dữ liệu của form mới để gửi
        success: function(response) {
            console.log(response.iserror);

            if(response.iserror == 7)
            {
                $('#errodModal').modal('show');
                $('#ErrorTable tr').not(':first').remove();
                for (var i = 0; i < response.lengthAsset_id_notOwners; i++) {
                    var newRow = '<tr>' +
                        '<td>' + response.asset_tag_notOwners[i] + '</td>' +
                        '<td>' + 'Assets do not belong to User' + '</td>' + 

                        '</tr>';
                    $('#ErrorTable').append(newRow);

                }
                for (var i = 0; i < response.lengthAssetTagNotFound; i++) {
                    var newRow = '<tr>' +
                        '<td>' + response.assetTagNotFound[i] + '</td>' +
                        '<td>' + 'Assets Not Found' + '</td>' + 

                        '</tr>';
                    $('#ErrorTable').append(newRow);

                }
            }

            // else if(response.iserror == 6)
            // {
            //     $('#errodModal').modal('show');
            //     $('#ErrorTable tr').not(':first').remove();
            //     for (var i = 0; i < response.lengthAssetTagNotFound; i++) {
            //         var newRow = '<tr>' +
            //             '<td>' + response.assetTagNotFound[i] + '</td>' +
            //             '<td>' + 'Assets Not Found' + '</td>' + 

            //             '</tr>';
            //         $('#ErrorTable').append(newRow);

            //     }

            // }

            else if(response.iserror == 5)
            {
                $('#errodModal').modal('show');
                $('#ErrorTable tr').not(':first').remove();
                for (var i = 0; i < 1; i++) {
                    var newRow = '<tr>' +
                        '<td>' + 'Error' + '</td>' +
                        '<td>' + 'Please choose User to checkin!' + '</td>' + 

                        '</tr>';
                    $('#ErrorTable').append(newRow);

                }

            }

            else if(response.iserror == 4)
            {
                $('#errodModal').modal('show');
                $('#ErrorTable tr').not(':first').remove();
                for (var i = 0; i < 1; i++) {
                    var newRow = '<tr>' +
                        '<td>' + 'Error' + '</td>' +
                        '<td>' + 'Please choose Assets to checkin!' + '</td>' + 

                        '</tr>';
                    $('#ErrorTable').append(newRow);

                }

            }

            else if(response.iserror == 3)
            {
                $('#errodModal').modal('show');
                $('#ErrorTable tr').not(':first').remove();
                for (var i = 0; i < 1; i++) {
                    var newRow = '<tr>' +
                        '<td>' + 'Error' + '</td>' +
                        '<td>' + 'Please choose only option. Assets field or bulk_asset_tag' + '</td>' + 

                        '</tr>';
                    $('#ErrorTable').append(newRow);

                }
            }

            else if(response.iserror == 2)
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
                typeHanoverPaper = response.typeHanoverPaper;

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
                $('#handoverTo').text(response.fullNameUserCheckout);
                $('#handoverFrom').text(response.fullNameUserTarget);
                $('.m-signature-pad--title--signer').text(response.fullNameUserCheckout);
                $('.m-signature-pad--title--signer2').text(response.fullNameUserTarget);
            }
   
        },
        error: function(xhr, status, error) {
            // Xử lý lỗi
            console.error("Có lỗi xảy ra: " + status + " " + error);
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
        formData.append('typeHanoverPaper', typeHanoverPaper);


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
                //console.log(response);
                var Linkfile = response.filelink;
                var message = 'View file upload ?';
                if (confirm(message)) {
                    window.open(Linkfile, '_blank');
                }
                $('#checkin-button').prop('disabled', false);
                // $('#checkout-form').submit();


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
