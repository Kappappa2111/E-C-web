<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Slider;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Rules\Captcha;
use Barryvdh\DomPDF\Facade as PDF;
use Shipping;

session_start();

class CheckoutController extends Controller
{
    private $order;

    public function vnpay_payment(Request $request)
    {
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "http://127.0.0.1:8000/checkout";
        $vnp_TmnCode = "YOU17NPC"; // Mã website tại VNPAY
        $vnp_HashSecret = "NMYCLNXMEPBVEJUNUGMDAZXZGHALDDHL"; // Chuỗi bí mật

        // Tạo mã đơn hàng
        $vnp_TxnRef = time() . '_' . rand(1000, 9999);

        // Thông tin đơn hàng
        $vnp_OrderInfo = 'Thanh toán đơn hàng';
        $vnp_OrderType = 'billpayment';

        // Lấy tổng tiền từ giỏ hàng
        $totalAmount = 0;
        $content = Cart::content();
        foreach ($content as $v_content) {
            $totalAmount += $v_content->price * $v_content->qty;
        }

        // Chuyển đổi tổng tiền thành đơn vị tiền tệ của VNPAY (VND)
        $vnp_Amount = $totalAmount * 100;

        // Thông tin thanh toán
        $vnp_Locale = 'vn';
        $vnp_BankCode = 'NCB';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        // Các tham số thanh toán
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);

        // Tạo chuỗi hash
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // Lưu thông tin thanh toán vào bảng payment
        $payment_data = array(
            'vnp_txn_ref' => $vnp_TxnRef,
            'vnp_amount' => $vnp_Amount,
            'payment_method' => $vnp_OrderType,
            'vnp_response_code' => '',

        );

        $payment_id = DB::table('payment')->insertGetId($payment_data);

        // Lưu thông tin đơn hàng vào bảng order
        $order_data = array(
            'customer_id' => Session::get('customer_id'),
            'shipping_id' => Session::get('shipping_id'),
            'payment_id' => $payment_id,
            'order_total' => round(Cart::total(0, '.', '') / (1 + 0.21), 2),
            'order_status' => 'Đơn hàng đã được thanh toán bằng VNPAY',
            'created_at' => now(),
        );

        $order_id = DB::table('order')->insertGetId($order_data);

        // Lưu chi tiết đơn hàng vào bảng order_details
        foreach ($content as $v_content) {
            $order_d_data = array(
                'order_id' => $order_id,
                'product_id' => $v_content->id,
                'product_name' => $v_content->name,
                'product_price' => $v_content->price,
                'product_sales_quantity' => $v_content->qty,
                'tax' => 0,
            );

            DB::table('order_details')->insert($order_d_data);

            // Giảm số lượng sản phẩm trong CSDL
            $product = DB::table('products')->where('id', $v_content->id)->first();

            if ($product && $product->quantity >= $v_content->qty) {
                DB::table('products')->where('id', $v_content->id)->decrement('quantity', $v_content->qty);
            } else {
                return response()->json(['error' => 'Sản phẩm không đủ số lượng.']);
            }
        }

        // Xử lý thanh toán theo phương thức đã chọn
        if ($vnp_OrderType == 1) {
            // Xử lý thanh toán thẻ ATM
            echo 'Thanh toán thẻ ATM';
        } elseif ($vnp_OrderType == 2) {
            // Xử lý thanh toán tiền mặt
            Cart::destroy();
            return view('home.handcash');
        } else {
            // Xử lý thanh toán thẻ ghi nợ
            echo 'Thanh toán thẻ ghi nợ';
        }
        $returnData = array();
        if (isset($_POST['redirect'])) {
            header('Location: ' . $vnp_Url);
            die();
        } else {
            echo json_encode($returnData);
        }
    }

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
    public function login_checkout()
    {
        return view('home.login_checkout');
    }

    public function add_customer(Request $request)
    {
        $data = array();
        $data['customer_name'] = $request->customer_name;
        $data['customer_phone'] = $request->customer_phone;
        $data['customer_email'] = $request->customer_email;

        $data['customer_password'] = Hash::make($request->customer_password);

        $customer_id = DB::table('customers')->insertGetId($data);

        Session::put('customer_id', $customer_id);
        Session::put('customer_name', $request->customer_name);

        return Redirect::to('/checkout');
    }


    public function checkout()
    {
        return view('home.checkout');
    }

    public function save_checkout_customer(Request $request)
    {
        $data = array();
        $data['customer_id'] = Session::get('customer_id'); // Thêm dòng này
        $data['shipping_name'] = $request->input('shipping_name');
        $data['shipping_phone'] = $request->input('shipping_phone');
        $data['shipping_email'] = $request->input('shipping_email');
        $data['shipping_notes'] = $request->input('shipping_notes');
        $data['shipping_address'] = $request->input('shipping_address');

        $shipping_id = DB::table('shipping')->insertGetId($data);

        Session::put('shipping_id', $shipping_id);

        return redirect('/payment');
    }


    public function payment()
    {
        return view('home.payment');
    }

    public function order_place(Request $request)
    {
        //insert payment_method
        $data = array();
        $data['payment_method'] = $request->payment_option;
        $data['payment_status'] = 'Đang chờ xử lý';
        $payment_id = DB::table('payment')->insertGetId($data);

        //insert order
        $order_data = array();
        $order_data['customer_id'] = Session::get('customer_id');
        $order_data['shipping_id'] = Session::get('shipping_id');
        $order_data['payment_id'] = $payment_id;
        $order_data['order_total'] = round(Cart::total(0, '.', '') / (1 + 0.21), 2);
        $order_data['order_status'] = 'Đang chờ xử lý';
        $order_id = DB::table('order')->insertGetId($order_data);

        //insert order_details
        $content = Cart::content();
        foreach ($content as $v_content) {
            $order_d_data = array();
            $order_d_data['order_id'] = $order_id;
            $order_d_data['product_id'] = $v_content->id;
            $order_d_data['product_name'] = $v_content->name;
            $order_d_data['product_price'] = $v_content->price;
            $order_d_data['product_sales_quantity'] = $v_content->qty;
            $order_d_data['tax'] = 0;

            DB::table('order_details')->insert($order_d_data);
        }

        if ($data['payment_method'] == 1) {
            echo 'Thanh toán thẻ ATM';
        } elseif ($data['payment_method'] == 2) {
            Cart::destroy();
            return view('home.handcash');
        } else {
            echo 'Thanh toán thẻ ghi nợ';
        }

        // return Redirect::to('/payment');
    }


    public function logout_checkout()
    {
        Session::flush();
        return Redirect::to('/login-checkout');
    }

    public function login_customer(Request $request)
    {
        $email = $request->email_account;
        $password = Hash::make($request->password_account);
        $result = DB::table('customers')->where('customer_email', $email)->first();

        if ($result && Hash::check($request->password_account, $result->customer_password)) {
            // Đúng mật khẩu
            Session::put('customer_id', $result->customer_id);
            return Redirect::to('/checkout');
        } else {
            // Sai mật khẩu
            return Redirect::to('/login-checkout');
        }
    }

    public function manage_order()
    {
        $all_order = DB::table('order')
            ->join('customers', 'order.customer_id', '=', 'customers.customer_id')
            ->select('order.*', 'customers.customer_name')
            ->orderBy('order.order_id', 'desc')->get();
        $manage_order = view('admin.order.manage_order')->with('all_order', $all_order);
        return view('admin.order.manage_order', compact('all_order', 'manage_order'));
    }

    public function view_order($orderId)
    {
        $order_by_id = DB::table('order')
            ->join('customers', 'order.customer_id', '=', 'customers.customer_id')
            ->join('shipping', 'order.shipping_id', '=', 'shipping.shipping_id')
            ->join('order_details', 'order.order_id', '=', 'order_details.order_id')
            ->select('order.*', 'customers.*', 'shipping.*', 'order_details.*')
            ->where('order.order_id', $orderId)
            ->get();
        $manager_order_by_id = view('admin.order.view_order')->with('order_by_id', $order_by_id);
        return view('admin.order.view_order', compact('order_by_id', 'manager_order_by_id'));
    }

    public function print_order($checkoutcode)
    {
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($this->print_order_convert($checkoutcode));
        return $pdf->stream();
    }

    public function print_order_convert($checkoutcode)
    {
        // Thực hiện truy vấn SQL để lấy dữ liệu đơn hàng
        $order_by_id = DB::table('order')
            ->join('customers', 'order.customer_id', '=', 'customers.customer_id')
            ->join('shipping', 'order.shipping_id', '=', 'shipping.shipping_id')
            ->join('order_details', 'order.order_id', '=', 'order_details.order_id')
            ->select('order.*', 'customers.*', 'shipping.*', 'order_details.*')
            ->where('order.order_id', $checkoutcode)
            ->get();

        $orderStatus = $order_by_id->first()->order_status; // Lấy trạng thái đơn hàng từ bản ghi đầu tiên

        $output = '<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <style>
                            /* Thêm phần CSS vào đây */
                            @font-face {
                                font-family: "DejaVu Sans";
                                src: url("path/to/your/font.woff2") format("woff2");
                            }

                            table {
                                font-family: "DejaVu Sans", sans-serif;
                                width: 100%;
                                border-collapse: collapse;
                                margin-bottom: 20px;
                            }

                            table, th, td {
                                border: 1px solid #ddd;
                            }

                            th, td {
                                padding: 8px;
                                text-align: left;
                            }

                            h5 {
                                font-weight: bold;
                                padding: 8px;
                                background-color: #d2e2ef;
                                text-align: center;
                                font-family: "DejaVu Sans", sans-serif;
                            }

                            .logo {
                                max-width: 100px;
                                margin-bottom: 10px;
                                border-radius: 50%;
                                vertical-align: middle;
                            }

                            .logo + p {
                                text-align: center;
                                display: inline-block;
                                vertical-align: middle;
                                margin-left: -12px;
                            }

                            h2 {
                                text-align: center;
                                font-family: "DejaVu Sans", sans-serif;
                            }

                            .thongtin1,
                            .thongtin2 {

                                display: inline-block;
                                vertical-align: top;
                                font-family: "DejaVu Sans", sans-serif;
                            }

                            .thongtin2 {
                                font-size: 15px;
                                margin-left: 60px;
                            }

                            h3{
                                text-align: center;
                                font-family: "DejaVu Sans", sans-serif;
                            }

                            span{
                                color: #fcb941;
                            }

                            .thongtin1{
                                color: #fcb941;
                            }
                            p{
                                font-family: "DejaVu Sans", sans-serif;
                            }
                        </style>
                        <title>In Đơn Hàng</title>
                    </head>
                    <body>
                        <div class="thongtin1" >
                            <img class="logo" src="UserLTE/assets/images/demos/demo-3/Logo.jpg" alt="Logo">
                            <p class="shop"><b>GIANG <br> DIGITAL <br> TECHNOLOGIES</b></p>
                        </div>
                        <div class="thongtin2">
                            <p><b>SĐT:</b> 033 712 0073</p>
                            <p><b>Địa chỉ:</b> Nguyễn Thiện Thành, Phường 5, Trà Vinh</p>
                            <p><b>Mã số thuế: </b>02GTT0/01</p>
                        </div>
                        <h2>HÓA ĐƠN BÁN HÀNG <br></h2>

                        <h5><b>THÔNG TIN VẬN CHUYỂN</b></h5>
                        <table>
                            <thead>
                                <tr>
                                    <th scope="col">Tên khách hàng</th>
                                    <th scope="col">Địa chỉ giao hàng</th>
                                    <th scope="col">Số điện thoại</th>
                                    <th scope="col">Ghi chú đơn hàng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>' . $order_by_id->first()->shipping_name . '</td>
                                    <td>' . $order_by_id->first()->shipping_address . '</td>
                                    <td>' . $order_by_id->first()->shipping_phone . '</td>
                                    <td>' . $order_by_id->first()->shipping_notes . '</td>
                                </tr>
                            </tbody>
                        </table>

                        <h5><b>CHI TIẾT ĐƠN HÀNG</b></h5>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 40%;" scope="col">Tên sản phẩm</th>
                                    <th style="width: 5%;" scope="col">Số lượng</th>
                                    <th scope="col">Giá</th>
                                    <th scope="col">Tổng tiền</th>
                                </tr>
                            </thead>
                            <tbody>';
        $totalAmount = 0;
        foreach ($order_by_id as $order) {
            $output .= '<tr>
                                                <td style="width: 40%;">' . $order->product_name . '</td>
                                                <td style="width: 5%; text-align: center;">' . $order->product_sales_quantity . '</td>
                                                <td style="text-align: center;">' . number_format(floatval($order->product_price)) . ' VNĐ</td>
                                                <td style="text-align: center;">' . number_format(floatval($order->product_price * $order->product_sales_quantity)) . ' VNĐ</td>

                                            </tr>';
            $totalAmount += $order->product_price * $order->product_sales_quantity;
        }
        $output .= '
                            </tbody>
                        </table>
                        <p><b>Tổng tiền phải thanh toán:</b> ' . number_format($totalAmount) . ' VNĐ.</p>
                        <p><b>Phương thức thanh toán:</b> ' . $orderStatus . '.</p>
                        <br>
                        <h3><span>GIANG SHOP </span> <br> CẢM ƠN QUÝ KHÁCH ĐÃ MUA SẮM TẠI CỬA HÀNG. </h3>
                    </body>
                </html>';

        return $output;
    }
}