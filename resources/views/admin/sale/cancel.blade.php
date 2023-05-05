@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.order.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.cancelProcess.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('register') ? 'has-error' : '' }}">
                <label for="register">{{ trans('global.order.fields.register') }}*</label>
                <input type="date" id="register" name="register" class="form-control" value="{{ old('register', isset($order) ? $order->register : date('Y-m-d')) }}" required>
                @if($errors->has('register'))
                    <em class="invalid-feedback">
                        {{ $errors->first('register') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.order.fields.register_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('refund') ? 'has-error' : '' }}">
                <label for="refund">{{ trans('global.order.fields.refund') }}*</label>
                <input type="number" id="refund" name="refund" class="form-control" value="{{ old('refund', isset($order) ? $order->refund : date('Y-m-d')) }}" required>
                @if($errors->has('refund'))
                    <em class="invalid-feedback">
                        {{ $errors->first('refund') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.order.fields.refund_helper') }}
                </p>
            </div>

           
            {{-- <div class="form-group {{ $errors->has('memo') ? 'has-error' : '' }}">
                <label for="memo">{{ trans('global.order.fields.memo') }}</label>
                <textarea id="memo" name="memo" class="form-control ">{{ old('memo', isset($product) ? $product->memo : '') }}</textarea>
                @if($errors->has('memo'))
                    <em class="invalid-feedback">
                        {{ $errors->first('memo') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.order.fields.memo_helper') }}
                </p>
            </div> --}}
            <div class="form-group {{ $errors->has('accounts_id') ? 'has-error' : '' }}">
                <label for="account_id">{{ trans('global.account.fields.accounts_id') }}*</label>
                <select name="account_id" class="form-control">
                    <option value="">-- choose account --</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}"{{ old('code') == $account->id ? ' selected' : '' }}>
                        {{ $account->code }}-{{ $account->name }} {{ $account->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('accounts_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('accounts_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.account.fields.accounts_id_helper') }}
                </p>
            </div>
         

          

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
    let row_number = {{ count(old('products', [''])) }};
    $("#add_row").click(function(e){
      e.preventDefault();
      let new_row_number = row_number - 1;
      $('#product' + row_number).html($('#product' + new_row_number).html()).find('td:first-child');
      $('#products_table').append('<tr id="product' + (row_number + 1) + '"></tr>');
      row_number++;
    });

    $("#delete_row").click(function(e){
      e.preventDefault();
      if(row_number > 1){
        $("#product" + (row_number - 1)).html('');
        row_number--;
        
      }
    //   getTotal()
    });

    $(document).on("change", "select.product_list" , function() {
        let data_key = $(this).closest('tr').attr('id');
        let qty = $('tr#'+data_key+' input.qty_list').val();
        let sub = qty * $(this).find(':selected').data('price');
        //alert(data_key);
        $('tr#'+data_key+' input.cogs_hidden')
        .val(
            $(this).find(':selected').data('cogs')
        );
        $('tr#'+data_key+' input.price_list')
        .val(
            $(this).find(':selected').data('price')
        );
        $('tr#'+data_key+' input.unit_list')
        .val(
            $(this).find(':selected').data('unit')
        );
        $('tr#'+data_key+' input.unit_list')
        .val(
            $(this).find(':selected').data('unit')
        );
        $('tr#'+data_key+' input.sub_list')
        .val(sub);
        var sum = 0;
        $('.sub_list').each(function () {
            sum += Number($(this).val());
        });
        $("input[name='total']")
        .val(sum);
    });

    // function getTotal(){
    //     // let data_key = $("input.price_list").closest('tr').attr('id');
    //     // let qty = $('tr#'+data_key+' input.qty_list').val();
    //     // let sub = $("input.price_list").val() * qty;
    //     // $('tr#'+data_key+' input.sub_list')
    //     // .val(sub);
    //     var sum = 0;
    //     var data_key =0;
       
    //     $('.sub_list').each(function () {
    //         // sum += Number($('tr#'+data_key+' input.sub_list').val());
    //         // data_key++;
    //         console.log( Number($('input.sub_list').val()))
    //     });
    //     // console.log(sum)
    //     $("input[name='total']")
    //     .val(sum);
    // }

    $(document).on("change", "input.qty_list" , function() {
        let data_key = $(this).closest('tr').attr('id');
        let price = $('tr#'+data_key+' input.price_list').val();
        let sub = $(this).val() * price;
        $('tr#'+data_key+' input.sub_list')
        .val(sub);
        var sum = 0;
        $('.sub_list').each(function () {
            sum += Number($(this).val());
        });
        $("input[name='total']")
        .val(sum);
    });

    $(document).on("change", "input.price_list" , function() {
        let data_key = $(this).closest('tr').attr('id');
        let qty = $('tr#'+data_key+' input.qty_list').val();
        let sub = $(this).val() * qty;
        $('tr#'+data_key+' input.sub_list')
        .val(sub);
        var sum = 0;
        $('.sub_list').each(function () {
            sum += Number($(this).val());
        });
        $("input[name='total']")
        .val(sum);
    });



    

  });

    // untuk ketik langsung
    $(".input.qty_list").bind('click keyup', function(){
        let data_key = $(this).closest('tr').attr('id');
        let price = $('tr#'+data_key+' input.price_list').val();
        let sub = $(this).val() * price;
        $('tr#'+data_key+' input.sub_list')
        .val(sub);
        var sum = 0;
        $('.sub_list').each(function () {
            sum += Number($(this).val());
        });
        $("input[name='total']")
        .val(sum);
    });

    
  $(".input.price_list").bind('click keyup', function(){
        let data_key = $(this).closest('tr').attr('id');
        let price = $('tr#'+data_key+' input.price_list').val();
        let sub = $(this).val() * price;
        $('tr#'+data_key+' input.sub_list')
        .val(sub);
        var sum = 0;
        $('.sub_list').each(function () {
            sum += Number($(this).val());
        });
        $("input[name='total']")
        .val(sum);
    });

  


  function check(browser) {
//   alert(browser);
  if(browser == "cash"){
    var x= document.getElementById("accounts_id")
  x.disabled=false
  }
  else{
    var x= document.getElementById("accounts_id")
  x.disabled=true
  }
}


  
</script>
@endsection
