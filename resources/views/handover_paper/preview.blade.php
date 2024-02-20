@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Preview Handover Paper
    @parent
@stop

{{-- Page content --}}
@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handover Paper</title>
    <link rel="stylesheet" href="{{ url('css/signature-pad.min.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
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
        .m-signature-pad--body {
            border-style: solid;
            border-color: grey;
            border-width: thin;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        @page {
            size: A4;
        }
        @media print {
        #clear_button, #submit-button, #print-button,#pdf-file {
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

<form class="form-horizontal" method="post" action="{{ route('handover.submit', ['assetId' => $asset->id, 'admin' => $checkoutUser->id, 'target' => $target->id]) }}" enctype="multipart/form-data" autocomplete="off">   
     @csrf
</head>
<body>
    <h1 style="text-align: center;">CÔNG TY TNHH SGS VIỆT NAM</h1>
    <p style="text-align: center;">Tầng 7, 9, 10 Toà nhà VTC  18 Tam Trinh, Phường Minh Khai, Quận Hai Bà Trưng, Hà Nội</p>
    <h2 style="text-align: center;">Handover Paper</h2>
    <p style="text-align: center;">Số: {{$numberOfReport}}</p>
    <table>
        <tr>
            <th>Handover Details</th>
            <th>Information</th>
        </tr>
        <tr>
            <td>Date of Handover:</td>
            <td>[Date, e.g., October 10, 2023]</td>
        </tr>
        <tr>
            <td>Handover From:</td>
            <td>{{ $checkoutUser ? $checkoutUser->getFullNameAttribute() :'N/A' }}</td>
        </tr>
        <tr>
            <td>Handover To:</td>
            <td>{{ $target ? $target->getFullNameAttribute() :'N/A' }}</td>
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
        <div>
            <p><b>NGƯỜI GIAO</b></p>
            <p>{{ $checkoutUser->getFullNameAttribute() }}</p>
        </div>
        <div></div> <!-- This is an empty div to create the space in the middle -->
        <div>
            <p><b>NGƯỜI NHẬN</b></p>
            <p>{{ $target->getFullNameAttribute()}}</p>
        </div>
    </div>
    <div class="col-md-12">
        <h4 style="padding-top: 20px">{{trans('general.sign_tos')}}</h4>
            <div id="signature-pad" class="m-signature-pad">
                <div class="m-signature-pad--body col-md-12 col-sm-12 col-lg-12 col-xs-12">
                    <canvas></canvas>
                    <input type="hidden" name="signature_output" id="signature_output">
                </div>
                    <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 text-center">
                        <button type="button" class="btn btn-sm btn-default clear" data-action="clear" id="clear_button">{{trans('general.clear_signature')}}</button>
                </div>
            </div>
    </div>
    <div class="col-md-6">
    <button type="button" id="print-button" class="btn btn-default">Print</button>
    </div>

    <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 text-center">
    <input type="file" id="pdf-file" name="pdf-file" accept=".pdf">
    </div>

    <div class="box-footer text-right">
            <button type="submit" class="btn btn-success" id="submit-button"><i class="fa fa-check icon-white" aria-hidden="true"></i> {{ trans('general.submit') }}</button>
    </div><!-- /.box-footer -->

</body>
</html>
</form>

@stop

@section('moar_scripts')

<script nonce="{{ csrf_token() }}">
        var wrapper = document.getElementById("signature-pad"),
            clearButton = wrapper.querySelector("[data-action=clear]"),
            saveButton = wrapper.querySelector("[data-action=save]"),
            canvas = wrapper.querySelector("canvas"),
            signaturePad;

        // Adjust canvas coordinate space taking into account pixel ratio,
        // to make it look crisp on mobile devices.
        // This also causes canvas to be cleared.
        function resizeCanvas() {
            // When zoomed out to less than 100%, for some very strange reason,
            // some browsers report devicePixelRatio as less than 1
            // and only part of the canvas is cleared then.
            var ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        }

        window.onresize = resizeCanvas;
        resizeCanvas();

        signaturePad = new SignaturePad(canvas);

        $('#clear_button').on("click", function (event) {
            signaturePad.clear();
        });

        $('#submit-button').on("click", function (event) {
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

    </script>


@stop


