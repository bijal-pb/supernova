<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiTrait;
use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Support\Facades\Validator;

class SubServiceController extends Controller
{
    use ApiTrait;
    /**
     * @OA\Get(
     *     path="/api/get/subservices",
     *     tags={"Service"},
     *     summary="Sub Service list",
     *     security={{"bearer_token":{}}},
     *     operationId="sub-service-list",
     * 
     *     @OA\Parameter(
     *         name="service_id",
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
    public function get_sub_services(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }
        try{
            $service = ServiceItem::where('service_id',$request->service_id)->where('status',1)->with('service')->get();
            return $this->response($service,'Service Items List!');
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }

}
