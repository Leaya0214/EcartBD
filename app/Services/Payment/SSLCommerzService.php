<?php
namespace App\Services\Payment;

use App\Library\SslCommerz\SslCommerzNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use Illuminate\Support\Facades\Log;

class SSLCommerzService
{
    public function initiatePayment(Order $order)
    {
        $post_data = [];

        $post_data['total_amount'] = $order->amount;
        $post_data['currency'] = $order->currency;
        $post_data['tran_id'] = $order->transaction_id;

        $post_data['cus_name'] = $order->customer_name;
        $post_data['cus_email'] = $order->email;
        $post_data['cus_add1'] = $order->address;
        $post_data['cus_add2'] = $order->opt_address;
        $post_data['cus_city'] = $order->state;
        $post_data['cus_postcode'] = $order->zip;
        $post_data['cus_country'] = 'Bangladesh';
        $post_data['cus_phone'] = $order->phone;

        $post_data['success_url'] = route('success');
        $post_data['fail_url'] = route('fail');
        // $post_data['cancel_url'] = route('cancel');
        $total_order = OrderItem::where('order_id', $order->id)->count();

        $post_data['shipping_method'] = "Home Delivery";
        $post_data['num_of_item'] = $total_order;
        $post_data['ship_name'] = "Home Delivery";
        $post_data['ship_add1'] = $order->address;
        $post_data['ship_city'] = $order->state;
        $post_data['ship_postcode'] = $order->zip;
        $post_data['ship_country'] = 'Bangladesh';
        $post_data['product_name'] = "E-Commerce Products";
        $post_data['product_category'] = "General";
        $post_data['product_profile'] = "physical-goods";
        $post_data['product_amount'] = $order->amount;

        $sslc = new SslCommerzNotification();

        // Create a payment attempt record so callbacks can find context
        try {
            PaymentAttempt::create([
                'order_id' => $order->id,
                'transaction_id' => $order->transaction_id,
                'status' => 'initiated',
                'amount' => $order->amount,
                'payload' => [
                    'order' => $order->toArray(),
                    'items' => $order->items()->get()->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            // do not break payment flow if attempt record couldn't be created
            Log::warning('Could not create PaymentAttempt: ' . $e->getMessage());
        }

        return $sslc->makePayment($post_data, 'hosted');
    }

    /**
     * Verify payment response from SSLCommerz using their notification helper
     * Returns true if validation passes, false otherwise
     * @param array $response
     * @return bool
     */
    public function verifyPayment(array $response): bool
    {
        $sslc = new SslCommerzNotification();

        $transactionId = $response['tran_id'] ?? '';
        $amount = isset($response['amount']) ? (float)$response['amount'] : 0;
        $currency = $response['currency'] ?? ($response['currency_type'] ?? 'BDT');

        try {
            return $sslc->orderValidate($response, $transactionId, $amount, $currency) === true;
        } catch (\Exception $e) {
            Log::error('Error validating SSLCommerz response: ' . $e->getMessage());
            return false;
        }
    }

    // ...existing methods retained above
}
