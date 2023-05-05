<?php

//Route::get('member/register', 'MembersController@register');


Route::redirect('/', '/login');

Route::redirect('/home', '/admin');

Auth::routes(['register' => false]);

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index')->name('home');

    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');

    Route::resource('permissions', 'PermissionsController');

    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');

    Route::resource('roles', 'RolesController');

    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');

    Route::resource('users', 'UsersController');

    Route::delete('products/destroy', 'ProductsController@massDestroy')->name('products.massDestroy');

    Route::resource('products', 'ProductsController');

    Route::delete('accounts/destroy', 'AccountController@massDestroy')->name('accounts.massDestroy');
    Route::resource('accounts', 'AccountController');
    Route::get('accbalance', 'AccbalanceController@index')->name('accbalance');
    Route::get('balance-trial', 'AccbalanceController@trial')->name('balancetrial');
    Route::get('profit-loss', 'AccbalanceController@profitLoss')->name('profitloss');
    Route::get('accmutation/{id}', 'AccbalanceController@mutation')->name('accmutation');
    Route::get('acc-mutation', 'AccbalanceController@accMutation')->name('acc-mutation');

    Route::delete('cogsallocats/destroy', 'CogsAllocatsController@massDestroy')->name('cogsallocats.massDestroy');
    Route::resource('cogsallocats', 'CogsAllocatsController');

    Route::delete('accountsgroups/destroy', 'AccountsGroupController@massDestroy')->name('accountsgroups.massDestroy');
    Route::resource('accountsgroups', 'AccountsGroupController');

    // Orders
    Route::delete('orders/destroy', 'OrdersController@massDestroy')->name('orders.massDestroy');
    Route::resource('orders', 'OrdersController');
    Route::get('orders/approved/{id}', 'OrdersController@approved')->name('orders.approved');
    Route::put('order-approvedprocess', 'OrdersController@approvedprocess')->name('orders.approvedprocess');

    // Ledgers
    Route::delete('ledgers/destroy', 'LedgersController@massDestroy')->name('ledgers.massDestroy');
    Route::resource('ledgers', 'LedgersController');

    // Packages
    Route::delete('packages/destroy', 'PackagesController@massDestroy')->name('packages.massDestroy');
    Route::resource('packages', 'PackagesController');

    // Productions
    Route::delete('productions/destroy', 'ProductionsController@massDestroy')->name('productions.massDestroy');
    Route::resource('productions', 'ProductionsController');

    // Customers
    Route::delete('customers/destroy', 'CustomersController@massDestroy')->name('customers.massDestroy');
    Route::resource('customers', 'CustomersController');
    Route::get('customer-unblock/{id}', 'CustomersController@unblock')->name('customers.unblock');
    Route::put('customer-unblock-process', 'CustomersController@unblockProcess')->name('customers.unblockprocess');



    // Reset
    Route::get('reset', 'ResetController@index')->name('reset');
    Route::get('reset-all', 'ResetController@resetall')->name('reset-all');

    //test
    Route::get('test', 'TestController@test')->name('test.test');
    Route::get('sms-api', 'OrdersController@smsApi');
    Route::get('net-tree', 'TestController@tree')->name('test.tree');

    // Sale Retur
    Route::delete('salereturs/destroy', 'SaleReturController@massDestroy')->name('salereturs.massDestroy');
    Route::resource('salereturs', 'SaleReturController');

    // Assets
    Route::delete('assets/destroy', 'AssetsController@massDestroy')->name('assets.massDestroy');
    Route::resource('assets', 'AssetsController');

    // Capitals
    Route::delete('capitals/destroy', 'CapitalsController@massDestroy')->name('capitals.massDestroy');
    Route::resource('capitals', 'CapitalsController');


    // Capitalists
    Route::delete('capitalists/destroy', 'CapitalistsController@massDestroy')->name('capitalists.massDestroy');
    Route::resource('capitalists', 'CapitalistsController');
    Route::get('capitalist-unblock/{id}', 'CapitalistsController@unblock')->name('capitalists.unblock');
    Route::put('capitalist-unblock-process', 'CapitalistsController@unblockProcess')->name('capitalists.unblockprocess');

    // Payables
    Route::delete('payables/destroy', 'PayablesController@massDestroy')->name('payables.massDestroy');
    Route::resource('payables', 'PayablesController');
    Route::get('payables-trs/{id}', 'PayableTrsController@indexTrs')->name('payables.indexTrs');
    Route::get('payables-trs-create/{id}', 'PayableTrsController@createTrs')->name('payables.createTrs');
    Route::post('payables-trs-store', 'PayableTrsController@storeTrs')->name('payables.storeTrs');
    Route::get('payables-trs-show/{id}', 'PayableTrsController@showTrs')->name('payables.showTrs');
    Route::get('payables-trs-edit/{id}', 'PayableTrsController@editTrs')->name('payables.editTrs');
    Route::delete('payables-trs-destroy/{id}', 'PayableTrsController@destroyTrs')->name('payables.destroyTrs');

    // Receivables
    Route::delete('receivables/destroy', 'ReceivablesController@massDestroy')->name('receivables.massDestroy');
    Route::resource('receivables', 'ReceivablesController');
    Route::get('receivables-trs/{id}', 'ReceivableTrsController@indexTrs')->name('receivables.indexTrs');
    Route::get('receivables-trs-create/{id}', 'ReceivableTrsController@createTrs')->name('receivables.createTrs');
    Route::post('receivables-trs-store', 'ReceivableTrsController@storeTrs')->name('receivables.storeTrs');
    Route::get('receivables-trs-show/{id}', 'ReceivableTrsController@showTrs')->name('receivables.showTrs');
    Route::get('receivables-trs-edit/{id}', 'ReceivableTrsController@editTrs')->name('receivables.editTrs');
    Route::delete('receivables-trs-destroy/{id}', 'ReceivableTrsController@destroyTrs')->name('receivables.destroyTrs');
    Route::get('statistik', 'StatistikController@index')->name('statistik.index');
    Route::get('statistik/product', 'StatistikController@product')->name('statistik.product');
    Route::get('statistik/member', 'StatistikController@member')->name('statistik.member');
    Route::get('statistik/member-order', 'StatistikController@memberOrder')->name('statistik.memberOrder');

    //order product
    Route::resource('order-product', 'OrderProductsController');

    //order package
    Route::resource('order-package', 'OrderPackagesController');

    //activation cancell

    Route::put('order-cancell', 'OrdersController@cancell')->name('orders.cancell');
    Route::put('order-unblock', 'OrdersController@unblock')->name('orders.unblock');

    // accountlocks
    Route::delete('accountlocks/destroy', 'AccountlockController@massDestroy')->name('accountlocks.massDestroy');
    Route::resource('accountlocks', 'AccountlockController');

    //tree
    Route::get('trees', 'TreeController@index')->name('trees.index');
    Route::get('tree-modal', 'TreeController@treeModal')->name('trees.modal');
    Route::get('tree-view', 'TreeController@tree')->name('trees.view');
});

Route::group(['prefix' => 'admin', 'as' => 'midtrans.', 'namespace' => 'Admin'], function () {
    Route::get('midtrans/finish', 'MidtransController@finishRedirect')->name('finish');
    Route::get('midtrans/unfinish', 'MidtransController@unfinishRedirect')->name('unfinish');
    Route::get('midtrans/failed', 'MidtransController@errorRedirect')->name('error');
    Route::post('midtrans/callback', 'MidtransController@notificationHandlerTopup')->name('notifiactionTopup');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin\Order'], function () {
    Route::get('buy', 'BuysController@index')->name('buy.index');
    Route::get('buy/create', 'BuysController@create')->name('buy.create');
    Route::post('buy/store', 'BuysController@store')->name('buy.store');
    Route::post('buy/cancel', 'BuysController@cancel')->name('buy.cancel');

    Route::get('invoice', 'invoiceController@index')->name('invoice.index');
    Route::get('invoice/create', 'invoiceController@create')->name('invoice.create');
    Route::post('invoice/store', 'invoiceController@store')->name('invoice.store');

    Route::get('invoice/createByOrder', 'invoiceController@createByOrder')->name('invoice.createByOrder');
    Route::post('invoice/storeByOrder', 'invoiceController@storeByOrder')->name('invoice.storeByOrder');

    Route::get('buypayment', 'BuyPaymentController@index')->name('buypayment.index');
    Route::get('buypayment/create', 'BuyPaymentController@create')->name('buypayment.create');
    Route::post('buypayment/store', 'BuyPaymentController@store')->name('buypayment.store');

    // Route::post('midtrans/callback', 'MidtransController@notificationHandlerTopup')->name('notifiactionTopup');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin\Project'], function () {

    Route::get('landcost', 'landcostController@index')->name('landcost.index');
    Route::get('landcost/create', 'landcostController@create')->name('landcost.create');
    Route::post('landcost/store', 'landcostController@store')->name('landcost.store');

    Route::get('contructioncost', 'ContructionCostController@index')->name('contructioncost.index');
    Route::get('contructioncost/create', 'ContructionCostController@create')->name('contructioncost.create');
    Route::post('contructioncost/store', 'ContructionCostController@store')->name('contructioncost.store');

    Route::get('production', 'ProductionController@index')->name('production.index');
    Route::get('production/create', 'ProductionController@create')->name('production.create');
    Route::post('production/store', 'ProductionController@store')->name('production.store');

    Route::resource('project', 'ProjectsController');
    Route::resource('block', 'BlocksController');

    // Route::post('midtrans/callback', 'MidtransController@notificationHandlerTopup')->name('notifiactionTopup');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin\Product'], function () {

    Route::get('property/block', 'PropertyController@getBlock')->name('property.getBlock');
    Route::resource('land', 'LandController');
    Route::resource('property', 'PropertyController');
    // Route::post('midtrans/callback', 'MidtransController@notificationHandlerTopup')->name('notifiactionTopup');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin\Sale'], function () {
    Route::get('sale', 'SaleController@index')->name('sale.index');
    Route::get('sale/create', 'SaleController@create')->name('sale.create');
    Route::post('sale/store', 'SaleController@store')->name('sale.store');

    Route::get('saleinvoice', 'SaleInvoiceController@index')->name('saleinvoice.index');
    Route::get('saleinvoice/create', 'SaleInvoiceController@create')->name('saleinvoice.create');
    Route::post('saleinvoice/store', 'SaleInvoiceController@store')->name('saleinvoice.store');

    Route::get('saleinvoice/createByOrder', 'SaleInvoiceController@createByOrder')->name('saleinvoice.createByOrder');
    Route::post('saleinvoice/storeByOrder', 'SaleInvoiceController@storeByOrder')->name('saleinvoice.storeByOrder');

    Route::get('salepayment', 'SalePaymentController@index')->name('salepayment.index');
    Route::get('salepayment/create', 'SalePaymentController@create')->name('salepayment.create');
    Route::post('salepayment/store', 'SalePaymentController@store')->name('salepayment.store');

    // Route::post('midtrans/callback', 'MidtransController@notificationHandlerTopup')->name('notifiactionTopup');
});


Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin\OtherCost'], function () {

    Route::get('ordercost', 'OrderCostController@index')->name('ordercost.index');
    Route::get('ordercost/create', 'OrderCostController@create')->name('ordercost.create');
    Route::post('ordercost/store', 'OrderCostController@store')->name('ordercost.store');

    Route::get('ordercompensation', 'OrderCompensationController@index')->name('ordercompensation.index');
    Route::get('ordercompensation/create', 'OrderCompensationController@create')->name('ordercompensation.create');
    Route::post('ordercompensation/store', 'OrderCompensationController@store')->name('ordercompensation.store');

    // Route::post('midtrans/callback', 'MidtransController@notificationHandlerTopup')->name('notifiactionTopup');
});
