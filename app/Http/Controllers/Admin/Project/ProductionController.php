<?php

namespace App\Http\Controllers\Admin\Project;

use App\Account;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Ledger;
use App\Order;
use App\Payreceivable;
use App\Product;
use App\project;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductionController extends Controller
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
        //     // ->where('category', 'material')
        //     // ->FilterRangeDate(null, $from, $to)
        //     ->whereBetween(DB::raw('DATE(register)'), [$from, $to]);
        // // ->orderBy("register", "desc");
        // dd($query->first()->customers);

        // dd($request->all());
        if ($request->ajax()) {

            $query = Order::with('products')
                ->with('customers')
                ->with('projects')
                ->with('accounts')
                ->FilterInput()
                ->FilterCustomer()
                ->FilterStatus()
                ->where('project_type', 'production')
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


            $table->editColumn('project', function ($row) {
                return $row->projects->name ? $row->projects->name : "";
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
        return view('admin.production.index', compact('orders', 'customers'));
    }
    public function create(Request $request)
    {

        $last_code = $this->get_last_code('production');
        $code = acc_code_generate($last_code, 8, 3);
        $products = Product::with('units')->selectRaw('products.*')->join('blocks', 'blocks.id', '=', 'products.block_id')->where('project_id', '=', $request->project_id)->get();
        $projects = project::get();
        // $customers = Customer::select('*')
        //     ->where('def', '=', '0')
        //     ->where('type', '=', 'suplyer')
        //     ->get();
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();
        return view('admin.production.create', compact('products', 'accounts', 'code', 'projects'));
    }
    public function store(Request $request)
    {


        // dd($request->all());
        //get total
        $total_material = 0;
        $total_property = 0;
        $warehouses_id = 1;
        $cekList = [];
        $types = $request->input('types', []);
        // jika cash
        $total = 0;
        // $cogs_total = 0;
        $products = $request->input('products', []);
        $quantities = $request->input('quantities', []);
        $prices = $request->input('prices', []);
        $surface_areas = $request->input('surface_areas', []);
        $building_areas = $request->input('building_areas', []);
        $more_lands = $request->input('more_lands', []);
        // $cogs = $request->input('cogs', []);


        // cek apa barang sudah ada
        for ($product = 0; $product < count($products); $product++) {
            if (!in_array($products[$product], $cekList)) {
                $cekList[] = $products[$product];
            } else {
                return back();
            }

            // $cogs_total += $quantities[$product] * $cogs[$product];
        }

        for ($product = 0; $product < count($products); $product++) {
            $total += $quantities[$product] * $prices[$product];
            Product::where('id', $products[$product])->update([
                'surface_area' => $surface_areas[$product],
                'building_area' => $building_areas[$product],
                'more_land' => $more_lands[$product],
                'price' => $prices[$product],
                'status' =>  $request->project_production_status
            ]);
            // $cogs_total += $quantities[$product] * $cogs[$product];
        }
        // dd($total, $total_material, $total_property);

        // dd($cekList);
        // cek apa barang sudah ada

        /* proceed ledger */
        $ledger_id = null;
        if ($request->project_production_status == "completed") {
            $data = ['register' => $request->input('register'), 'title' => 'Pembangunan Proyek', 'memo' => 'Pembangunan Proyek'];
            $ledger = Ledger::create($data);
            $ledger_id = $ledger->id;
            //set ledger entry arr
            $acc_inv_stock = '20';
            $acc_prty_stock = '117';
            $total_pay = $total;
            $accounts = array($acc_prty_stock);
            $amounts = array($total);
            $types = array('C');

            //push array jurnal
            array_push($accounts, '116');
            array_push($amounts, $total_pay);
            array_push($types, "D");
            //ledger entries

            // dd($accounts, $amounts, $types);
            for ($account = 0; $account < count($accounts); $account++) {
                if ($accounts[$account] != '' && $amounts[$account]) {
                    $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
                }
            }
        }

        /* proceed order, order products, order details (inventory stock) */
        // $ref_def_id = Customer::select('id')
        //     ->Where('def', '=', '1')
        //     ->get();
        // $owner_def = $ref_def_id[0]->id;
        // $customers_id = 0;
        $warehouses_id = 1;
        // dd("jjhgf");
        //set order
        $data = array_merge($request->all(), ['project_production_status' => $request->project_production_status, 'customers_id' => null, 'total' => $total, 'type' => 'manufactur_process', 'category' => 'material', 'project_type' => 'production', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'payment_type' => '']);
        // dd($data);
        $order = Order::create($data);
        // dd("aass");
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
            // $order->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id]);
            if ($request->project_production_status == "completed") {
                $order->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => null]);
            }
            // }
            // }
        }

        return redirect()->route('admin.production.index');
    }
}
