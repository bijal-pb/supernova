<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Traits\ApiTrait;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use ApiTrait;
    /**
     *  @OA\Get(
     *     path="/api/customer/home",
     *     tags={"Home"},
     *     security={{"bearer_token":{}}},
     *     summary="Customer Home",
     *     operationId="customer-home",
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
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
    public function customer_home(Request $request)
    {
        try {
            $category = Category::select('id', 'name', 'image')->where('status',1);

            if ($request->search != null) {
                $category = $category->where('categories.name', 'LIKE', '%' . $request->search . '%');
            }
            $category = $category->get();

            $appointment = Order::select('id', 'order_id', 'order_date', 'status')->where('order_by', Auth::id())->where('status', 1)->orderBy('order_date','desc')->get();

            return $this->response([
                'categories' => $category,
                'appointments' => $appointment,
            ], 'Home!');
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false);
        }
    }

    /**
     *  @OA\Get(
     *     path="/api/business/home",
     *     tags={"Home"},
     *     security={{"bearer_token":{}}},
     *     summary="Business Home",
     *     operationId="business-home",
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
    public function business_home(Request $request)
    {
        try {
            $orders = Order::select('id', 'order_id', 'order_date', 'status', 'amount')
                ->with('service_booked')
                ->orderBy('order_date','desc')
                ->get();

            $appointment = Order::whereIn('status', [7, 8])->count();
            $action_require = Order::where('status', 1)->count();
            $pending_orders = Order::where('status', 6)->orderBy('id','desc')->get();
            $total_schedule = Order::whereDate('order_date', carbon::now())
                                     ->whereIn('status', [1, 2, 4, 6])
                                     ->count();

            $total_earning = Order::select(DB::raw("sum(amount) as total_earning"))
                ->whereIn('status', [7, 8])
                ->get();

            $data = [
                'today_schedule' => $total_schedule,
                'overall_earning' => $total_earning,
                'appoinments' => $appointment,
                'action_required' => $action_require,
                'pending_orders' => $pending_orders,
                'order_detail' => $orders,
            ];
            return $this->response($data, 'Business Home');

        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false);
        }
    }

    /**
     *  @OA\Get(
     *     path="/api/staff/home",
     *     tags={"Home"},
     *     security={{"bearer_token":{}}},
     *     summary="Staff Home",
     *     operationId="staff-home",
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
    public function staff_home(Request $request)
    {
        try {
            $orders = Order::select('id', 'order_id', 'order_date', 'status', 'amount')->with('service_booked')->where('staff_id', Auth::id())->orderBy('order_date','desc')->get();
            $completed_order = Order::where('status', 8)->count();
            $total_schedule = Order::whereDate('order_date', carbon::now())->whereIn('status', [1, 2, 4, 6])->count();

            $data = [
                'today_schedule' => $total_schedule,
                'completed_order' => $completed_order,
                'order_detail' => $orders,
            ];
            return $this->response($data, 'Staff Home');

        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false);
        }
    }
}
