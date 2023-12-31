<div id="{{ (isset($id_divname)) ? $id_divname : 'assetsBulkEditToolbar' }}" style="min-width:400px">
{{ Form::open([
      'method' => 'POST',
      'route' => ['hardware/bulkedit'],
      'class' => 'form-inline',
      'id' => (isset($id_formname)) ? $id_formname : 'assetsBulkForm',
 ]) }}


    <label for="bulk_actions">
        <span class="sr-only">
            {{ trans('button.bulk_actions') }}
        </span>
    </label>
    <select name="bulk_actions" class="form-control select2" aria-label="bulk_actions" style="width: 200px;">
        @if((isset($status)) && ($status == 'Deleted'))
        @can('delete', \App\Models\Asset::class)
            <option value="restore">{{trans('button.restore')}}</option> 
        @endcan
        @else
        @can('update', \App\Models\Asset::class)
            <option value="edit">{{ trans('button.edit') }}</option>
        @endcan
        @if(isset($checkin) && ($checkin))
            @can('checkin', \App\Models\Asset::class)
                <option value="checkin">{{trans('button.checkin_all')}}</option>
            @endcan
        @endif
        @can('delete', \App\Models\Asset::class)
            <option value="delete">{{ trans('button.delete') }}</option>
        @endcan
        <option value="labels" accesskey="l">{{ trans_choice('button.generate_labels', 2) }}</option>
        @endif
    </select>

    <button class="btn btn-primary" id="{{ (isset($id_button)) ? $id_button : 'bulkAssetEditButton' }}" disabled>{{ trans('button.go') }}</button>
    {{ Form::close() }}
</div>
