@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.order.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.invoice.storeByOrder") }}" method="POST" enctype="multipart/form-data">
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
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.order.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($order) ? $order->code : $code) }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.order.fields.code_helper') }}
                </p>
            </div>

            {{-- <div class="form-group {{ $errors->has('customers_id') ? 'has-error' : '' }}">
                <label for="customers_id">{{ trans('global.order.fields.customers_id') }}*</label>
                <select name="customers_id" class="form-control">
                    <option value="">-- choose customer --</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}"{{ old('code') == $customer->id ? ' selected' : '' }}>
                        {{ $customer->code }}-{{ $customer->name }} {{ $customer->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('customers_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customers_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.order.fields.customers_id_helper') }}
                </p>
            </div> --}}


            <div class="form-group {{ $errors->has('orders_id') ? 'has-error' : '' }}">
                {{-- <label for="order_id">{{ trans('global.order.fields.orders_id') }}*</label> --}}
                <label>Pesanan</label>
                <select name="order_id" class="form-control">
                    <option value="">-- choose order --</option>
                    @foreach ($orders as $order)
                        <option value="{{ $order->id }}"{{ old('code') == $order->id ? ' selected' : '' }}>
                        {{ $order->code }}-{{ $order->total }} {{ $order->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('orders_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('orders_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{-- {{ trans('global.order.fields.orders_id_helper') }} --}}
                </p>
            </div>

            {{-- <div class="form-group {{ $errors->has('orders_id') ? 'has-error' : '' }}">
                <label for="orders_id">{{ trans('global.order.fields.orders_id') }}*</label>
                <select name="orders_id" class="form-control">
                    <option value="">-- choose order --</option>
                    @foreach ($orders as $order)
                        <option value="{{ $order->id }}"{{ old('code') == $order->id ? ' selected' : '' }}>
                        {{ $order->code }}-{{ $order->name }} {{ $order->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('orders_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('orders_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.order.fields.orders_id_helper') }}
                </p>
            </div> --}}
            <div class="form-group {{ $errors->has('memo') ? 'has-error' : '' }}">
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
            </div>




            {{-- <div class="card">
                <div class="card-header">
                    Products
                </div>

                <div class="card-body">
                    <table class="table" id="products_table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Sub Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (old('products', ['']) as $index => $oldProduct)
                                <tr id="product{{ $index }}">
                                    <td>
                                        <select name="products[]" class="form-control product_list">
                                            <option value="">-- choose product --</option>
                                            @foreach ($products as $product)
                                                <option data-cogs="{{ $product->cogs }}" data-price="{{ $product->price }}" data-type="{{ $product->type }}" value="{{ $product->id }}"{{ $oldProduct == $product->id ? ' selected' : '' }}>
                                                    {{ $product->name }} (Rp. {{ number_format($product->price, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="cogs[]" value="{{ old('cogs.' . $index) ?? '0' }}" class="cogs_hidden">
                                        <input type="hidden" name="types[]" value="{{ old('types.' . $index) ?? '0' }}" class="types_hidden">
                                    </td>
                                    <td>
                                        <input type="number" name="quantities[]" class="form-control qty_list" value="{{ old('quantities.' . $index) ?? '1' }}" />
                                    </td>
                                    <td>
                                        <input type="number" name="prices[]" class="form-control price_list" value="{{ old('prices.' . $index) ?? '0' }}" />
                                    </td>
                                    <td>
                                    <input type="number" name="subs[]" class="form-control sub_list" value="{{ old('subs.' . $index) ?? '0' }}" readonly />
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="product{{ count(old('products', [''])) }}"></tr>
                        </tbody>
                        {{-- untuk pembelian --}}
                        {{-- <tr>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                            Discount
                            </td>
                            <td>
                            <input type="number" name="discount" class="form-control" value="{{ old('discount') ?? '0' }}" />
                            </td>
                        </tr> --}}
                        {{-- <tr>
                                    <td>
                                    </td>
                                    <td>
                                    </td>
                                    <td>
                                    Total
                                    </td>
                                    <td>
                                    <input type="number" name="total" class="form-control" value="{{ old('total') ?? '0' }}" readonly />
                                    </td>
                                </tr>
                    </table>
                    <div class="row">
                        <div class="col-md-12">
                            <button id="add_row" class="btn btn-default pull-left">+ Add Row</button>
                            <button id='delete_row' class="pull-right btn btn-danger">- Delete Row</button>
                        </div>
                    </div>
                </div>
            </div> --}}


                <div class="form-check">
                <input class="form-check-input paymentType" type="radio" name="paymentType" id="paymentType1" value="cash" onclick="check(this.value)" checked>
                <label class="form-check-label" for="paymentType1">
                  Tunai
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input paymentType" type="radio" name="paymentType" id="paymentType2" value="credit" onclick="check(this.value)">
                <label class="form-check-label" for="paymentType2">
                  Kredit
                </label>
              </div>

              <div class="form-group {{ $errors->has('accounts_id') ? 'has-error' : '' }}">
                <label for="accounts_id">{{ trans('global.order.fields.accounts_id') }}*</label>
                <select name="accounts_id" id ="accounts_id" class="form-control">
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
                    {{ trans('global.order.fields.accounts_id_helper') }}
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
        
        alert(    $(this).find(':selected').data('type'));
        $('tr#'+data_key+' input.cogs_hidden')
        .val(
            $(this).find(':selected').data('cogs')
        );
        $('tr#'+data_key+' input.price_list')
        .val(
            $(this).find(':selected').data('price')
        );
        $('tr#'+data_key+' input.types_hidden')
        .val(
            $(this).find(':selected').data('type')
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
