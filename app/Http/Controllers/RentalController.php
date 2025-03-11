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
     *     summary="Get user rentals",
     *     description="Retrieve all rentals for the authenticated user",
     *     tags={"Rentals"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Rental")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function getUserRentals()
    {
        $user = Auth::user(); // Get the authenticated user
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $rentals = Rental::where('user_id', $user->id)->get();
        return response()->json($rentals, 200);
    }
    
    /**
     * @OA\Post(
     *     path="/api/rentals",
     *     summary="Create a new rental",
     *     description="Create a new rental record for the authenticated user",
     *     tags={"Rentals"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"car_id", "start_date", "end_date", "total_amount", "status"},
     *             @OA\Property(property="car_id", type="integer", example=1),
     *             @OA\Property(property="start_date", type="string", format="date", example="2023-03-15"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2023-03-20"),
     *             @OA\Property(property="total_amount", type="number", format="float", example=250.00),
     *             @OA\Property(property="status", type="string", enum={"pending", "completed", "cancelled"}, example="pending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rental created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Rental")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'total_amount' => 'required|numeric',
            'status' => 'required|in:pending,completed,cancelled',
        ]);
        
        $rental = Rental::create([
            'user_id' => Auth::id(),
            'car_id' => $validatedData['car_id'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'total_amount' => $validatedData['total_amount'],
            'status' => $validatedData['status'],
        ]);
        
        return response()->json($rental, 201);
    }
    
    /**
     * @OA\Put(
     *     path="/api/rentals/{id}",
     *     summary="Update a rental",
     *     description="Update an existing rental record for the authenticated user",
     *     tags={"Rentals"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the rental to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"car_id", "start_date", "end_date", "total_amount", "status"},
     *             @OA\Property(property="car_id", type="integer", example=1),
     *             @OA\Property(property="start_date", type="string", format="date", example="2023-03-15"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2023-03-20"),
     *             @OA\Property(property="total_amount", type="number", format="float", example=250.00),
     *             @OA\Property(property="status", type="string", enum={"pending", "completed", "cancelled"}, example="completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rental updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Rental")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rental not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rental not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $rental = Rental::where('user_id', Auth::id())->find($id);
        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }
        
        $validatedData = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'total_amount' => 'required|numeric',
            'status' => 'required|in:pending,completed,cancelled',
        ]);
        
        $rental->update($validatedData);
        return response()->json($rental, 200);
    }
    
    /**
     * @OA\Delete(
     *     path="/api/rentals/{id}",
     *     summary="Delete a rental",
     *     description="Delete a rental record by ID",
     *     tags={"Rentals"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the rental to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rental deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rental deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rental not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rental not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function delete($id)
    {
        $rental = Rental::where('user_id', Auth::id())->find($id);
        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }
        
        $rental->delete();
        return response()->json(['message' => 'Rental deleted successfully'], 200);
    }
}