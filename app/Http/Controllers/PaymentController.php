<?php

namespace App\Http\Controllers;

// require_once 'vendor/autoload.php';

use App\Address;
use App\Payment;
use App\Order;
use Illuminate\Http\Request;
use App\PayPalHelper;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return print_r(getenv('PAYPAL_CREDENTIALS' . getenv("PAYPAL_ENVIRONMENT") . '.client_id'));
        $randNo = (string)rand(10000, 20000);
        $paypal = new PayPalHelper();
        $orderData = array(
            'intent' => 'CAPTURE',
            'application_context' =>
            array(
                'return_url' => 'https://example.com/return',
                'cancel_url' => 'https://example.com/cancel',
                'brand_name' => 'EXAMPLE INC',
                'locale' => 'en-US',
                'landing_page' => 'BILLING',
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                'user_action' => 'PAY_NOW',
            ),
            'purchase_units' =>
            array(
                0 =>
                array(
                    'reference_id' => 'PUHF',
                    'description' => 'Sporting Goods',
                    'custom_id' => 'CUST-HighFashions',
                    'soft_descriptor' => 'HighFashions',
                    'amount' =>
                    array(
                        'currency_code' => 'USD',
                        'value' => '220.00',
                        'breakdown' =>
                        array(
                            'item_total' =>
                            array(
                                'currency_code' => 'USD',
                                'value' => '180.00',
                            ),
                            'shipping' =>
                            array(
                                'currency_code' => 'USD',
                                'value' => '20.00',
                            ),
                            'handling' =>
                            array(
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ),
                            'tax_total' =>
                            array(
                                'currency_code' => 'USD',
                                'value' => '20.00',
                            ),
                            'shipping_discount' =>
                            array(
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ),
                        ),
                    ),
                    'items' =>
                    array(
                        0 =>
                        array(
                            'name' => 'T-Shirt',
                            'description' => 'Green XL',
                            'sku' => 'sku01',
                            'unit_amount' =>
                            array(
                                'currency_code' => 'USD',
                                'value' => '90.00',
                            ),
                            'tax' =>
                            array(
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ),
                            'quantity' => '1',
                            'category' => 'PHYSICAL_GOODS',
                        ),
                        1 =>
                        array(
                            'name' => 'Shoes',
                            'description' => 'Running, Size 10.5',
                            'sku' => 'sku02',
                            'unit_amount' =>
                            array(
                                'currency_code' => 'USD',
                                'value' => '45.00',
                            ),
                            'tax' =>
                            array(
                                'currency_code' => 'USD',
                                'value' => '5.00',
                            ),
                            'quantity' => '2',
                            'category' => 'PHYSICAL_GOODS',
                        ),
                    ),
                    'shipping' =>
                    array(
                        'method' => 'United States Postal Service',
                        'name' =>
                        array(
                            'full_name' => 'John Doe',
                        ),
                        'address' =>
                        array(
                            'address_line_1' => '123 Townsend St',
                            'address_line_2' => 'Floor 6',
                            'admin_area_2' => 'San Francisco',
                            'admin_area_1' => 'CA',
                            'postal_code' => '94107',
                            'country_code' => 'US',
                        ),
                    ),
                ),
            ),
        );
        return json_encode($paypal->orderCreate($orderData));
    }

    public function captureOrder()
    {
        $paypalHelper = new PayPalHelper();
        $order = $paypalHelper->orderCapture()['data'];
        $address = Address::create([
            'user_id'           => isset($_SESSION['user']) ?: $_SESSION['user']['id'],
            'contact_names'     => $order['purchase_units'][0]['shipping']['name']['full_name'],
            'address_line_1'    => $order['purchase_units'][0]['shipping']['address']['address_line_1'],
            'address_line_2'    => $order['purchase_units'][0]['shipping']['address']['address_line_2'],
            'admin_area_2'      => $order['purchase_units'][0]['shipping']['address']['admin_area_2'],
            'admin_area_1'      => $order['purchase_units'][0]['shipping']['address']['admin_area_1'],
            'postal_code'       => $order['purchase_units'][0]['shipping']['address']['postal_code'],
            'country_code'      => $order['purchase_units'][0]['shipping']['address']['country_code'],
        ]);
        $payment = Payment::create([
            'txn_id'            => $order['id'],
            'user_id'           => isset($_SESSION['user']) ?: $_SESSION['user']['id'],
            'payment_method'    => 'paypal',
            'payer_email'       => $order['payer']['email_address'],
            'amount'            => $order['purchase_units'][0]['payments']['captures'][0]['amount']['value'],
            'currency_code'     => $order['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'],
            'payment_status'    => $order['status'],
            'address_id'        => $address->id,
            'created_at'        => $order['purchase_units'][0]['payments']['captures'][0]['create_time'],
            'updated_at'        => $order['purchase_units'][0]['payments']['captures'][0]['update_time']
        ]);

        Order::create([
            'user_id'           => '1',
            'address_id'        => $address->id,
            'payment_id'        => $payment->id,
        ]);
        return $this->response->setJSON(['status' => 201, 'message' => 'Order created successfully']);
    }

    public function success()
    {
        $data = [
            'page_name' => 'payment_success',
            'page_title' => 'payment success'
        ];
        return view($this->themePath, $data);
    }
}
