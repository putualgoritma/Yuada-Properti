<?php

namespace App\Http\Controllers\Admin\Order;

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

class BuysController extends Controller
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
                ->where('type', 'po')
                // ->FilterRangeDate(null, $from, $to)
                ->whereBetween(DB::raw('DATE(register)'), [$from, $to])
                ->orderBy("register", "desc");

            $accounts = Account::select('*')
                ->where('accounts_group_id', 1)
                ->get();

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) use ($accounts) {
                $viewGate = '';
                $editGate = '';
                $deleteGate = '';
                $crudRoutePart = 'buy';
                $type = "po";
                $accounts = $accounts;

                return view('partials.datatablesOrders', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'type',
                    'accounts',
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
        return view('admin.buy.index', compact('orders', 'customers'));
    }
    public function create(Request $request)
    {

        $last_code = $this->get_last_code('buy');
        $code = acc_code_generate($last_code, 8, 3);
        $products = Product::with('units')->where('type', '!=', "")->get();
        $customers = Customer::select('*')
            ->where('def', '=', '0')
            ->where('type', '=', 'suplyer')
            ->get();
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();
        return view('admin.buy.create', compact('products', 'customers', 'accounts', 'code'));
    }
    public function store(StoreOrderRequest $request)
    {
        // dd($request->all());
        //get total
        $warehouses_id = 1;

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





        // $memo = "Registrasi Utang";
        // $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo, 'status' => 'approved'];
        // $ledger = Ledger::create($data);
        // $ledger_id = $ledger->id;

        $data = array_merge($request->all(), ['total' => $total, 'type' => 'po', 'status' => 'pending', 'ledgers_id' => 0, 'customers_id' => $request->customers_id, 'payment_type' => 'credit']);
        $order = Order::create($data);

        for ($product = 0; $product < count($products); $product++) {
            if ($products[$product] != '') {
                $order->products()->attach($products[$product], ['quantity' => $quantities[$product], 'price' => $prices[$product]]);
            }
            // dd($order);
        }

        // //set order order details (inventory stock)
        // for ($product = 0; $product < count($products); $product++) {
        //     if ($products[$product] != '') {
        //         // //check if package
        //         // $products_type = Product::select('type')
        //         //     ->where('id', $products[$product])
        //         //     ->get();
        //         // $products_type = json_decode($products_type, false);
        //         // if ($products_type[0]->type == 'package') {
        //         //     $package_items = Package::with('products')
        //         //         ->where('id', $products[$product])
        //         //         ->get();
        //         //     $package_items = json_decode($package_items, false);
        //         //     $package_items = $package_items[0]->products;
        //         //     //loop items
        //         //     foreach ($package_items as $key => $value) {
        //         //         $order->productdetails()->attach($value->id, ['quantity' => $quantities[$product] * $value->pivot->quantity, 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
        //         //         $order->productdetails()->attach($value->id, ['quantity' => $quantities[$product] * $value->pivot->quantity, 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
        //         //     }
        //         // } else {
        //         $order->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $request->customers_id]);
        //         $order->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $request->customers_id]);
        //         // }
        //         // dd($order);
        //     }
        //     // dd('ssss');
        // }
        // dd($order->productdetails());

        // $accounts = array('20', '23');
        // $amounts = array($total, $total);
        // $types = array('D', 'C');
        //ledger entries
        // for ($account = 0; $account < count($accounts); $account++) {
        //     if ($accounts[$account] != '') {
        //         $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
        //     }
        // }
        // dd($ledger);
        // dd($request->all());
        // $data = array_merge($request->all(), ['account_id' => '23', 'account_pay' => '13', 'customer_id' => $request->customers_id, 'label' => $memo, 'order_id' => $order->id, 'type' => 'payable', 'status' => 'approved']);
        // dd($data);
        // $payreceivable = Payreceivable::create($data);
        // dd('ssss');
        //get code trs
        // $last_code = $this->get_last_code('payable_trs');
        // $code = acc_code_generate($last_code, 8, 3);

        // $payreceivable->ledgers()->attach($ledger_id, ['type' => 'D', 'status' => 'approved', 'code' => $code, 'label' => $memo, 'memo' => $request->input('memo'), 'register' => $request->input('register'), 'amount' => $total, 'account_id' => '20']);

        return redirect()->route('admin.buy.index');
    }

    // public function cancel(Request $request)
    // {
    //     $payreceivable = Payreceivable::where('order_id', $request->order_id)->first();
    //     // dd($payreceivable);
    //     Order::where('id', $request->order_id)->update([
    //         'status' => 'cancel'
    //     ]);
    //     // dd($request->all());
    //     if ($payreceivable) {
    //         $total = $payreceivable->amount;
    //         $memo = "Pembatalan Order";
    //         $data = ['register' => date('Y-m-d h:i:s'), 'title' => $memo, 'memo' => $memo, 'status' => 'approved'];
    //         $ledger = Ledger::create($data);
    //         // $ledger_id = $ledger->id;

    //         $accounts = array('19', $request->accounts_id);
    //         $amounts = array($total, $total);
    //         $types = array('C', 'D');
    //         //ledger entries
    //         for ($account = 0; $account < count($accounts); $account++) {
    //             if ($accounts[$account] != '') {
    //                 $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
    //             }
    //         }
    //     }

    //     return back();
    // }
}
