<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Category;
use App\Traits\ApiTrait;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    use ApiTrait;
    /**
     * @OA\Get(
     *     path="/api/get/services",
     *     tags={"Service"},
     *     summary="Service list",
     *     security={{"bearer_token":{}}},
     *     operationId="service-list",
     * 
     *     @OA\Parameter(
     *         name="category_id",
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
    public function get_services(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }
        try{
            $service = Service::where('category_id',$request->category_id)->where('status',1)->get();
            return $this->response($service,'Services List!');
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }

}
