@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.product.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.products.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.product.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($product) ? $product->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.product.fields.name_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.product.fields.description') }}</label>
                <textarea id="description" name="description" class="form-control ">{{ old('description', isset($product) ? $product->description : '') }}</textarea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.product.fields.description_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('price') ? 'has-error' : '' }}">
                <label for="price">{{ trans('global.product.fields.price') }}</label>
                <input type="number" id="price" name="price" class="form-control" value="{{ old('price', isset($product) ? $product->price : '') }}" step="0.01">
                @if($errors->has('price'))
                    <em class="invalid-feedback">
                        {{ $errors->first('price') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.product.fields.price_helper') }}
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


            {{-- <div class="form-group {{ $errors->has('discount') ? 'has-error' : '' }}">
                <label for="discount">{{ trans('global.product.fields.discount') }}</label>
                <input type="number" id="discount" name="discount" class="form-control" value="{{ old('discount', isset($product) ? $product->discount : '') }}" step="0.01">
                @if($errors->has('discount'))
                    <em class="invalid-feedback">
                        {{ $errors->first('discount') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.product.fields.discount_helper') }}
                </p>
            </div> --}}
            {{-- <div class="form-group {{ $errors->has('bv') ? 'has-error' : '' }}">
                <label for="bv">{{ trans('global.product.fields.bv') }}</label>
                <input type="number" id="bv" name="bv" class="form-control" value="{{ old('bv', isset($product) ? $product->bv : '') }}" step="0.01">
                @if($errors->has('bv'))
                    <em class="invalid-feedback">
                        {{ $errors->first('bv') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.product.fields.bv_helper') }}
                </p>
            </div> --}}

            {{-- <div class="form-group {{ $errors->has('model') ? 'has-error' : '' }}">
                <label for="model">{{ trans('global.product.fields.model') }}*</label>
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
                    {{ trans('global.product.fields.model_helper') }}
                </p>
            </div> --}}
            
            <!-- <div class="form-group {{ $errors->has('cogs') ? 'has-error' : '' }}">
                <label for="cogs">{{ trans('global.product.fields.cogs') }}</label>
                <input type="number" id="cogs" name="cogs" class="form-control" value="{{ old('cogs', isset($product) ? $product->cogs : '') }}" step="0.01">
                @if($errors->has('cogs'))
                    <em class="invalid-feedback">
                        {{ $errors->first('cogs') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.product.fields.cogs_helper') }}
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
@endsection