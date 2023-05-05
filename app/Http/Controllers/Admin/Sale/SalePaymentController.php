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

class SalePaymentController extends Controller
{
    use TraitModel;
    public function index(Request $request)
    {
        //$from = !empty($request->from) ? $request->from : date('Y-m-01');
        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');


        // dd($query->get());
        if ($request->ajax()) {
            $query = Order::selectRaw('
            orders.code,
            customers.name,
            payreceivables.amount,
            payreceivables.register
            ')
                ->join('payreceivables', 'payreceivables.order_id', '=', 'orders.id')
                ->join('customers', 'customers.id', '=', 'orders.customers_id')
                ->where('orders.type', 'so')
                // ->FilterRangeDate(null, $from, $to)
                ->whereBetween(DB::raw('DATE(payreceivables.register)'), [$from, $to])
                ->orderBy("payreceivables.register", "desc");


            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = '';
                $editGate = '';
                $deleteGate = '';
                $crudRoutePart = '';

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
                return $row->name ? $row->name : "";
            });

            $table->editColumn('memo', function ($row) {
                return $row->memo ? $row->memo : "";
            });

            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });

            $table->editColumn('register', function ($row) {
                return $row->register ? $row->register : "";
            });

            $table->editColumn('amount', function ($row) {
                return $row->amount ? number_format($row->amount, 2) : "";
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

            $table->rawColumns(['actions', 'placeholder']);

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
        return view('admin.salepayment.index', compact('orders', 'customers'));
    }
    public function create(Request $request)
    {

        $last_code = $this->get_last_code('factur');
        $code = acc_code_generate($last_code, 8, 3);
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

        return view('admin.salePayment.create', compact('orders', 'accounts', 'code'));
    }
    public function store(Request $request)
    {
        // dd($request->all());
        //get total

        // cari detail order
        $order = Order::where('id', $request->order_id)->first();
        // dd($order);
        // cari detail order end


        $total_material = 0;
        $total_property = 0;
        $warehouses_id = 1;
        $types = $request->input('types', []);
        // jika kredit
        $total = $request->total;
        // $cogs_total = 0;
        $products = $request->input('products', []);
        $quantities = $request->input('quantities', []);
        $prices = $request->input('prices', []);
        // $cogs = $request->input('cogs', []);
        // for ($product = 0; $product < count($products); $product++) {
        //     $total += $quantities[$product] * $prices[$product];
        //     // $cogs_total += $quantities[$product] * $cogs[$product];
        // }





        $memo = "Pembayaran DP Penjualan";
        $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo, 'status' => 'approved'];
        $ledger = Ledger::create($data);
        $ledger_id = $ledger->id;

        $accounts = array($request->account_id, '23');
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
        $data = array_merge($request->all(), ['account_id' => $request->account_id, 'account_pay' => '23', 'customer_id' => $order->customers_id, 'label' => $memo, 'order_id' => $order->id, 'amount' => $total, 'type' => 'receivable', 'status' => 'approved']);
        // dd($data);
        $payreceivable = Payreceivable::create($data);
        // dd('ssss');
        //get code trs
        $last_code = $this->get_last_code('receivable_trs');
        $code = acc_code_generate($last_code, 8, 3);

        $payreceivable->ledgers()->attach($ledger_id, ['type' => 'C', 'status' => 'approved', 'code' => $code, 'label' => $memo, 'memo' => $request->input('memo'), 'register' => $request->input('register'), 'amount' => $total, 'account_id' => '23']);
        $payreceivable->ledgers()->attach($ledger_id, ['type' => 'D', 'status' => 'approved', 'code' => $code, 'label' => $memo, 'memo' => $request->input('memo'), 'register' => $request->input('register'), 'amount' => $total, 'account_id' => $request->account_id]);

        // dd($payreceivable);
        return redirect()->route('admin.salepayment.index');
    }
}
