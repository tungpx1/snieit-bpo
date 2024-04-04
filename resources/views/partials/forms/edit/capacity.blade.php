<!-- Order Number -->
<div class="form-group {{ $errors->has('capacity') ? ' has-error' : '' }}">
   <label for="capacity" class="col-md-3 control-label">{{ ('Capacity') }}</label>
   <div class="col-md-1 col-sm-8">
       <input class="form-control" type="text" name="capacity" aria-label="capacity" id="capacity" value="{{ old('capacity', $item->capacity) }}" />
       {!! $errors->first('capacity', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
   </div>
</div>
