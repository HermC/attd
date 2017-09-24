<div class="form-group {!! !$errors->has($errorKey) ?: 'has-error' !!}">

    <label for="{{$id['position']}}" class="col-sm-{{$width['label']}} control-label">{{$label}}</label>

    <div class="col-sm-{{$width['field']}}">

        @include('admin::form.error')
        
	    <div id="r-result"  style="width:100%; margin">请输入签到位置:<input type="text" id="{{$id['position']}}"   name="{{$id['position']}}"   size="20"   style="width:150px;" /></div>
	    <div id="searchResultPanel" style="border:1px solid #C0C0C0;width:150px;height:auto; display:none;"></div>
	    <div id="l-map"  style="height:300px;width:100%;margin-top:10px"></div>
	    <input type="hidden"  id="{{$id['latitude']}}"  name="{{$id['latitude']}}" value="{{ old($column['latitude'], $value['latitude']) }}" {!! $attributes !!} />
        <input type="hidden"  id="{{$id['longitude']}}"  name="{{$id['longitude']}}" value="{{ old($column['longitude'], $value['longitude']) }}" {!! $attributes !!} />
        @include('admin::form.help-block')

    </div>
</div>