<?php

namespace App\Http\Controllers\Admin\Sale;

use App\Account;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Ledger;
use App\Order;
use App\Payreceivable;
use App\Product;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SaleInvoiceController extends Controller
{
    use TraitModel;
    public function index(Request $request)
    {
        //$from = !empty($request->from) ? $request->from : date('Y-m-01');
        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');

        // dd($request->all());
        if ($request->ajax()) {

            $query = Order::with('products')
                ->with('customers')
                ->with('accounts')
                ->FilterInput()
                ->FilterCustomer()
                ->FilterStatus()
                ->where('type', 'si')
                // ->FilterRangeDate(null, $from, $to)
                ->whereBetween(DB::raw('DATE(register)'), [$from, $to])
                ->orderBy("register", "desc");

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'order_show';
                $editGate = 'order_edit';
                $deleteGate = 'order_delete';
                $crudRoutePart = 'orders';

                return view('partials.datatablesOrders', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('register', function ($row) {
                return $row->register ? $row->register : "";
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('name', function ($row) {
                if (isset($row->customers->code)) {
                    return $row->customers->code ? $row->customers->code . " - " . $row->customers->name : "";
                } else {
                    return '';
                }
            });

            $table->editColumn('memo', function ($row) {
                return $row->memo ? $row->memo : "";
            });

            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });

            $table->editColumn('status_delivery', function ($row) {
                return $row->status_delivery ? $row->status_delivery : "";
            });

            $table->editColumn('amount', function ($row) {
                return $row->total ? number_format($row->total, 2) : "";
            });

            $table->editColumn('accpay', function ($row) {
                if (isset($row->accounts->code)) {
                    return $row->accounts->name ? $row->accounts->name : "";
                } else {
                    return '';
                }
            });

            $table->editColumn('product', function ($row) {
                $product_list = '<ul>';
                foreach ($row->products as $key => $item) {
                    $product_list .= '<li>' . $item->name . " (" . $item->pivot->quantity . " x " . number_format($item->price, 2) . ")" . '</li>';
                }
                $product_list .= '</ul>';
                return $product_list;
            });

            $table->rawColumns(['actions', 'placeholder', 'product']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        $orders = Order::with('products')
            ->with('customers')
            ->with('accounts');

        $customers = Customer::select('*')
            ->where(function ($query) {
                $query->where('type', 'member')
                    ->orWhere('type', 'agent')
                    ->orWhere('def', '1');
            })
            ->orderBy("name", "asc")
            ->get();

        //return $orders;
        return view('admin.saleinvoice.index', compact('orders', 'customers'));
    }
    public function create(Request $request)
    {

        $last_code = $this->get_last_code('factur');
        $code = acc_code_generate($last_code, 8, 3);
        $products = Product::where('type', '!=', "")->get();
        $customers = Customer::select('*')
            ->where('def', '=', '0')
            ->where('type', '=', 'suplyer')
            ->get();
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();
        return view('admin.saleinvoice.create', compact('products', 'customers', 'accounts', 'code'));
    }
    public function store(StoreOrderRequest $request)
    {
        // dd($request->all());
        //get total
        $total_material = 0;
        $total_property = 0;
        $warehouses_id = 1;
        $types = $request->input('types', []);
        if ($request->paymentType == "cash") {
            // jika cash
            $total = 0;
            // $cogs_total = 0;
            $products = $request->input('products', []);
            $quantities = $request->input('quantities', []);
            $prices = $request->input('prices', []);
            // $cogs = $request->input('cogs', []);
            for ($product = 0; $product < count($products); $product++) {
                $total += $quantities[$product] * $prices[$product];
                if ($types[$product] == "property") {
                    $total_property += $quantities[$product] * $prices[$product];
                } else if ($types[$product] == "material") {
                    $total_material += $quantities[$product] * $prices[$product];
                }

                // $cogs_total += $quantities[$product] * $cogs[$product];
            }
            // dd($total, $total_material, $total_property);

            /* proceed ledger */
            $data = ['register' => $request->input('register'), 'title' => 'Transaksi Pembelian Barang', 'memo' => 'Transaksi Pembelian Barang'];
            $ledger = Ledger::create($data);
            $ledger_id = $ledger->id;
            //set ledger entry arr
            $acc_inv_stock = '20';
            $acc_prty_stock = '116';
            $total_pay = $total;
            $accounts = array($acc_inv_stock, $acc_prty_stock);
            $amounts = array($total_material, $total_property);
            $types = array('C', 'C');
            //if agent get cashback
            // $customer_row = Customer::select('*')
            //     ->Where('id', '=', $request->input('customers_id'))
            //     ->get();
            // if ($customer_row[0]->type == 'agent') {
            //     //get cashback 01
            //     $acc_disc = 68;
            //     $acc_res_cashback = 70;
            //     $networkfee_row = NetworkFee::select('*')
            //         ->Where('code', '=', 'CBA01')
            //         ->get();
            //     $networkfee2_row = NetworkFee::select('*')
            //         ->Where('code', '=', 'CBA02')
            //         ->get();
            //     //LEV 1
            //     $lev_fee_row = NetworkFee::select('*')
            //         ->Where('code', '=', 'LEV')
            //         ->get();
            //     //set ref fee
            //     $ref_fee_row = NetworkFee::select('*')
            //         ->Where('code', '=', 'REF')
            //         ->get();
            //     //set cashback member 1
            //     $cashback_mbr_row = NetworkFee::select('*')
            //         ->Where('code', '=', 'CBM01')
            //         ->get();
            //     //set cashback member 2
            //     $cashback_mbr2_row = NetworkFee::select('*')
            //         ->Where('code', '=', 'CBM02')
            //         ->get();
            //     $cba1 = (($networkfee_row[0]->amount) / 100) * $total;
            //     $cba2 = (($networkfee2_row[0]->amount) / 100) * $total;
            //     $total_disc = $networkfee_row[0]->amount + $networkfee2_row[0]->amount + $lev_fee_row[0]->amount + $ref_fee_row[0]->amount + $cashback_mbr_row[0]->amount + $cashback_mbr2_row[0]->amount;
            //     $amount_disc = (($total_disc) / 100) * $total;
            //     $amount_res_cashback = $amount_disc - $cba1;
            //     $total_pay = $total - $cba1;
            //     //$acc_points = '67';
            //     //push array jurnal
            //     array_push($accounts, $acc_disc, $acc_res_cashback);
            //     array_push($amounts, $amount_disc, $amount_res_cashback);
            //     array_push($types, "D", "C");
            // }
            //push array jurnal
            array_push($accounts, $request->input('accounts_id'));
            array_push($amounts, $total_pay);
            array_push($types, "D");
            //ledger entries

            // dd($accounts, $amounts, $types);
            for ($account = 0; $account < count($accounts); $account++) {
                if ($accounts[$account] != '' && $amounts[$account]) {
                    $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
                }
            }

            /* proceed order, order products, order details (inventory stock) */
            $ref_def_id = Customer::select('id')
                ->Where('def', '=', '1')
                ->get();
            $owner_def = $ref_def_id[0]->id;
            $customers_id = $request->input('customers_id');
            $warehouses_id = 1;
            //set order
            $data = array_merge($request->all(), ['total' => $total, 'type' => 'si', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $customers_id, 'payment_type' => 'cash']);
            $order = Order::create($data);
            //set order products
            for ($product = 0; $product < count($products); $product++) {
                if ($products[$product] != '') {
                    $order->products()->attach($products[$product], ['quantity' => $quantities[$product], 'price' => $prices[$product], 'cogs' => $prices[$product]]);
                }
            }
            //set order order details (inventory stock)
            for ($product = 0; $product < count($products); $product++) {
                // if ($products[$product] != '') {
                //     //check if package
                //     $products_type = Product::select('type')
                //         ->where('id', $products[$product])
                //         ->get();
                //     $products_type = json_decode($products_type, false);
                //     if ($products_type[0]->type == 'package') {
                //         $package_items = Package::with('products')
                //             ->where('id', $products[$product])
                //             ->get();
                //         $package_items = json_decode($package_items, false);
                //         $package_items = $package_items[0]->products;
                //         //loop items
                //         foreach ($package_items as $key => $value) {
                //             $order->productdetails()->attach($value->id, ['quantity' => $quantities[$product] * $value->pivot->quantity, 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $owner_def]);
                //             $order->productdetails()->attach($value->id, ['quantity' => $quantities[$product] * $value->pivot->quantity, 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                //         }
                //     } else {
                $order->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                // $order->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                // }
                // }
            }
        } else if ($request->paymentType == "credit") {
            // jika kredit
            $total = 0;
            // $cogs_total = 0;
            $products = $request->input('products', []);
            $quantities = $request->input('quantities', []);
            $prices = $request->input('prices', []);
            // $cogs = $request->input('cogs', []);
            for ($product = 0; $product < count($products); $product++) {
                $total += $quantities[$product] * $prices[$product];
                // $cogs_total += $quantities[$product] * $cogs[$product];
            }





            $memo = "Registrasi Utang";
            $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo, 'status' => 'approved'];
            $ledger = Ledger::create($data);
            $ledger_id = $ledger->id;

            $data = array_merge($request->all(), ['total' => $total, 'type' => 'si', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $request->customers_id, 'payment_type' => 'credit']);
            $order = Order::create($data);

            for ($product = 0; $product < count($products); $product++) {
                if ($products[$product] != '') {
                    $order->products()->attach($products[$product], ['quantity' => $quantities[$product], 'price' => $prices[$product]]);
                }
                // dd($order);
            }

            //set order order details (inventory stock)
            for ($product = 0; $product < count($products); $product++) {
                if ($products[$product] != '') {
                    // //check if package
                    // $products_type = Product::select('type')
                    //     ->where('id', $products[$product])
                    //     ->get();
                    // $products_type = json_decode($products_type, false);
                    // if ($products_type[0]->type == 'package') {
                    //     $package_items = Package::with('products')
                    //         ->where('id', $products[$product])
                    //         ->get();
                    //     $package_items = json_decode($package_items, false);
                    //     $package_items = $package_items[0]->products;
                    //     //loop items
                    //     foreach ($package_items as $key => $value) {
                    //         $order->productdetails()->attach($value->id, ['quantity' => $quantities[$product] * $value->pivot->quantity, 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                    //         $order->productdetails()->attach($value->id, ['quantity' => $quantities[$product] * $value->pivot->quantity, 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                    //     }
                    // } else {
                    $order->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $request->customers_id]);
                    // $order->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $request->customers_id]);
                    // }
                    // dd($order);
                }
                // dd('ssss');
            }
            // dd($order->productdetails());

            $accounts = array('20', '23');
            $amounts = array($total, $total);
            $types = array('D', 'C');
            //ledger entries
            for ($account = 0; $account < count($accounts); $account++) {
                if ($accounts[$account] != '') {
                    $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
                }
            }
            // dd($ledger);
            // dd($request->all());
            $data = array_merge($request->all(), ['account_id' => '23', 'account_pay' => '13', 'customer_id' => $request->customers_id, 'label' => $memo, 'order_id' => $order->id, 'type' => 'payable', 'status' => 'approved']);
            // dd($data);
            $payreceivable = Payreceivable::create($data);
            // dd('ssss');
            //get code trs
            $last_code = $this->get_last_code('payable_trs');
            $code = acc_code_generate($last_code, 8, 3);

            $payreceivable->ledgers()->attach($ledger_id, ['type' => 'D', 'status' => 'approved', 'code' => $code, 'label' => $memo, 'memo' => $request->input('memo'), 'register' => $request->input('register'), 'amount' => $total, 'account_id' => '20']);
        } else {
        }

        return redirect()->route('admin.saleinvoice.index');
    }

    // dari pesanan
    public function createByOrder(Request $request)
    {

        $last_code = $this->get_last_code('factur');
        $code = acc_code_generate($last_code, 8, 3);
        $products = Product::where('type', '!=', "")->get();
        $orders = Order::with('products')
            ->with('customers')
            ->with('accounts')
            ->where('orders.type', 'so')
            // ->FilterRangeDate(null, $from, $to)
            // ->whereBetween(DB::raw('DATE(register)'), [$from, $to])
            ->orderBy("register", "desc")
            ->get();
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();
        return view('admin.saleinvoice.createByOrder', compact('orders', 'accounts', 'code'));
    }
    public function storeByOrder(Request $request)
    {
        // dd($request->all());
        Order::where('id', $request->order_id)->update(['status' => 'closed']);
        $dp = Order::selectRaw('
        orders.code,
        customers.name,
        payreceivables.amount,
        payreceivables.register
        ')
            ->join('payreceivables', 'payreceivables.order_id', '=', 'orders.id')
            ->join('customers', 'customers.id', '=', 'orders.customers_id')
            ->where('orders.type', 'so')
            ->where('orders.id', $request->order_id)
            ->first();
        $products = Order::selectRaw('order_product.*, products.type, products.id')
            ->join('order_product', 'order_product.order_id', '=', 'orders.id')
            ->join('products', 'order_product.product_id', '=', 'products.id')
            ->where('orders.id', $request->order_id)->get();
        $order = Order::where('id', $request->order_id)->first();
        //get total
        // dd($products);
        $total_material = 0;
        $total_property = 0;
        $warehouses_id = 1;
        $types = $request->input('types', []);
        if ($request->paymentType == "cash") {
            // jika cash
            $total = 0;
            // $cogs_total = 0;
            // $products = $request->input('products', []);
            // $quantities = $request->input('quantities', []);
            // $prices = $request->input('prices', []);
            // $cogs = $request->input('cogs', []);
            foreach ($products as $product) {
                $total += $product->quantity * $product->price;
                if ($product->type == "property") {
                    $total_property += $product->quantity * $product->price;
                } else if ($product->type == "material") {
                    $total_material += $product->quantity * $product->price;
                }

                // $cogs_total += $quantities[$product] * $cogs[$product];
            }
            // dd($total, $total_material, $total_property);
            // dd($total);
            /* proceed ledger */
            if ($dp) {
                $total = $total - $dp->amount;
            }
            $data = ['register' => $request->input('register'), 'title' => 'Transaksi Pembelian Barang', 'memo' => 'Transaksi Pembelian Barang'];
            $ledger = Ledger::create($data);
            $ledger_id = $ledger->id;
            //set ledger entry arr
            $acc_inv_stock = '20';
            $acc_prty_stock = '116';
            $total_pay = $total;
            $accounts = array($acc_inv_stock, $acc_prty_stock);
            $amounts = array($total_material, $total_property);
            $types = array('D', 'D');

            //push array jurnal
            array_push($accounts, $request->input('accounts_id'));
            array_push($amounts, $total_pay);
            array_push($types, "C");
            if ($dp) {
                array_push($accounts, "19");
                array_push($amounts, $dp->amount);
                array_push($types, "C");
            }
            //ledger entries
            // dd($accounts, $amounts, $types);
            // dd($accounts, $amounts, $types);
            for ($account = 0; $account < count($accounts); $account++) {
                if ($accounts[$account] != '' && $amounts[$account]) {
                    $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
                }
            }
            // dd('sss');

            /* proceed order, order products, order details (inventory stock) */
            $ref_def_id = Customer::select('id')
                ->Where('def', '=', '1')
                ->get();
            $owner_def = $ref_def_id[0]->id;
            $customers_id = $order->customers_id;
            $warehouses_id = 1;
            //set order
            $data = array_merge($request->all(), ['total' => $order->total, 'type' => 'si', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $customers_id, 'payment_type' => 'cash']);
            $order = Order::create($data);
            //set order products
            // dd("ssss");
            foreach ($products as $product) {
                if ($product->id != '') {
                    $order->products()->attach($product->id, ['quantity' => $product->quantity, 'price' => $product->price, 'cogs' => $product->price]);
                    $order->productdetails()->attach($product->id, ['quantity' => $product->quantity, 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                }
            }
            //set order order details (inventory stock)
            // for ($product = 0; $product < count($products); $product++) {
            // if ($products[$product] != '') {
            //     //check if package
            //     $products_type = Product::select('type')
            //         ->where('id', $products[$product])
            //         ->get();
            //     $products_type = json_decode($products_type, false);
            //     if ($products_type[0]->type == 'package') {
            //         $package_items = Package::with('products')
            //             ->where('id', $products[$product])
            //             ->get();
            //         $package_items = json_decode($package_items, false);
            //         $package_items = $package_items[0]->products;
            //         //loop items
            //         foreach ($package_items as $key => $value) {
            //             $order->productdetails()->attach($value->id, ['quantity' => $quantities[$product] * $value->pivot->quantity, 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $owner_def]);
            //             $order->productdetails()->attach($value->id, ['quantity' => $quantities[$product] * $value->pivot->quantity, 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
            //         }
            //     } else {
            // $order->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
            // $order->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
            // }
            // }
            // }
        } else if ($request->paymentType == "credit") {
            // jika kredit
            $total = 0;


            // $cogs_total = 0;
            foreach ($products as $product) {
                $total += $product->quantity * $product->price;
                if ($product->type == "property") {
                    $total_property += $product->quantity * $product->price;
                } else if ($product->type == "material") {
                    $total_material += $product->quantity * $product->price;
                }

                // $cogs_total += $quantities[$product] * $cogs[$product];
            }





            $memo = "Utang Pembelian";
            $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo, 'status' => 'approved'];
            $ledger = Ledger::create($data);
            $ledger_id = $ledger->id;

            $data = array_merge($request->all(), ['total' => $order->total, 'type' => 'si', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $order->customers_id, 'payment_type' => 'credit']);
            $order = Order::create($data);

            foreach ($products as $product) {
                if ($product->id != '') {
                    $order->products()->attach($product->id, ['quantity' => $product->quantity, 'price' => $product->price, 'cogs' => $product->price]);
                    $order->productdetails()->attach($product->id, ['quantity' => $product->quantity, 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $order->customers_id]);
                }
            }


            if ($dp) {
                $accounts = array('20', '23', '19');
                $amounts = array($total, ($total - $dp->amount), $dp->amount);
                $types = array('D', 'C', 'c');
            } else {
                $accounts = array('20', '23');
                $amounts = array($total, $total);
                $types = array('D', 'C');
            }
            //ledger entries
            for ($account = 0; $account < count($accounts); $account++) {
                if ($accounts[$account] != '') {
                    $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
                }
            }
            // dd($ledger);
            // dd($request->all());
            $data = array_merge($request->all(), ['account_id' => '23', 'account_pay' => '13', 'customer_id' => $order->customers_id, 'label' => $memo, 'order_id' => $order->id, 'type' => 'payable', 'status' => 'approved']);
            // dd($data);
            $payreceivable = Payreceivable::create($data);
            // dd('ssss');
            //get code trs
            $last_code = $this->get_last_code('payable_trs');
            $code = acc_code_generate($last_code, 8, 3);

            $payreceivable->ledgers()->attach($ledger_id, ['type' => 'D', 'status' => 'approved', 'code' => $code, 'label' => $memo, 'memo' => $request->input('memo'), 'register' => $request->input('register'), 'amount' => $total, 'account_id' => '20']);
        } else {
        }

        return redirect()->route('admin.saleinvoice.index');
    }

    // dari pesanan en




}
