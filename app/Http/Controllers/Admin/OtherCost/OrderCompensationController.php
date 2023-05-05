<?php

namespace App\Http\Controllers\Admin\OtherCost;


use App\Account;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Ledger;
use App\Order;
use App\OrderCompensation;
use App\Payreceivable;
use App\Product;
use App\project;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class OrderCompensationController extends Controller
{
    use TraitModel;
    public function index(Request $request)
    {
        //$from = !empty($request->from) ? $request->from : date('Y-m-01');
        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');

        // $query = Order::with('products')
        //     ->with('customers')
        //     ->with('projects')
        //     ->with('accounts')
        //     ->FilterInput()
        //     ->FilterCustomer()
        //     ->FilterStatus()
        //     // ->where('category', 'land')
        //     // ->FilterRangeDate(null, $from, $to)
        //     ->whereBetween(DB::raw('DATE(register)'), [$from, $to]);
        // // ->ordersBy("register", "desc");
        // dd($query->first()->customers);


        // $query = Order::with('products')
        //     ->with('orderCompensations')
        //     ->with('customers')
        //     ->with('accounts')
        //     ->FilterInput()
        //     ->FilterCustomer()
        //     ->FilterStatus()
        //     // ->FilterRangeDate(null, $from, $to)
        //     ->whereBetween(DB::raw('DATE(register)'), [$from, $to])
        //     ->orderBy("register", "desc");

        // dd($query->get());
        if ($request->ajax()) {

            $query = Order::select('orders.*')->join('order_compensations', 'order_compensations.order_id', '=', 'orders.id')->with('products')
                ->with('orderCompensations')
                ->with('customers')
                ->with('accounts')
                ->FilterInput()
                ->FilterCustomer()
                ->FilterStatus()
                ->groupBy('orders.id')
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

            $table->editColumn('created_at', function ($row) {
                // return $row->orderCompensations->created_at ? $row->orderCompensations->created_at : "";
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });


            $table->editColumn('order', function ($row) {
                return $row->name ? $row->name : "";
            });


            $table->editColumn('item_name', function ($row) {
                // return $row->orderCompensations->item_name ? $row->orderCompensations->item_name : "";
            });

            $table->editColumn('cost', function ($row) {
                // return $row->orderCompensations->cost ? $row->orderCompensations->cost : "";
            });

            // $table->editColumn('name', function ($row) {
            //     if (isset($row->customers->code)) {
            //         return $row->customers->code ? $row->customers->code . " - " . $row->customers->name : "";
            //     } else {
            //         return '';
            //     }
            // });

            $table->editColumn('product', function ($row) {
                $product_list = '<ul>';
                foreach ($row->products as $key => $item) {
                    $product_list .= '<li>' . $item->name . " (" . $item->pivot->quantity . " x " . number_format($item->price, 2) . ")" . '</li>';
                }
                $product_list .= '</ul>';
                return $product_list;
            });

            $table->editColumn('ordercompensation', function ($row) {
                $product_list = '<ul>';
                foreach ($row->orderCompensations as $key => $item) {
                    $product_list .= '<li>' . $item->item_name . " (" . number_format($item->compensation_cost, 2) . ")" . '</li>';
                }
                $product_list .= '</ul>';
                return $product_list;
            });

            $table->rawColumns(['actions', 'placeholder', 'product', 'ordercompensation']);

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
        return view('admin.ordercompensation.index', compact('orders', 'customers'));
    }
    public function create(Request $request)
    {

        $last_code = $this->get_last_code('order');
        $code = acc_code_generate($last_code, 8, 3);
        $products = Product::with('units')->where('type', '=', "land")->get();
        $orders = Order::where('type', 'si')->get();
        // $customers = Customer::select('*')
        //     ->where('def', '=', '0')
        //     ->where('type', '=', 'suplyer')
        //     ->get();
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();
        return view('admin.ordercompensation.create', compact('products', 'accounts', 'code', 'orders'));
    }
    public function store(Request $request)
    {
        // dd($request->all());
        //get total
        $total_material = 0;
        $total_property = 0;
        $warehouses_id = 1;
        $types = $request->input('types', []);
        // jika cash
        $total = 0;
        $total_amount = 0;
        $total_cost = 0;
        // $cogs_total = 0;
        $products = $request->input('item_name', []);
        $cost = $request->input('compensation_cost', []);
        $amount = $request->input('compensation_amount', []);
        $order = Order::where('id', $request->order_id)->first();
        // dd($request->all());
        // dd($order);
        // $cogs = $request->input('cogs', []);
        for ($product = 0; $product < count($products); $product++) {
            $total += $amount[$product] - $cost[$product];
            // $cogs_total += $quantities[$product] * $cogs[$product];
        }
        // dd($total);
        // dd($total, $total_material, $total_property);

        /* proceed ledger */
        $data = ['register' => $request->input('register'), 'title' => 'Biaya Tambahan', 'memo' => 'Biaya Tambahan'];
        $ledger = Ledger::create($data);
        $ledger_id = $ledger->id;
        //set ledger entry arr
        $acc_kompensasi = '118';
        $acc_pendapatan = '102';
        $total_pay = $total;
        $accounts = array($acc_kompensasi);
        $amounts = array($total);
        $types = array('C');

        //push array jurnal
        array_push($accounts, $request->account_id);
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
        // $ref_def_id = Customer::select('id')
        //     ->Where('def', '=', '1')
        //     ->get();
        // $owner_def = $ref_def_id[0]->id;
        // $customers_id = 0;
        // $warehouses_id = 1;
        // // dd("jjhgf");
        // //set order
        // $data = array_merge($request->all(), ['customers_id' => null, 'total' => $total, 'type' => 'manufactur_process', 'category' => 'land', 'project_type' => 'land', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'payment_type' => '']);
        // // dd($data);
        // $order = Order::create($data);
        // dd("aass");
        //set order products
        for ($product = 0; $product < count($products); $product++) {
            if ($products[$product] != '') {
                OrderCompensation::create(
                    [
                        'order_id' => $order->id,
                        'item_name' => $products[$product],
                        'compensation_cost' => $cost[$product],
                        'compensation_amount' => $amount[$product]
                    ]
                );
            }
        }

        return redirect()->route('admin.ordercompensation.index');
    }
}
