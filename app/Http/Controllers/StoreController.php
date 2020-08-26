<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Resources\CategoryResourceCollection;
use App\Http\Resources\ProductResourceCollection;
use App\Http\Resources\StoreResource;
use App\Http\Resources\StoreResourceCollection;
use App\Product;
use Illuminate\Support\Str;
use App\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    /**
     * Get a validator for an incoming request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'                  => ['required', 'unique:stores', 'max:255', 'min:3'],
            'address'               => ['required'],
            'support_phone'         => ['nullable'],
            'theme_id'              => ['nullable'],
            'category_id'           => ['required'],
            'user_id'               => ['nullable'],
            'about_text'            => ['nullable'],
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        return new StoreResourceCollection(Store::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validator($request->all())->validate();
        $request['slug'] = Str::slug($request['name']);
        $store = Store::create($request->all());
        return new StoreResource($store);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Store $store)
    {
        // if ($request->query('q')) {
        //     return new ProductResourceCollection(Product::where('user_id', $store['user_id'])->get());
        // } elseif ($request->query('category')) {
        //     return new CategoryResourceCollection(Category::where('user_id', $store['user_id'])->get());
        // } else {
            return new StoreResource($store);
        // };
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Store $store)
    {
        $store->update($request->all());
        return new StoreResource($store);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function destroy(Store $store)
    {
        $store->delete();
        return response()->json(['status' => 200, 'message' => 'store deleted successfully']);
    }
}
