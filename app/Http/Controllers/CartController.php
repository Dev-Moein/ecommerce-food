<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
        public function index(Request $request)
         {
            $addresses = Auth::user()->addresses;
             $cart = $request->session()->get('cart');
            if($cart == null){
                return view('cart.index',compact('cart'));
            }
            $cart_total_price = 0;
            foreach($cart as $key => $item){
                $price = $item['is_sale'] ? $item['sale_price'] : $item['price'];
                $cart_total_price += $price * $item['qty'];
            }
            return view('cart.index',compact('cart','cart_total_price','addresses'));
         }

    public function increment(Request $request)
    {
         $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'required|integer'
        ]);
        $product = Product::findOrFail($request->product_id);

       $cart =$request->session()->get('cart',[]);

       if(isset($cart[$product->id])){
        if($cart[$product->id]["qty"] >= $product->quantity){
            return redirect()->back()->with('error','محصول با بیشترین تعداد ممکن به سبد خرید اضافه شده');
        }
       $cart[$product->id]["qty"]++;
       }else{
          $cart[$product->id] = [
            'name' => $product->name,
            'quantity' => $product->quantity,
            'is_sale' => $product->is_sale,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'primary_image' => $product->primary_image,
            'qty' => 1
        ];
       }
        $request->session()->put('cart',$cart);
        return redirect()->back()->with('success','محصول با موفقیت به سبد خرید اضافه شد');
    }
     public function decrement(Request $request)
    {
         $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'required|integer'
        ]);
        $product = Product::findOrFail($request->product_id);
        if($request->qty == 0){
            return redirect()->back()->with('error','نعداد محصول مورد نظر کمتر از حد مجاز میباشد');
        }
       $cart =$request->session()->get('cart',[]);

       if(isset($cart[$product->id])){

          $cart[$product->id] = [
            'name' => $product->name,
            'quantity' => $product->quantity,
            'is_sale' => $product->is_sale,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'primary_image' => $product->primary_image,
            'qty' => $cart[$product->id]['qty'] - 1
        ];
       }
        $request->session()->put('cart',$cart);
        return redirect()->back()->with('warning','محصول با موفقیت از سبد خرید حذف شد');
    }
     public function add(Request $request)
    {
         $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'required|integer'
        ]);
        $product = Product::findOrFail($request->product_id);
        if($request->qty >= $product->quantity){
            return redirect()->back()->with('error','نعداد محصول مورد نظر بیشتر از حد مجاز میباشد');
        }
       $cart =$request->session()->get('cart',[]);

       if(isset($cart[$product->id])){

       $cart[$product->id]["qty"] = $request->qty;
       }else{
          $cart[$product->id] = [
            'name' => $product->name,
            'quantity' => $product->quantity,
            'is_sale' => $product->is_sale,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'primary_image' => $product->primary_image,
            'qty' => $request->qty
        ];
       }
        $request->session()->put('cart',$cart);
        return redirect()->back()->with('success','محصول با موفقیت به سبد خرید اضافه شد');
    }
     public function remove(Request $request)
    {
        $cart = session()->get('cart');

        if(isset($cart[$request->product_id])) {
            unset($cart[$request->product_id]);
        }
        $request->session()->put('cart', $cart);
        return redirect()->back()->with('warning', 'محصول با موفقیت از سبد خرید حذف شد.');
    }
    public function clear()
{
    session()->put('cart', []);
    return redirect()->route('product.menu')->with('success', 'سبد خرید با موفقیت خالی شد.');
}
    public function checkCoupon(Request $request)
{
    $request->validate([
      'code' => 'required|string'
    ]);
    $coupon = Coupon::where('code',$request->code)->where('expired_at','>',Carbon::now())->first();
    if($coupon == null){
        return redirect()->route('cart.index')->withErrors(['code' => 'کد تخفیف وارد شده وجود نداره']);
    }
     if (Order::where('user_id', Auth::id())->where('coupon_id', $coupon->id)->where('payment_status', 1)->exists()) {
                return redirect()->route('cart.index')->withErrors(['code' => 'شما قبلا از این کد تخفیف استفاده کرده اید']);
            }
    $request->session()->put('coupon',['code' => $coupon->code,'percent' => $coupon->percentage]);
    return redirect()->route('cart.index');
}
}
