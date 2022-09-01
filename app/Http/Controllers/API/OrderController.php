<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPhoto;
use App\Models\ServiceItem;
use App\Models\Notification;
use App\Models\User;
use App\Traits\ApiTrait;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use ApiTrait;

    /**
     *  @OA\Post(
     *     path="/api/order/place",
     *     tags={"Order"},
     *     summary="Order Place by customer",
     *     security={{"bearer_token":{}}},
     *     operationId="customer-order-place",
     *
     *     @OA\Parameter(
     *         name="order_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="house_no",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notes",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="service_item_id[]",
     *         in="query",
     *         description="value pass like 1,2",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *                @OA\Property(
     *                    property="photos[]",
     *                    description="photos",
     *                    type="array",
     *                    @OA\Items(type="file", format="binary")
     *                 ),
     *                ),
     *          ),
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
    public function order_place(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_date' => 'required',
            'amount' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        DB::beginTransaction();
        try {
            $order = new Order;
            $order_id = '#' . rand(10, 99) . time();
            $order->order_id = $order_id;
            $order->order_by = Auth::id();
            $order->order_date = $request->order_date;
            $order->amount = $request->amount;
            $order->house_no = $request->house_no;
            $order->address = $request->address;
            $order->notes = $request->notes;
            $order->status = 1;
            $order->save();

            if (count($request->service_item_id) > 0) {
                foreach ($request->service_item_id as $sid) {
                    $oi = new OrderItem;
                    $service_item = ServiceItem::find($sid);
                    $oi->order_id = $order->id;
                    $oi->service_item_id = $sid;
                    $oi->amount = $service_item->amount;
                    $oi->save();
                }
            } else {
                return $this->response([], "Service item id required!", false, 404);
            }

            if ($request->hasfile('photos')) {
                foreach ($request->file('photos') as $file) {
                    $name = rand(10000, 99999) . time() . '.' . $file->extension();
                    $file->move(public_path() . '/orderImages/', $name);
                    $op = new OrderPhoto;
                    $op->order_id = $order->id;
                    $op->photo = $name;
                    $op->save();
                }
            }

            DB::commit();
            $login_user = User::find(Auth::id());
            $customer = User::find($order->order_by);
            sendPushNotification($customer->device_token, 'Order Place','You have placed an order '.$order->order_id.' succesfully', 1, $customer->id, $order->id);
            $admin = User::where('type',1)->first();
            sendPushNotification($admin->device_token, 'Order Request','You have new order request', 1, $admin->id, $order->id);
            // $order = Order::find($order->id);
            // $provider = User::find($order->staff_id)->first();
            // sendPushNotification($provider->device_token, 'Order Request','You have new order request', 1, $provider->id, $order->id);

            return $this->response($order, 'Order placed successfully!');
        } catch (Exception $e) {
            DB::rollback();
            return $this->response([], $e->getMessage(), false, 404);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/order/detail",
     *     tags={"Order"},
     *     summary="Order Detail",
     *     security={{"bearer_token":{}}},
     *     operationId="order-detail",
     *
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
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
    public function order_detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {
            $order = Order::with(['provider_detail', 'service_booked','photos','order_by'])->where('id', $request->order_id)->get();
            return $this->response($order, 'Order Detail!');
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }

    /**
     *  @OA\Post(
     *     path="/api/customer/order/accept",
     *     tags={"Customer"},
     *     summary="Order accept or reject",
     *     security={{"bearer_token":{}}},
     *     operationId="customer-order-accept",
     *
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *       @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description=" 1-accept | 2-reject",
     *         @OA\Schema(
     *             type="integer"
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
    public function CustomerOrderAccept(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:1,2',
        ]);
        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {
            if ($request->status == 1) {
                $order = Order::find($request->order_id);
                $order->status = 4;
                $order->save();
                return $this->response('', 'Order accept by customer!');
            }
            if ($request->status == 2) {
                $order = Order::find($request->order_id);
                $order->status = 5;
                $order->save();
                return $this->response('', 'Order reject by customer!');
            }
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/customer/appointment",
     *     tags={"Customer"},
     *     summary="Customer Appointments",
     *     security={{"bearer_token":{}}},
     *     operationId="customer-appointment",
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=true,
     *         description= "1 - inprogress | 2 - pending | 3 - completed ",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
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
    public function customerAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:1,2,3',
        ]);
        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {
            $order = Order::with(['service_booked', 'photos']);
            if ($request->status == 1) {
                $order = $order->where('status', 6);
            }
            if ($request->status == 2) {
                $order = $order->whereIn('status', [1, 2, 4]);
            }
            if ($request->status == 3) {
                $order = $order->whereIn('status', [7, 8]);
            }

            $order = $order->where('order_by', Auth::id())
                ->OrderBy('id', 'desc')->paginate(20);
            return $this->response($order, 'Appointments!');
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/business/appointment",
     *     tags={"Business"},
     *     summary="Business Appointments",
     *     security={{"bearer_token":{}}},
     *     operationId="business-appointment",
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=true,
     *         description= "1 - inprogress | 2 - pending | 3 - completed ",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
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
    public function businessAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:1,2,3',
        ]);
        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {
            $order = Order::with(['service_booked', 'photos','order_by']);
            if ($request->status == 1) {
                $order = $order->where('status', 6);
            }
            if ($request->status == 2) {
                $order = $order->whereIn('status', [1, 2, 4]);
            }
            if ($request->status == 3) {
                $order = $order->whereIn('status', [7, 8]);
            }

            $order = $order->OrderBy('id', 'desc')->paginate(20);
            return $this->response($order, 'Appointments!');
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }

    /**
     *  @OA\Post(
     *     path="/api/business/order/accept",
     *     tags={"Business"},
     *     summary="Order accept or reject",
     *     security={{"bearer_token":{}}},
     *     operationId="business-order-accept",
     *
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *       @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description=" 1-accept | 2-reject",
     *         @OA\Schema(
     *             type="integer"
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
    public function BusinessOrderAccept(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:1,2',
        ]);
        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {
            if ($request->status == 1) {
                $order = Order::find($request->order_id);
                $order->status = 2;
                $order->save();

                $login_user = User::find(Auth::id());
                $customer = User::find($order->order_by);
                sendPushNotification($customer->device_token, 'Order Accept','Your order '.$order->order_id.' has been accepted', 1, $customer->id, $order->id);
                return $this->response('', 'Order is accepted by admin!');
            }
            if ($request->status == 2) {
                $order = Order::find($request->order_id);
                $order->status = 3;
                $order->save();

                $login_user = User::find(Auth::id());
                $customer = User::find($order->order_by);
                sendPushNotification($customer->device_token, 'Order Reject','Your order '.$order->order_id.' has been rejected', 1, $customer->id, $order->id);
                return $this->response('', 'Order is rejected by admin!');
            }
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }

    /**
     *  @OA\Post(
     *     path="/api/order/status/update",
     *     tags={"Order"},
     *     summary="Order status complete or inprogress",
     *     security={{"bearer_token":{}}},
     *     operationId="order-status-update",
     *
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *       @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description=" 1-for inprogress| 2-mark as complete",
     *         @OA\Schema(
     *             type="integer"
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
    public function orderStatusUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:1,2',
        ]);
        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {
            if ($request->status == 1) {
                $order = Order::find($request->order_id);
                $order->status = 6;
                $order->save();

                $login_user = User::find(Auth::id());
                $customer = User::find($order->order_by);
                sendPushNotification($customer->device_token, 'Order Inprogress','Provider has started work for order '.$order->order_id.'.' ,1, $customer->id, $order->id);
                $admin = User::where('type',1)->first();
                sendPushNotification($admin->device_token, 'Order Inprogress','Provider has started work for order '.$order->order_id.'.' , 1, $admin->id, $order->id);
                return $this->response('', 'Order is inprogress!');
            }
            if ($request->status == 2) {
                $order = Order::find($request->order_id);
                $order->status = 8;
                $order->save();

                $login_user = User::find(Auth::id());
                $customer = User::find($order->order_by);
                sendPushNotification($customer->device_token, 'Order Completed','Provider has completed work for order '.$order->order_id.' please make a payment through online/cash mode.' ,1, $customer->id, $order->id,'order');
                $admin = User::where('type',1)->first();
                sendPushNotification($admin->device_token, 'Order Completed','Provider has completed work for order '.$order->order_id.' please make a payment through online/cash mode.' , 1, $admin->id, $order->id);
                
                return $this->response('', 'Order is completed!');
            }
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/staff/appointment",
     *     tags={"Staff"},
     *     summary="Staff Appointments",
     *     security={{"bearer_token":{}}},
     *     operationId="staff-appointment",
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=true,
     *         description= "1 - inprogress | 2 - pending | 3 - completed ",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
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
    public function staffAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:1,2,3',
        ]);
        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {
            $order = Order::with(['service_booked', 'photos','order_by']);
            if ($request->status == 1) {
                $order = $order->where('status', 6);
            }
            if ($request->status == 2) {
                $order = $order->whereIn('status', [1, 2, 4]);
            }
            if ($request->status == 3) {
                $order = $order->whereIn('status', [7, 8]);
            }

            $order = $order->where('staff_id', Auth::id())->OrderBy('id', 'desc')->paginate(20);
            return $this->response($order, 'Appointments!');
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }

    /**
     *  @OA\Post(
     *     path="/api/business/order/update",
     *     tags={"Order"},
     *     summary="Business Order update",
     *     security={{"bearer_token":{}}},
     *     operationId="business-order-update",
     *
     *     @OA\Parameter(
     *         name="order_id",
     *         description="order id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="expense",
     *         description="extra expense",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *       @OA\Parameter(
     *         name="discount",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="staff_id",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         required=true,
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
    public function business_order_update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }

        try {
            $order = Order::find($request->order_id);
            $order->amount = $request->amount;
            $order->expense = $request->expense;
            $order->discount = $request->discount;
            $order->staff_id = $request->staff_id;
            $order->save();

            $order = Order::find($order->id);
            $staff = User::find($order->staff_id);
            sendPushNotification($staff->device_token, 'Order','You have been assinged a new order '.$order->order_id.' ', 1, $staff->id, $order->id);

            return $this->response($order, 'Order updated successfully!');
        } catch (Exception $e) {

            return $this->response([], $e->getMessage(), false, 404);
        }
    }

    /**
     *  @OA\Post(
     *     path="/api/order/image",
     *     tags={"Order"},
     *     summary="Order Image",
     *     security={{"bearer_token":{}}},
     *     operationId="order-image",
     * 
     *      @OA\Parameter(
     *         name="order_id",
     *         description="order id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *                @OA\Property(
     *                    property="photo",
     *                    description="order photo",
     *                    type="array",
     *                    @OA\Items(type="file", format="binary")
     *                 ),
     *                ),
     *          ),
     *     ),
     *
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
    public function order_image(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'photo' => 'required|image|mimes:png,jpeg,jpg,svg,ico|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }
        try {
            $filename = null;
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . $file->getClientOriginalName();
                $file->move(public_path() . '/orderImages/', $filename);
            }
             $order = new OrderPhoto;            
             $order->photo = $filename;
             $order->order_id = $request->order_id;
             $order->save();
            return $this->response($order, 'Order image added successfully!');
            
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false, 404);
        }
    }

}
