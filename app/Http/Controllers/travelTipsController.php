<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;

class travelTipsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $admin = false;
        // get all the products
        $products = Product::all();


        //pass the data to th view
        return view('travelTips', ['products' => $products, 'admin' => $admin],);
    }


}
