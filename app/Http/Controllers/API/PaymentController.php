<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use App\Traits\ApiTrait;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    use ApiTrait;
    /**
     *  @OA\Post(
     *     path="/api/customer/payment",
     *     tags={"Order"},
     *     summary="customer payment",
     *     security={{"bearer_token":{}}},
     *     operationId="customer-payment",
     *
     *     @OA\Parameter(
     *         name="transaction_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *       @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description=" 1-cash | 2-check | 3-card",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     * *     @OA\Parameter(
     *         name="cheque_no",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
     **/
    public function customer_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'type' => 'required|in:1,2,3',
            'cheque_no' => 'required_if:type,2',
        ]);
        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {
            $payment = new OrderPayment;
            $payment->transaction_id = $request->transaction_id;
            $payment->order_id = $request->order_id;
            $payment->amount = $request->amount;
            $payment->type = $request->type;
            if ($payment->type == 2) {
                $payment->cheque_no = $request->cheque_no;
            }
            $payment->save();
            if ($payment->save()) {
                $order_payment = Order::find($request->order_id);
                $order_payment->status = 7;
                $order_payment->save();
            }

            $order = Order::where('id', $request->order_id)->first();
            $customer = User::find(Auth::id());
            sendPushNotification($customer->device_token, 'Order Payment', 'Payment done for ' . $order->order_id . '.', 1, $customer->id, $order->id);

            $admin = User::where('type', 1)->first();
            sendPushNotification($admin->device_token, 'Order Payment', 'Payment done for ' . $order->order_id . '.', 1, $admin->id, $order->id);

            if ($order->staff_id != null && $order->staff_id !== 1) {
                $staff = User::find($order->staff_id);
                sendPushNotification($staff->device_token, 'Order Payment', 'Payment done for ' . $order->order_id . '.', 1, $staff->id, $order->id);

            }
            return $this->response($payment, 'Payment Succesfully!');

        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }
}
