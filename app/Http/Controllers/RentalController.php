<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Rental;

/**
 * @OA\Schema(
 *     schema="Rental",
 *     title="Rental",
 *     description="Rental model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="car_id", type="integer", example=1),
 *     @OA\Property(property="start_date", type="string", format="date", example="2023-03-15"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2023-03-20"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=250.00),
 *     @OA\Property(property="status", type="string", enum={"pending", "completed", "cancelled"}, example="pending"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-03-10T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-03-10T12:00:00Z")
 * )
 */

 class RentalController extends Controller
{


    /**
 * @OA\Get(
 *     path="/api/rentals",
 *     summary="Get a list of all rentals",
 *     tags={"Rentals"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="A list of rentals",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Rental")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error"
 *     )
 * )
 */

    public function index()
    {
        // Fetch all rentals from the database
        $rentals = Rental::all();

        // Return the rentals as a JSON response
        return response()->json($rentals);
    }


    /**
     * @OA\Post(
     *     path="/api/rentals",
     *     summary="Store a new rental",
     *     description="Create a new rental entry",
     *     operationId="storeRental",
     *     tags={"Rentals"},
     * 
 *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "car_id", "start_date", "end_date", "total_amount", "status"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="car_id", type="integer", example=5),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-03-15"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2025-03-20"),
     *             @OA\Property(property="total_amount", type="number", format="float", example=250.75),
     *             @OA\Property(property="status", type="string", example="Confirmed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rental created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rental created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid data")
     * )
     */
    public function store(Request $request)
    {


        $request->validate([
            'user_id' => 'required|exists:users,id',
            'car_id' => 'required|exists:cars,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_amount' => 'required|numeric',
            'status' => 'required|string',
        ]);

        $rental = Rental::create([
            'user_id' => $request->user_id,
            'car_id' => $request->car_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_amount' => $request->total_amount,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Rental created successfully',
            'data' => $rental
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/rentals/{id}",
     *     summary="Show a specific rental",
     *     description="Retrieve a specific rental by ID",
     *     operationId="showRental",
     *     tags={"Rentals"},
     * 
 *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Rental ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rental details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Rental not found")
     * )
     */
    public function show($id)
    {
        $rental = Rental::findOrFail($id);

        return response()->json([
            'data' => $rental
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/rentals/{id}",
     *     summary="Update a rental",
     *     description="Update rental details",
     *     operationId="updateRental",
     *     tags={"Rentals"},
     * 
 *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Rental ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"start_date", "end_date", "total_amount", "status"},
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-03-15"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2025-03-20"),
     *             @OA\Property(property="total_amount", type="number", format="float", example=250.75),
     *             @OA\Property(property="status", type="string", example="Confirmed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rental updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rental updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Rental not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $rental = Rental::findOrFail($id);

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_amount' => 'required|numeric',
            'status' => 'required|string',
        ]);

        $rental->update([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_amount' => $request->total_amount,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Rental updated successfully',
            'data' => $rental
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/rentals/{id}",
     *     summary="Delete a rental",
     *     description="Delete a specific rental by ID",
     *     operationId="deleteRental",
     *     tags={"Rentals"},
     * 
 *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Rental ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rental deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rental deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Rental not found")
     * )
     */
    public function destroy($id)
    {
        $rental = Rental::findOrFail($id);
        $rental->delete();

        return response()->json([
            'message' => 'Rental deleted successfully'
        ]);
    }
}