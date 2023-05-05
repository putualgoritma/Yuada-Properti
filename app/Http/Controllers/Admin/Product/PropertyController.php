<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\CogsAllocat;
use App\Customer;
use App\Account;
use App\Block;
use App\project;
use App\Unit;
use Yajra\DataTables\Facades\DataTables;

class PropertyController extends Controller
{

    public function getBlock(Request $request)
    {
        $blocks = Block::where('project_id', $request->project_id)
            ->pluck('name', 'id');

        return response()->json($blocks);
    }

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('product_access'), 403);

        $ref_def_id = Customer::select('id')
            ->Where('def', '=', '1')
            ->get();
        $def_id = $ref_def_id[0]->id;
        // $query = Product::selectRaw("products.*,(SUM(CASE WHEN product_order_details.type = 'D' THEN product_order_details.quantity ELSE 0 END) - SUM(CASE WHEN product_order_details.type = 'C' THEN product_order_details.quantity ELSE 0 END)) AS quantity_balance")
        //     ->where('products.type', 'property')
        //     ->leftjoin('product_order_details', 'product_order_details.products_id', '=', 'products.id')
        //     ->FilterStatus()
        //     ->groupBy('products.id');
        // dd($query->get());
        // ajax
        if ($request->ajax()) {

            $query = Product::selectRaw("products.*,(SUM(CASE WHEN product_order_details.type = 'D' THEN product_order_details.quantity ELSE 0 END) - SUM(CASE WHEN product_order_details.type = 'C' THEN product_order_details.quantity ELSE 0 END)) AS quantity_balance")
                ->where('products.type', 'property')
                ->leftjoin('product_order_details', 'product_order_details.products_id', '=', 'products.id')
                ->FilterStatus()
                ->groupBy('products.id');

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'product_show';
                $editGate = 'product_edit';
                $deleteGate = 'product_delete';
                $crudRoutePart = 'products';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('description', function ($row) {
                return $row->description ? $row->description : "";
            });

            $table->editColumn('model', function ($row) {
                return $row->model ? $row->model : "";
            });

            $table->editColumn('price', function ($row) {
                return $row->price ? $row->price : "";
            });

            $table->editColumn('bv', function ($row) {
                return $row->bv ? $row->bv : "";
            });

            $table->editColumn('stock', function ($row) {
                return $row->quantity_balance ? $row->quantity_balance : "";
            });

            $table->rawColumns(['actions', 'placeholder', 'product']);

            // $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        $products = Product::selectRaw("products.*,(SUM(CASE WHEN product_order_details.type = 'D' AND product_order_details.status = 'onhand' AND product_order_details.owner = '" . $def_id . "' THEN product_order_details.quantity ELSE 0 END) - SUM(CASE WHEN product_order_details.type = 'C' AND product_order_details.status = 'onhand' AND product_order_details.owner = '" . $def_id . "' THEN product_order_details.quantity ELSE 0 END)) AS quantity_balance")
            ->where('products.type', 'single')
            ->leftjoin('product_order_details', 'product_order_details.products_id', '=', 'products.id')
            ->FilterStatus()
            ->groupBy('products.id');

        return view('admin.property.index', compact('products'));
    }

    public function create()
    {
        abort_unless(\Gate::allows('product_create'), 403);
        $accounts = Account::where('accounts_group_id', 6)
            ->get();
        $sub_contractors = Customer::where('type', 'sub_contractor')->get();
        $units = Unit::get();
        $projects = project::get();
        //return $accounts;

        return view('admin.property.create', compact('accounts', 'units', 'sub_contractors', 'projects'));
    }

    public function store(StoreProductRequest $request)
    {
        abort_unless(\Gate::allows('product_create'), 403);
        $product = Product::where('block_id', $request->block_id)->where('no', $request->no)->first();
        if ($product) {
            return back()->withError('Data di blok dengan nomor ini sudah ada')->withInput();
        }
        //init
        $accounts = $request->input('accounts', []);
        $amounts = $request->input('amounts', []);
        $cogs = 0;
        //set cogs
        for ($account = 0; $account < count($accounts); $account++) {
            $cogs += $amounts[$account];
        }
        //set bv
        $bruto =  $request->input('price') - $cogs - $request->input('bv');
        //$bv = substr_replace($bruto, '0000', -4, 4);
        $profit = $bruto;

        // //get cogs allocate
        // $cogs=0;
        // $cogsallocats = CogsAllocat::select('account_id', 'allocation')
        //     ->where('allocation', '>', 0)
        //     ->get();
        // $cogsallocats_arr = json_decode($cogsallocats, true);
        // foreach ($cogsallocats_arr as $key => $value) {
        //     $cogs += $request->input('price')*($value['allocation']/100);
        // }

        $data = array_merge($request->all(), ['cogs' => $cogs, 'profit' => $profit, 'type' => 'property']);
        $product = Product::create($data);

        //store to cogs_products
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $product->accounts()->attach($accounts[$account], ['amount' => $amounts[$account]]);
            }
        }

        return redirect()->route('admin.property.index');
    }

    public function edit(Product $product)
    {
        abort_unless(\Gate::allows('product_edit'), 403);

        $accounts = Account::where('accounts_group_id', 6)
            ->get();

        $product->load('accounts');
        //return $product->accounts;

        return view('admin.property.edit', compact('product', 'accounts'));
    }

    public function update(StoreProductRequest $request, Product $product)
    {
        abort_unless(\Gate::allows('product_edit'), 403);

        //init
        $accounts = $request->input('accounts', []);
        $amounts = $request->input('amounts', []);
        $cogs = 0;
        //set cogs
        for ($account = 0; $account < count($accounts); $account++) {
            $cogs += $amounts[$account];
        }

        $data = $request->all();

        $img_path = "/images/products";
        $basepath = str_replace("laravel-admin", "public_html/admin", \base_path());
        $data = $request->all();
        if ($request->file('img') != null) {
            $resource = $request->file('img');
            //$img_name = $resource->getClientOriginalName();
            $name = strtolower($request->input('name'));
            $name = str_replace(" ", "-", $name);
            $img_name = $img_path . "/" . $name . "-" . $product->id . "-01." . $resource->getClientOriginalExtension();
            try {
                //unlink old
                $data = array_merge($data, ['img' => $img_name]);
                $resource->move($basepath . $img_path, $img_name);
            } catch (QueryException $exception) {
                return back()->withError('File is too large!')->withInput();
            }
        }

        // //get cogs allocate
        // $cogs=0;
        // $cogsallocats = CogsAllocat::select('account_id', 'allocation')
        //     ->where('allocation', '>', 0)
        //     ->get();
        // $cogsallocats_arr = json_decode($cogsallocats, true);
        // foreach ($cogsallocats_arr as $key => $value) {
        //     $cogs += $request->input('price')*($value['allocation']/100);
        // }

        //set bv
        $bruto =  $request->input('price') - $cogs - $request->input('bv');
        //$bv = substr_replace($bruto, '0000', -4, 4);
        $profit = $bruto;

        $data = array_merge($data, ['cogs' => $cogs, 'profit' => $profit]);

        $product->update($data);

        $product->accounts()->detach();
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $product->accounts()->attach($accounts[$account], ['amount' => $amounts[$account]]);
            }
        }

        return redirect()->route('admin.property.index');
    }

    public function show(Product $product)
    {
        abort_unless(\Gate::allows('product_show'), 403);

        return view('admin.property.show', compact('product'));
    }

    public function destroy(Product $product)
    {
        abort_unless(\Gate::allows('product_delete'), 403);

        $product->delete();

        return back();
    }

    public function massDestroy(MassDestroyProductRequest $request)
    {
        Product::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
