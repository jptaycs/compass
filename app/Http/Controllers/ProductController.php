<?php

namespace App\Http\Controllers;
use Auth;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //gets the id of the authenticated user
        $user = Auth::user();
        $admin = false;
        // get all the products
        $products = Product::all();
        // if there is an authenticated user, check if admin
        // if yes, get only the products with his id
        if ($user != null) {
            if ($user->isAdmin) {
                $admin = true;
                $products = Product::where('user_id', '=', $user->id)->get();
            }
        }
        //pass the data to the view
        return view('shop', ['products' => $products, 'admin' => $admin]);
    }
    



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorizeAdmin();
        return view('addProduct');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'product_price' => ['required', 'numeric', 'min:0'],
            'img' => self::IMAGE_RULES,
        ]);

        Product::create([
            'product_name' => $data['product_name'],
            'price' => $data['product_price'],
            'img' => $this->saveImage($request->file('img')),
            'user_id' => Auth::id(),
        ]);
        return redirect('shop');
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = $this->ownedProduct($id);
        return view('editProduct', ['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = $this->ownedProduct($id);
        $data = $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'product_price' => ['required', 'numeric', 'min:0'],
            'img' => ['nullable', ...array_slice(self::IMAGE_RULES, 1)],
        ]);

        $product->product_name = $data['product_name'];
        $product->price = $data['product_price'];
        if ($request->hasFile('img')) {
            $product->img = $this->saveImage($request->file('img'));
        }
        $product->save();
        return redirect("shop");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->ownedProduct($id)->delete();
        return redirect("shop");
    }

    // Only images, checked by content (not just the name), at most 5 MB. Saved under a random name with the extension of
    // the detected type, so a client-chosen name like "shell.php" or "../x" can never reach the web root.
    private const IMAGE_RULES = ['required', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'];

    private function saveImage(UploadedFile $file): string
    {
        $name = Str::random(40) . '.' . $file->guessExtension();
        $file->move(public_path('img'), $name);
        return $name;
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->isAdmin, 403);
    }

    // An admin manages only the products they added (the shop page already lists only theirs).
    private function ownedProduct($id): Product
    {
        $this->authorizeAdmin();
        $product = Product::findOrFail($id);
        abort_unless($product->user_id === Auth::id(), 403);
        return $product;
    }

}
