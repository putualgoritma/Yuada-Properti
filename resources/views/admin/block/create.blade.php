@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.block.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.block.store") }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group {{ $errors->has('projects_id') ? 'has-error' : '' }}">
                {{-- <label for="projects_id">{{ trans('global.project.fields.projects_id') }}*</label> --}}
                <label>Project*</label>
                <select name="project_id" class="form-control">
                    <option value="">-- choose project --</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}"{{ old('code') == $project->id ? ' selected' : '' }}>
                        {{ $project->code }}-{{ $project->name }} {{ $project->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('projects_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('projects_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.project.fields.projects_id_helper') }} --}}
                </p>
            </div>

            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                {{-- <label for="code">{{ trans('global.block.fields.code') }}*</label> --}}
                <label for="code">Code*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ $code}}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.block.fields.block_helper') }} --}}
                </p>
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                {{-- <label for="name">{{ trans('global.block.fields.name') }}*</label> --}}
                <label for="nama">Nama*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($block) ? $block->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.block.fields.name_helper') }} --}}
                </p>
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection