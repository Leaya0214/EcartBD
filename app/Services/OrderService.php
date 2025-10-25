<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use Illuminate\Support\Facades\DB;


class OrderService{
    public function createOrder(array $customer, array $cart, float $totalAmount, string $transaction_id){
        return DB::transaction( function() use ($customer,$cart,$totalAmount,$transaction_id){
            $order = Order::create([
                'transaction_id' => $transaction_id,
                'customer_name' => $customer['name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'address' => $customer['address'],
                'opt_address' => $customer['address2'],
                'state' => $customer['state'],
                'zip' => $customer['zip'],
                'amount' => $totalAmount,
                'currency' => 'BDT',
                'status' => 'Pending',
            ]);

            foreach($cart as $item){
                $order->items()->create([
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'discount' => $item['discount'],
                    'image' => $item['image'],
                ]);
            }

            
        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => 'cod',
            'transaction_id' => $transaction_id,
            'status' => 'Pending',
        ]);
             return $order;
        });
    }

    /**
     * Update a payment attempt status and optionally store response
     *
     * @param string $transactionId
     * @param string $status
     * @param array|null $response
     * @return void
     */
    public function updatePaymentAttemptStatus(string $transactionId, string $status, $response = null)
    {
        $attempt = PaymentAttempt::where('transaction_id', $transactionId)->first();
        if (!$attempt) {
            // create a minimal attempt record so we can track it
            $attempt = PaymentAttempt::create([
                'transaction_id' => $transactionId,
                'status' => $status,
                'response' => $response,
            ]);
        } else {
            $attempt->status = $status;
            if ($response !== null) {
                $attempt->response = $response;
            }
            $attempt->save();
        }

        // Update any existing Payment record linked to this transaction
        $payment = Payment::where('transaction_id', $transactionId)->first();
        if ($payment) {
            $payment->status = $status === 'successful' ? 'successful' : $payment->status;
            if ($response !== null) {
                $payment->response = $response;
            }
            $payment->save();
        }
    }

    /**
     * Create or finalize an Order from a PaymentAttempt.
     * If the attempt references an order, mark it paid. Otherwise create order from payload.
     *
     * @param PaymentAttempt $paymentAttempt
     * @param array|null $response
     * @return Order|null
     */
    public function createFinalOrderFromPaymentAttempt($paymentAttempt, $response = null)
    {
        return DB::transaction(function () use ($paymentAttempt, $response) {
            // If the PaymentAttempt already points to an order, mark it as paid/processing
            $order = null;
            if ($paymentAttempt->order_id) {
                $order = Order::find($paymentAttempt->order_id);
            }

            // If there is no order, but attempt has a payload with order data, create one
            if (!$order) {
                $payload = $paymentAttempt->payload ?? [];
                $order = Order::create([
                    'transaction_id' => $paymentAttempt->transaction_id,
                    'customer_name' => $payload['customer']['name'] ?? ($payload['customer_name'] ?? 'Unknown'),
                    'email' => $payload['customer']['email'] ?? ($payload['email'] ?? null),
                    'phone' => $payload['customer']['phone'] ?? ($payload['phone'] ?? null),
                    'address' => $payload['customer']['address'] ?? ($payload['address'] ?? null),
                    'opt_address' => $payload['customer']['address2'] ?? ($payload['opt_address'] ?? null),
                    'state' => $payload['customer']['state'] ?? ($payload['state'] ?? null),
                    'zip' => $payload['customer']['zip'] ?? ($payload['zip'] ?? null),
                    'amount' => $paymentAttempt->amount ?? ($payload['amount'] ?? 0),
                    'currency' => $payload['currency'] ?? 'BDT',
                    'status' => 'Paid',
                ]);

                // If there are items stored in payload, create order items
                if (!empty($payload['items']) && is_array($payload['items'])) {
                    foreach ($payload['items'] as $item) {
                        $order->items()->create([
                            'product_id' => $item['id'] ?? null,
                            'product_name' => $item['name'] ?? null,
                            'price' => $item['price'] ?? 0,
                            'quantity' => $item['quantity'] ?? 1,
                            'discount' => $item['discount'] ?? 0,
                            'image' => $item['image'] ?? null,
                        ]);
                    }
                }
            } else {
                // mark existing order as paid
                $order->status = 'Paid';
                $order->save();
            }

            // Create or update payment record
            $payment = Payment::updateOrCreate(
                ['transaction_id' => $paymentAttempt->transaction_id],
                [
                    'order_id' => $order->id ?? null,
                    'gateway' => 'sslcommerz',
                    'status' => 'successful',
                    'response' => $response,
                ]
            );

            // Link attempt to order if not linked
            if (!$paymentAttempt->order_id && $order) {
                $paymentAttempt->order_id = $order->id;
                if ($response !== null) {
                    $paymentAttempt->response = $response;
                }
                $paymentAttempt->save();
            }

            return $order;
        });
    }
}