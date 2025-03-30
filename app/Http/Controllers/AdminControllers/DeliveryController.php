<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    /**
     * @OA\Get(
     *      path="/deliveries",
     *      summary="Get all deliveries",
     *      tags={"Delivery Management"},
     *      @OA\Response(response=200, description="List of deliveries")
     * )
     */
    public function index()
    {
        $deliveries = Delivery::all();
        return response()->json($deliveries, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/deliveries/restaurant/{restaurantId}",
     *      summary="Get deliveries by restaurantId with pagination",
     *      tags={"Delivery Management"},
     *      @OA\Parameter(
     *          name="restaurantId",
     *          in="path",
     *          required=true,
     *          description="Unique ID of the restaurant",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          required=false,
     *          description="Page number for pagination",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="List of deliveries for the given restaurantId with pagination",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="customer_id", type="integer", example=5),
     *                  @OA\Property(property="customer_name", type="string", example="John Doe"),
     *                  @OA\Property(property="customer_email", type="string", example="john@example.com"),
     *                  @OA\Property(property="address_1", type="string", example="123 Street Name"),
     *                  @OA\Property(property="address_2", type="string", example="Near Park"),
     *                  @OA\Property(property="phone_number", type="string", example="9876543210"),
     *                  @OA\Property(property="restaurantId", type="string", example="res_12345"),
     *                  @OA\Property(property="pincode", type="integer", example=560001),
     *                  @OA\Property(property="total_amount", type="number", format="float", example=150.50)
     *              )),
     *              @OA\Property(property="links", type="object",
     *                  @OA\Property(property="first", type="string", example="http://api.example.com/deliveries/restaurant/res_12345?page=1"),
     *                  @OA\Property(property="last", type="string", example="http://api.example.com/deliveries/restaurant/res_12345?page=3"),
     *                  @OA\Property(property="prev", type="string", nullable=true),
     *                  @OA\Property(property="next", type="string", example="http://api.example.com/deliveries/restaurant/res_12345?page=2")
     *              ),
     *              @OA\Property(property="meta", type="object",
     *                  @OA\Property(property="current_page", type="integer", example=1),
     *                  @OA\Property(property="from", type="integer", example=1),
     *                  @OA\Property(property="last_page", type="integer", example=3),
     *                  @OA\Property(property="path", type="string", example="http://api.example.com/deliveries/restaurant/res_12345"),
     *                  @OA\Property(property="per_page", type="integer", example=10),
     *                  @OA\Property(property="to", type="integer", example=10),
     *                  @OA\Property(property="total", type="integer", example=25)
     *              )
     *          )
     *      ),
     *      @OA\Response(response=404, description="No deliveries found")
     * )
     */

    public function getDeliveryByRestaurantId($restaurantId)
    {
        $deliveries = DB::table('delivery as d')
            ->join('customers as c', 'd.customer_id', '=', 'c.id')
            ->select(['*'
            ])
            ->where('d.restaurantId', $restaurantId)
            ->paginate(10);

        if ($deliveries->isEmpty()) {
            return response()->json(['message' => 'No deliveries found for this restaurant'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($deliveries, Response::HTTP_OK);
    }


    /**
     * @OA\Post(
     *      path="/deliveries",
     *      summary="Create a new delivery",
     *      tags={"Delivery Management"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"customer_id", "address_1", "phone_number", "restaurantId", "pincode"},
     *              @OA\Property(property="customer_id", type="integer", example=5),
     *              @OA\Property(property="address_1", type="string", example="123 Street Name"),
     *              @OA\Property(property="address_2", type="string", example="Near Park"),
     *              @OA\Property(property="phone_number", type="string", example="9876543210"),
     *              @OA\Property(property="restaurantId", type="string", example="res_12345"),
     *              @OA\Property(property="pincode", type="integer", example=560001)
     *          )
     *      ),
     *      @OA\Response(response=201, description="Delivery created successfully"),
     *      @OA\Response(response=400, description="Validation Error")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer',
            'address_1' => 'required|string',
            'address_2' => 'nullable|string',
            'phone_number' => 'required|string|max:15',
            'restaurantId' => 'required|string',
            'pincode' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $delivery = Delivery::create($request->all());
        return response()->json($delivery, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/deliveries/{id}",
     *      summary="Get delivery by ID",
     *      tags={"Delivery Management"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=200, description="Delivery details"),
     *      @OA\Response(response=404, description="Delivery not found")
     * )
     */
    public function show($id)
    {
        $delivery = Delivery::find($id);
        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($delivery, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/deliveries/{id}",
     *      summary="Update delivery by ID",
     *      tags={"Delivery Management"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"customer_id", "address_1", "phone_number", "restaurantId", "pincode"},
     *              @OA\Property(property="customer_id", type="integer", example=5),
     *              @OA\Property(property="address_1", type="string", example="123 Street Name"),
     *              @OA\Property(property="address_2", type="string", example="Near Park"),
     *              @OA\Property(property="phone_number", type="string", example="9876543210"),
     *              @OA\Property(property="restaurantId", type="string", example="res_12345"),
     *              @OA\Property(property="pincode", type="integer", example=560001)
     *          )
     *      ),
     *      @OA\Response(response=200, description="Delivery updated successfully"),
     *      @OA\Response(response=404, description="Delivery not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $delivery = Delivery::find($id);
        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found'], Response::HTTP_NOT_FOUND);
        }

        $delivery->update($request->all());
        return response()->json($delivery, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/deliveries/{id}",
     *      summary="Delete delivery by ID",
     *      tags={"Delivery Management"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=204, description="Delivery deleted successfully"),
     *      @OA\Response(response=404, description="Delivery not found")
     * )
     */
    public function destroy($id)
    {
        $delivery = Delivery::find($id);
        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found'], Response::HTTP_NOT_FOUND);
        }

        $delivery->delete();
        return response()->json(['message' => 'Delivery deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
