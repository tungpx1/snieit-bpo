@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Bulk Checkout Consumables
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
                    <h3 class="box-title">Bulk Checkout Consumables </h3>
                </div>
                <div class="box-body">
                    <form class="form-horizontal" method="post" action="" autocomplete="off">
                    {{ csrf_field() }}
                        @include ('partials.forms.edit.consumable-select', ['translated_name' =>'Consumables', 'fieldname' => 'assigned_user', 'required'=>'true'])
            

                        <label for="consumables-select">Consumable:</label>
                        <select id="consumables-select" name="consumables[][consumableId]" required>
                            <option value="">Chọn consumable</option>
                            {{-- Các options sẽ được thêm bởi JavaScript --}}
                        </select>

                        <label for="qty">Số lượng:</label>
                        <input type="number" name="consumables[][qty]" min="1" required>


                        <div class="form-group">
                            <label class="col-md-3 control-label">Export handover paper</label>
                            <div class="col-md-8" style="padding-top:7px;">
                                <input type="checkbox" name="export_handover_paper" />
                            </div>
                        </div>

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

@stop

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.querySelector('[name="assigned_user"]');
    userSelect.addEventListener('change', function() {
        const userId = this.value;
        // Lấy company ID
        fetch(`/get-company/${userId}`)
            .then(response => response.json())
            .then(data => {
                // Load consumables dựa trên company ID
                updateConsumablesDropdown(data.companyId);
            });
    });
});

function updateConsumablesDropdown(companyId) {
    fetch(`/get-consumables/${companyId}`)
        .then(response => response.json())
        .then(consumables => {
            let consumablesSelect = document.getElementById('consumables-select');
            consumablesSelect.innerHTML = '<option value="">Chọn consumable</option>'; // Clear và thêm option mặc định
            consumables.forEach(consumable => {
                let option = new Option(consumable.name, consumable.id);
                consumablesSelect.appendChild(option);
            });
        });
}
</script>
