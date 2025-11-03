<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'address_id'  => 'required|integer|exists:user_addresses,id',
            'coupon_code' => 'nullable|string',
        ]);

        if (!session()->has('cart')) {
            return redirect()->route('cart.index')->with('error', 'سبد خرید شما خالی می‌باشد');
        }

        $cart = session('cart');
        $totalAmount = 0;

        foreach ($cart as $key => $item) {
            $product = \App\Models\Product::findOrFail($key);
            if ($product->quantity < $item['qty']) {
                return redirect()->route('cart.index')->with('error', 'تعداد محصول کافی نیست');
            }
            $totalAmount += $product->is_sale ? $product->sale_price * $item['qty'] : $product->price * $item['qty'];
        }

        $couponAmount = 0;
        $coupon = null;
        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)
                ->where('expired_at', '>', Carbon::now())
                ->first();

            if (!$coupon) {
                return redirect()->route('cart.index')->withErrors(['coupon' => 'کد تخفیف معتبر نیست']);
            }

            $couponAmount = ($totalAmount * $coupon->percentage) / 100;
        }

        $payingAmount = $totalAmount - $couponAmount;

        session([
            'cart' => $cart,
            'address_id' => $request->address_id,
            'coupon_id' => $coupon->id ?? null,
            'amounts' => [
                'totalAmount' => $totalAmount,
                'couponAmount' => $couponAmount,
                'payingAmount' => $payingAmount,
            ],
        ]);

        if (app()->environment('local') || !env('ZARINPAL_MERCHANT_ID')) {
            $simAuthority = 'SIM-' . rand(10000, 99999);

            $html = <<<HTML
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>شبیه‌ساز پرداخت</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f8f9fa;display:flex;align-items:center;justify-content:center;height:100vh;}</style>
</head>
<body>
<div class="card shadow p-4 text-center" style="max-width:400px">
<h4 class="mb-3">شبیه‌ساز پرداخت</h4>
<p>مبلغ قابل پرداخت:</p>
<h3 class="text-primary mb-4">{$payingAmount} تومان</h3>
<a href="/payment/verify?manual_status=1&Authority={$simAuthority}&Status=OK" class="btn btn-success w-100 mb-2">پرداخت موفق ✅</a>
<a href="/payment/verify?manual_status=0&Authority={$simAuthority}&Status=NOK" class="btn btn-danger w-100">پرداخت ناموفق ❌</a>
<p class="mt-3 text-muted small">(فقط برای تست)</p>
</div>
</body>
</html>
HTML;

            return response($html);
        }
        // درگاه واقعی ...
    }
public function status(Request $request)
{
    $status = $request->status;

    if ($status == 1) {
        $message = 'پرداخت با موفقیت انجام شد ✅';
        $alert = 'success';
    } else {
        $message = 'پرداخت ناموفق بود ❌';
        $alert = 'danger';
    }

    return view('payment.status', compact('status','message','alert'));
}

  public function verify(Request $request)
{
    $authority = $request->get('Authority');
    $manual = $request->get('manual_status');
    $statusFromGateway = $request->get('Status');

    if (app()->environment('local') || !env('ZARINPAL_MERCHANT_ID')) {

        $isSuccess = ($manual !== null)
            ? ($manual == 1 ? 1 : 0)
            : ($statusFromGateway == 'OK' ? 1 : 0);

        $refNumber = $authority ?: 'SIM-' . rand(10000, 99999);

        app(OrderController::class)->create(
            session('cart'),
            session('address_id'),
            session('coupon_id') ? \App\Models\Coupon::find(session('coupon_id')) : null,
            session('amounts'),
            $authority,    // ✅ token (Authority)
            $isSuccess,    // ✅ status
            $refNumber     // ✅ ref_id
        );

        session()->forget(['cart','address_id','coupon_id','amount','amounts']);

        return redirect()->route('payment.status', ['status' => $isSuccess])
            ->with($isSuccess ? 'success' : 'error', $isSuccess ? 'پرداخت موفق بود ✅' : 'پرداخت ناموفق بود ❌');
    }

    // درگاه واقعی ادامه دارد...
}

}
