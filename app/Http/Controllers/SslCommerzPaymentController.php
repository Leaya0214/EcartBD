<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Services\OrderService;
use App\Services\Payment\SSLCommerzService;

class SslCommerzPaymentController extends Controller
{

    protected $orderService;
    protected $sslCommerzService;

    public function __construct(OrderService $orderService, SSLCommerzService $sslCommerzService)
    {
        $this->orderService = $orderService;
        $this->sslCommerzService = $sslCommerzService;
    }

    public function exampleEasyCheckout()
    {
        return view('frontend.pages.exampleEasycheckout');
    }

    public function exampleHostedCheckout(Request $request)
    {
        $cart = session()->get('cart');
        $subTotal = (float) $request->subtotal;
        $total = (float) $request->total_price;
        $discount = (float) $request->discount;
        $delivery = (float) $request->delivery_charge;
        // dd($total);
        return view('frontend.pages.exampleHosted', compact('cart', 'subTotal', 'total', 'discount', 'delivery'));
    }


    public function index(Request $request, OrderService $orderService, SSLCommerzService $sslCommerz)
    {
        $cart = session()->get('cart', []);
        $transaction_id = uniqid();

        $customer = [
            'name' => $request->customer_name,
            'email' => $request->customer_email,
            'phone' => $request->customer_mobile,
            'address' => $request->address,
            'address2' => $request->address2,
            'state' => $request->state,
            'zip' => $request->zip,
        ];

        $total = array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'] - $item['discount'];
        }, $cart));

        $order = $orderService->createOrder($customer, $cart, $total, $transaction_id);

        return $sslCommerz->initiatePayment($order);
    }

    public function success(Request $request)
    {
        $response = $request->all();
        $transactionId = $response['tran_id'] ?? null;

        if (!$transactionId) {
            return redirect()->route('order.fail')->with('error', 'Transaction ID not found in success response.');
        }

        // Get the payment attempt record
        $paymentAttempt = PaymentAttempt::where('transaction_id', $transactionId)->first();

        if (!$paymentAttempt) {
            return redirect()->route('order.fail')->with('error', 'Payment attempt record not found for this transaction.');
        }

        // Verify the payment status with SSLCommerz (server-side validation)
        // This method in your SSLCommerzService should call SslCommerzNotification::orderValidate()
        $isValid = $this->sslCommerzService->verifyPayment($response);

        if ($isValid && $response['status'] == 'VALID') {
            // Payment is valid and successful
            // Update the PaymentAttempt status
            $this->orderService->updatePaymentAttemptStatus($transactionId, 'successful', $response);

            // Create the final order ONLY if it doesn't already exist (handle IPN race condition)
            $order = Order::where('transaction_id', $transactionId)->first();
            if (!$order) {
                $order = $this->orderService->createFinalOrderFromPaymentAttempt($paymentAttempt, $response);
                // Clear the cart session after successful order creation
                session()->forget('cart');
            }

            // Redirect to a success page with order details
            return redirect()->route('order.success', ['order_id' => $order->id ?? 'unknown'])
                ->with('message', 'Payment successful and order placed!');
        } else {
            // Payment validation failed or status is not 'VALID'
            // Update the PaymentAttempt status to 'failed'
            $this->orderService->updatePaymentAttemptStatus($transactionId, 'failed', $response);
            return redirect()->route('order.fail')->with('error', 'Payment failed or validation error. Please try again.');
        }
    }

    /**
     * Handles failed payment callback from SSLCommerz.
     * This is typically the customer's browser being redirected back.
     */
    public function fail(Request $request)
    {
        $response = $request->all();
        $transactionId = $response['tran_id'] ?? null;

        if ($transactionId) {
            $this->orderService->updatePaymentAttemptStatus($transactionId, 'failed', $response);
        }

        return redirect()->route('order.fail')->with('error', 'Payment failed.');
    }

    /**
     * Handles cancelled payment callback from SSLCommerz.
     * This is typically the customer's browser being redirected back.
     */
    public function cancel(Request $request)
    {
        $response = $request->all();
        $transactionId = $response['tran_id'] ?? null;

        if ($transactionId) {
            $this->orderService->updatePaymentAttemptStatus($transactionId, 'cancelled', $response);
        }

        return redirect()->route('order.cancel')->with('error', 'Payment cancelled by user.');
    }

    /**
     * Handles Instant Payment Notification (IPN) from SSLCommerz.
     * This is a server-to-server communication. It should return a 200 OK response.
     */
    public function ipn(Request $request)
    {
        // #Received all the payment information from the gateway
        $response = $request->all();
        $transactionId = $response['tran_id'] ?? null;

        if (!$transactionId) {
            return response('Transaction ID not found', 400); // Bad request if no tran_id
        }

        // Get the payment attempt record
        $paymentAttempt = PaymentAttempt::where('transaction_id', $transactionId)->first();

        // If no payment attempt found, it's an unexpected IPN or old transaction, just log and return OK
        if (!$paymentAttempt) {
            \Log::warning("IPN received for unknown transaction_id: {$transactionId}", $response);
            return response('OK', 200);
        }

        // Verify the payment status with SSLCommerz (server-side validation)
        $isValid = $this->sslCommerzService->verifyPayment($response);

        if ($isValid) {
            // IPN is valid, now process based on status
            if ($response['status'] == 'VALID') {
                // Payment successful
                // Update PaymentAttempt status (idempotent, won't change if already 'successful')
                $this->orderService->updatePaymentAttemptStatus($transactionId, 'successful', $response);

                // Create the final order ONLY if it doesn't already exist
                $order = Order::where('transaction_id', $transactionId)->first();
                if (!$order) {
                    $this->orderService->createFinalOrderFromPaymentAttempt($paymentAttempt, $response);
                    // No need to clear session cart here for IPN, as it's server-to-server.
                }
            } else {
                // Payment failed or cancelled via IPN
                $this->orderService->updatePaymentAttemptStatus($transactionId, 'failed', $response);
            }
        } else {
            // IPN validation failed
            \Log::error("SSLCommerz IPN validation failed for transaction: {$transactionId}", $response);
            // Optionally, mark attempt as 'validation_failed' if you have such a status
            $this->orderService->updatePaymentAttemptStatus($transactionId, 'validation_failed', $response);
        }

        // Always return 200 OK for IPN, otherwise SSLCommerz might keep retrying
        return response('OK', 200);
    }
}
