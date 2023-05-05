@extends('layouts.admin')
@section('content')
@can('product_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.land.create") }}">
                {{ trans('global.add') }} {{ trans('global.land.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.land.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
    <div class="form-group">
    <form action="" id="filtersForm">
                <div class="col-md-12">
                    <div class="row">
                        {{-- <div class="col-md-6">
                            <div class="input-group">
                                <select id="status" name="status" class="form-control">
                                <option value="show">Show</option>
                                <option value="hidden">Hidden</option>
                                </select>
                            </div>
                        </div>
                        <span class="input-group-btn">
                        <input type="submit" class="btn btn-primary" value="Filter">
                    </span> --}}
                    </div>                    
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-lands">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.land.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.land.fields.description') }}
                        </th>
                        {{-- <th>
                            {{ trans('global.land.fields.model') }}
                        </th> --}}
                        <th>
                            {{ trans('global.land.fields.price') }}
                        </th>
                  
                        <th>
                            Stok
                        </th>
                        <th>
                        Satuan
                    </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@section('scripts')
@parent
<script>
    $(function () {
    let searchParams = new URLSearchParams(window.location.search)
    let status = searchParams.get('status')
    if (status) {
        $("#status").val(status);
    }

    let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('product_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    land: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  $('.datatable-lands:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.land.index') }}",
      dataType: "json",
      headers: {'x-csrf-token': _token},
      method: 'GET',
      data: {
        'status':  $("#status").val(),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'name', name: 'name' },
        { data: 'description', name: 'description' },
        // { data: 'model', name: 'model' },
        { data: 'price', name: 'price', searchable: false  },
     
        { data: 'stock', name: 'stock', searchable: false },
        { data: 'satuan', name: 'satuan', searchable: false },
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    pageLength: 100,
    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
  };

  $('.datatable-lands').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });

})

</script>
@endsection
@endsection
