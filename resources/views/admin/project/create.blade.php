@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.project.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.project.store") }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group {{ $errors->has('register') ? 'has-error' : '' }}">
                {{-- <label for="register">{{ trans('global.order.fields.register') }}*</label> --}}
                <label for="register">Register*</label>
                <input type="date" id="register" name="register" class="form-control" value="{{ old('register', isset($order) ? $order->register : date('Y-m-d')) }}" required>
                @if($errors->has('register'))
                    <em class="invalid-feedback">
                        {{ $errors->first('register') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.order.fields.register_helper') }} --}}
                </p>
            </div>

            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                {{-- <label for="code">{{ trans('global.project.fields.code') }}*</label> --}}
                <label for="code">Code*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ $code}}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.project.fields.project_helper') }} --}}
                </p>
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                {{-- <label for="name">{{ trans('global.project.fields.name') }}*</label> --}}
                <label for="nama">Nama*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($project) ? $project->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.project.fields.name_helper') }} --}}
                </p>
            </div>

            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                {{-- <label for="address">{{ trans('global.order.fields.address') }}</label> --}}
                <label for="alamat">Alamat*</label>
                <textarea id="address" name="address" class="form-control ">{{ old('address', isset($product) ? $product->address : '') }}</textarea>
                @if($errors->has('address'))
                    <em class="invalid-feedback">
                        {{ $errors->first('address') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.order.fields.address_helper') }} --}}
                </p>
            </div>
           
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection