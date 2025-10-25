<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\VendorController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\SliderController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\ProductGalleryController;
use App\Http\Controllers\Backend\SubCategoryController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\CuponController;
use App\Http\Controllers\Frontend\Home;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\AddtoCartController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\Backend\BrandController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\SslCommerzPaymentController;
use App\Models\Order;

/*
|--------------------------------------------------------------------------
| Frontend Route
|--------------------------------------------------------------------------
|*/

// SSLCOMMERZ Start
Route::get('/example1', [SslCommerzPaymentController::class, 'exampleEasyCheckout']);
Route::get('/example2', [SslCommerzPaymentController::class, 'exampleHostedCheckout'])->name("exampleHostedCheckout");

Route::post('/pay', [SslCommerzPaymentController::class, 'index']);
Route::post('/pay-via-ajax', [SslCommerzPaymentController::class, 'payViaAjax']);

Route::post('/success', [SslCommerzPaymentController::class, 'success'])->name('success');
Route::post('/fail', [SslCommerzPaymentController::class, 'fail'])->name('fail');
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);

Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn']);
//SSLCOMMERZ END

// Order result pages used by payment callbacks
Route::get('/order/success/{order_id}', function ($order_id) {
    return view('frontend.pages.order_success', compact('order_id'));
})->name('order.success');

Route::get('/order/fail', function () {
    return view('frontend.pages.order_fail');
})->name('order.fail');

Route::get('/order/cancel', function () {
    return view('frontend.pages.order_cancel');
})->name('order.cancel');

Route::get('/',[Home::class,'index'])->name("home");

//Route for search

Route::get('/categorysearch',[HomeController::class,'categorySearch'])->name('category.search');
Route::get('/productsearch',[HomeController::class,'productSearch'])->name('product.search');
Route::get('/product/{id}/quickview', [HomeController::class, 'quickview']);


//Route for cart
Route::get('/addtocart/{id}',[AddtoCartController::class,'addtocart']);
Route::get('/showcartitems',[AddtoCartController::class,'showcartitems']);
Route::get('/showitem',[AddtoCartController::class,'showitem']);
Route::get('/removeitem/{id}',[AddtoCartController::class,'removeitem']);
Route::get('/viewcart',[AddtoCartController::class,'viewcart'])->name("viewcart");
Route::get('/coupon/{coupon}',[AddtoCartController::class,'coupon']);
Route::post('/update-cart',[AddtoCartController::class,'updateCart'])->name('update.cart');
Route::post('/coupon-apply',[AddtoCartController::class,'applyCoupon'])->name('coupon.apply');


Route::get('/dashboard',[DashboardController::class,'dashboard'])->middleware(['auth'])->name('dashboard');

// Route::get('/dashboard', function () {
//     return view('backend/dashboard');
// })->middleware(['auth','verified'])->name('dashboard');

require __DIR__.'/auth.php';

//Routes for vendor
Route::group(['prefix'=>'/vendor'],function(){
    Route::get('/add',[VendorController::class,'add'])->name('vendor.add');
    Route::get('/manage',[VendorController::class,'view'])->name('vendor.manage');
    Route::post('/store',[VendorController::class,'store']);
    Route::get('/show',[VendorController::class,'show']);
    Route::get('/destroy/{id}',[VendorController::class,'destroy']);
    Route::get('/edit/{id}',[VendorController::class,'edit']);
    Route::get('/change/{id}',[VendorController::class,'change']);
    Route::post('/update/{id}',[VendorController::class,'update']);
});



// Route for Slider
Route::group(['prefix' => '/slider'],function(){
    Route::get('/addSlider',[SliderController::class,'add'])->name('slider.add');
    Route::POST('/storeSlider',[SliderController::class,'store'])->name('slider.store');
    Route::get('/showSlider',[SliderController::class,'show'])->name('slider.show');
    Route::get('/view/{id}',[SliderController::class,'view'])->name('slider.view');
    Route::get('/status/{id}',[SliderController::class,'status'])->name('slider.status');
    Route::get('/delete/{id}',[SliderController::class,'delete'])->name('slider.delete');
    Route::get('/edit/{id}',[SliderController::class,'edit'])->name('slider.edit');
    Route::POST('/update/{id}',[SliderController::class,'update'])->name('slider.update');
    Route::get('/deleteMultiimage/{id}',[SliderController::class,'deleteMultiimage'])->name('multiImage.delete');

    Route::get('/multi}',[SliderController::class,'multiAdd'])->name('slider.multi');
    Route::POST('/multiStore}',[SliderController::class,'multiStore'])->name('multi.store');
    
});

//Route for Section

Route::group(['prefix' => '/section'],function(){
    Route::get('/index', [SectionController::class,'index'])->name('section.index');
    Route::post('/store', [SectionController::class,'store'])->name('section.store');
    Route::post('/update', [SectionController::class,'update'])->name('section.update');
    Route::post('/status/{id}', [SectionController::class,'statusUpdate'])->name('section.status.update');
});
// Route for Category

Route::group(['prefix' => '/category'],function(){
    Route::get('/addCategory',[CategoryController::class,'addCategory'])->name('category.add');
    Route::POST('/storeCategory',[CategoryController::class,'store'])->name('category.store');
    Route::get('/showCategory',[CategoryController::class,'show']);
    Route::get('/deleteCategory/{id}',[CategoryController::class,'destroy']);
    Route::get('/status/{id}',[CategoryController::class,'status']);
    Route::get('/edit/{id}',[CategoryController::class,'edit']);
    Route::POST('/update/{id}',[CategoryController::class,'update'])->name('category.update');
});

// Route group for subcategory 
Route::group(['prefix'=>'/subcategoey'],function(){

    Route::get('subcategoryview',[SubCategoryController::class,'subcategoryview'])->name('subcategoryview');
    
    Route::post('/addsubcategory',[SubCategoryController::class,'addsubcategory']);
    Route::get('/datatable',[SubCategoryController::class,'showtable']);
    Route::get('/deletesubcategory/{id}',[SubCategoryController::class,'delete']);
    Route::get('/updatesubcategoryview/{id}',[SubCategoryController::class,'updateview']);
    Route::post('/updatesubcategory/{id}',[SubCategoryController::class,'update']);
    
    
    });

// Route group for Product
    Route::group(['prefix'=>'/product'],function(){
    Route::get('productview',[ProductController::class,'productview'])->name('productview');
    Route::post('addproduct',[ProductController::class,'addproduct'])->name('addproduct');
    Route::get('manageproductview',[ProductController::class,'manageproductview'])->name('manageproductview');
    Route::get('/manageproduct',[ProductController::class,'manageproduct']);
    Route::get('statuschange/{id}',[ProductController::class,'statuschange'])->name('statuschange');
    Route::get('/deletesubproduct/{id}',[ProductController::class,'delete']);
    Route::get('/updateproductview/{id}',[ProductController::class,'updateproductview']); 
    Route::post('/updateproduct/{id}',[ProductController::class,'updateproduct']);
    });


// Route for Product Gallery
Route::group(['prefix' => '/productGaleery'],function(){
    Route::get('/addProductGaleery',[ProductGalleryController::class,'add'])->name('productGallery.add');
});


// Route for Coupon
Route::group(['prefix'=>'/cupon'],function() {
    route::get('/add',[CuponController::class,'index' ])->name('add');
    route::post('/store',[CuponController::class,'store' ]);
    Route::get('/show',[CuponController::class,'show'])->name('show');
    Route::get('/delete/{id}',[CuponController::class,'destroy']);
    Route::get('/edit/{id}',[CuponController::class,'edit']);
    Route::post('/update/{id}',[CuponController::class,'update'])->name('update');

});

Route::get('/brand',[BrandController::class,'index'])->name('brand.index');
Route::post('/brand/store',[BrandController::class,'store'])->name('brand.store');

Route::get('/order/list',[  OrderController::class,'index'])->name('orders.index');


