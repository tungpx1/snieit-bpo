@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Handover paper list
    @parent
@stop

@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-sm btn-primary pull-right">
        {{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')
<style>
         #type-search {
             width: 160px;
         }
         #status-search {
             width: 160px;
         }
     </style>
     
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-body">
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-md-12">
                        <form class="form-inline">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="user-search">Receiver</label>
                                        <select class="js-example-basic-single form-control" name="user" id="user-search">
                                            <option></option>
                                            @foreach($users as $key => $user)
                                            <option value="{{ $user->id }}" @if ($user_search == $user->id) selected="selected" @endif>{{ $user->getFullNameAttribute() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="asset-search">Asset_Tag</label>
                                        <select class="js-example-basic-single form-control" name="asset_tag" id="asset-search">
                                            <option></option>
                                            @foreach($assets as $key => $asset)
                                                <option value="{{ $asset->asset_tag }}" @if ($asset_search == $asset->asset_tag) selected="selected" @endif>{{ $asset->asset_tag }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status-search">Status Handover Paper </label>
                                        <select class="js-example-basic-single form-control" name="status" id="status-search">
                                            <option value="-1" @if ($status == -1) selected @endif>Show All</option>
                                            <option value="0" @if ($status == 0) selected @endif>Unconfirmed</option>
                                            <option value="1" @if ($status == 1) selected @endif>Confirmed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="type-search">Type Handover Paper </label>
                                        <select class="js-example-basic-single form-control" name="type" id="type-search">
                                            <option value="-1" @if ($type == -1) selected @endif>Show All</option>
                                            <option value="0" @if ($type == 0) selected @endif>Checkin</option>
                                            <option value="1" @if ($type == 1) selected @endif>Checkout</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Search</button>
                        </form>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Number of report</th>
                                    <th scope="col">Link</th>
                                    <th scope="col">Sender</th>
                                    <th scope="col">Receiver</th>
                                    <th scope="col">Asset_tag</th>
                                    <th scope="col">Type</th>
                                    <th>Verify</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($papers as $key => $paper)
                                    <tr>
                                        <th scope="row">{{ $key + 1 }}</th>
                                        <td>{{ $paper->number_of_report }}</td>
                                        <td><a href="{{$paper->link}}" target="_blank" class="btn btn-link">Click here</a></td>
                                        <td>{{ $paper->sender ? $paper->sender->getFullNameAttribute() : '' }}</td>
                                        <td>{{ $paper->receiver ? $paper->receiver->getFullNameAttribute() : '' }}</td>
                                        <td>{{ $paper->asset_tag ? $paper->asset_tag : 'N/A' }}</td>
                                        <td>{{ $paper->type === 0 ? 'Check in' : 'Check out' }}</td>
                                        <td>
                                            @if ($paper->is_verify)
                                                <a class="btn btn-success" style="color: yellow;" href="{{ route('handover_paper.verify', ['id' => $paper]) }}">Confirmed</a>
                                            @else
                                                <a class="btn btn-danger" href="{{ route('handover_paper.verify', ['id' => $paper]) }}">Unconfirmed</a>
                                            @endif
                                        </td>

                                        <!-- <td>@if ($paper->is_verify) <a class="btn btn-success" href="{{ route('handover_paper.verify', ['id' => $paper]) }}">Confirmed</a> @else <a class="btn btn-danger" href="{{ route('handover_paper.verify', ['id' => $paper]) }}">Unconfirmed</a> @endif</td> -->
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            {{ $papers->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection