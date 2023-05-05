@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.property.title_singular') }}
    </div>

    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card-body">
        <form action="{{ route("admin.property.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.property.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($property) ? $property->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.property.fields.name_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.property.fields.description') }}</label>
                <textarea id="description" name="description" class="form-control ">{{ old('description', isset($property) ? $property->description : '') }}</textarea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.property.fields.description_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('price') ? 'has-error' : '' }}">
                <label for="price">{{ trans('global.property.fields.price') }}</label>
                <input type="number" id="price" name="price" class="form-control" value="{{ old('price', isset($property) ? $property->price : '') }}" step="0.01">
                @if($errors->has('price'))
                    <em class="invalid-feedback">
                        {{ $errors->first('price') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.property.fields.price_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('units_id') ? 'has-error' : '' }}">
                {{-- <label for="units_id">{{ trans('global.order.fields.units_id') }}*</label> --}}
                <label>Satuan</label>
                <select name="unit_id" class="form-control">
                    <option value="">-- choose unit --</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}"{{ old('code') == $unit->id ? ' selected' : '' }}>
                       {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('units_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('units_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.order.fields.units_id_helper') }} --}}
                </p>
            </div>

            <div class="form-group {{ $errors->has('sub_contractors_id') ? 'has-error' : '' }}">
                {{-- <label for="sub_contractors_id">{{ trans('global.order.fields.sub_contractors_id') }}*</label> --}}
                <label>Mandor</label>
                <select name="customer_id" class="form-control">
                    <option value="">-- choose sub_contractor --</option>
                    @foreach ($sub_contractors as $sub_contractor)
                        <option value="{{ $sub_contractor->id }}"{{ old('code') == $sub_contractor->id ? ' selected' : '' }}>
                       {{ $sub_contractor->name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('sub_contractors_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('sub_contractors_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.order.fields.sub_contractors_id_helper') }} --}}
                </p>
            </div>


            <div class="form-group {{ $errors->has('projects_id') ? 'has-error' : '' }}">
                {{-- <label for="projects_id">{{ trans('global.order.fields.projects_id') }}*</label> --}}
                <label>Project</label>
                <select name="project_id" id="project_id" class="form-control">
                    <option value="">-- choose project --</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}"{{ old('code') == $project->id ? ' selected' : '' }}>
                       {{ $project->name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('projects_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('projects_id') }}
                    </em>
                @endif
                <p class="helper-project">
                    {{-- {{ trans('global.order.fields.projects_id_helper') }} --}}
                </p>
            </div>

            <div class="form-group {{ $errors->has('block_id') ? 'has-error' : '' }}">
                <label for="block_id">{{ trans('global.staff.fields.block') }}*</label>
                <select id="block_id" name="block_id" class="form-control" value="{{ old('block_id', isset($customer) ? $customer->block : '') }}">
                    <option value="">--Pilih Sub Depertement--</option>                    
                </select>
                @if($errors->has('block_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('block_id') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('no') ? 'has-error' : '' }}">
                <label for="no">{{ trans('global.property.fields.no') }}</label>
                <input type="number" id="no" name="no" class="form-control" value="{{ old('no', isset($property) ? $property->no : '') }}" step="0.01">
                @if($errors->has('no'))
                    <em class="invalid-feedback">
                        {{ $errors->first('no') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.property.fields.no_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('surface_area') ? 'has-error' : '' }}">
                <label for="surface_area">{{ trans('global.property.fields.surface_area') }}</label>
                <input type="number" id="surface_area" name="surface_area" class="form-control" value="{{ old('surface_area', isset($property) ? $property->surface_area : '') }}" step="0.01">
                @if($errors->has('surface_area'))
                    <em class="invalid-feedback">
                        {{ $errors->first('surface_area') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.property.fields.surface_area_helper') }}
                </p>
            </div>

            
            <div class="form-group {{ $errors->has('building_area') ? 'has-error' : '' }}">
                <label for="building_area">{{ trans('global.property.fields.building_area') }}</label>
                <input type="number" id="building_area" name="building_area" class="form-control" value="{{ old('building_area', isset($property) ? $property->building_area : '') }}" step="0.01">
                @if($errors->has('building_area'))
                    <em class="invalid-feedback">
                        {{ $errors->first('building_area') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.property.fields.building_area_helper') }}
                </p>
            </div>


            <div class="form-group {{ $errors->has('more_land') ? 'has-error' : '' }}">
                <label for="more_land">{{ trans('global.property.fields.more_land') }}</label>
                <input type="number" id="more_land" name="more_land" class="form-control" value="{{ old('more_land', isset($property) ? $property->more_land : '') }}" step="0.01">
                @if($errors->has('more_land'))
                    <em class="invalid-feedback">
                        {{ $errors->first('more_land') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.property.fields.more_land_helper') }}
                </p>
            </div>

            {{-- <div class="form-group {{ $errors->has('discount') ? 'has-error' : '' }}">
                <label for="discount">{{ trans('global.property.fields.discount') }}</label>
                <input type="number" id="discount" name="discount" class="form-control" value="{{ old('discount', isset($property) ? $property->discount : '') }}" step="0.01">
                @if($errors->has('discount'))
                    <em class="invalid-feedback">
                        {{ $errors->first('discount') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.property.fields.discount_helper') }}
                </p>
            </div> --}}
            {{-- <div class="form-group {{ $errors->has('bv') ? 'has-error' : '' }}">
                <label for="bv">{{ trans('global.property.fields.bv') }}</label>
                <input type="number" id="bv" name="bv" class="form-control" value="{{ old('bv', isset($property) ? $property->bv : '') }}" step="0.01">
                @if($errors->has('bv'))
                    <em class="invalid-feedback">
                        {{ $errors->first('bv') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.property.fields.bv_helper') }}
                </p>
            </div> --}}

            {{-- <div class="form-group {{ $errors->has('model') ? 'has-error' : '' }}">
                <label for="model">{{ trans('global.property.fields.model') }}*</label>
                <select name="model" class="form-control">
                    <option value="network" selected="selected">Network</option>
                    <option value="reseller">Reseller</option>                    
                </select>
                @if($errors->has('model'))
                    <em class="invalid-feedback">
                        {{ $errors->first('model') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.property.fields.model_helper') }}
                </p>
            </div> --}}
            
            <!-- <div class="form-group {{ $errors->has('cogs') ? 'has-error' : '' }}">
                <label for="cogs">{{ trans('global.property.fields.cogs') }}</label>
                <input type="number" id="cogs" name="cogs" class="form-control" value="{{ old('cogs', isset($property) ? $property->cogs : '') }}" step="0.01">
                @if($errors->has('cogs'))
                    <em class="invalid-feedback">
                        {{ $errors->first('cogs') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.property.fields.cogs_helper') }}
                </p>
            </div> -->
   
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
  $(document).ready(function(){
    let row_number = {{ count(old('accounts', [''])) }};
    $("#add_row").click(function(e){
      e.preventDefault();
      let new_row_number = row_number - 1;
      $('#account' + row_number).html($('#account' + new_row_number).html()).find('td:first-child');
      $('#accounts_table').append('<tr id="account' + (row_number + 1) + '"></tr>');
      row_number++;
    });

    $("#delete_row").click(function(e){
      e.preventDefault();
      if(row_number > 1){
        $("#account" + (row_number - 1)).html('');
        row_number--;
      }
    });
  });
</script>

<script>
    $('#project_id').change(function(){
    var project_id = $(this).val();    
    if(project_id){
        $.ajax({
           type:"GET",
           url:"{{ route('admin.property.getBlock') }}?project_id="+project_id,
           dataType: 'JSON',
           success:function(res){               
            if(res){
                $("#block_id").empty();
                $("#block_id").append('<option>---Pilih Block---</option>');
                $.each(res,function(id,name){
                    $("#block_id").append('<option value="'+id+'">'+name+'</option>');
                });
            }else{
               $("#block_id").empty();
            }
           }
        });
    }else{
        $("#block_id").empty();
    }      
   });
</script>
@endsection