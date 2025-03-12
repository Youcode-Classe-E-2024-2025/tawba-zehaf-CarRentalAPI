<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
/**
 * @OA\Schema(
 *     schema="Car",
 *     title="Car",
 *     description="Car model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="company", type="string", example="Toyota"),
 *     @OA\Property(property="model", type="string", example="Corolla"),
 *     @OA\Property(property="license_plate", type="string", example="ABC-123"),
 *     @OA\Property(property="price_per_day", type="number", format="float", example=50.00),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-10T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-03-10T12:00:00Z")
 * )
 */

class CarController extends Controller
{
   /**
 * @OA\Get(
 *     path="/api/cars/pagin/{param}",
 *     summary="Get all cars with pagination",
 *     description="Retrieve a paginated list of cars",
 *     tags={"Cars"},
 *     @OA\Parameter(
 *         name="param",
 *         in="path",
 *         required=true,
 *         description="Number of items per page",
 *         @OA\Schema(type="integer", example=10)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Car")),
 *             @OA\Property(property="pagination", type="object",
 *                 @OA\Property(property="total", type="integer", example=100),
 *                 @OA\Property(property="per_page", type="integer", example=10),
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="last_page", type="integer", example=10),
 *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
 *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error"
 *     )
 * )
 */
public function getAll(int $param)
{
    try {
        // Ensure perPage is a positive integer to avoid issues
        $perPage = $param > 0 ? (int) $param : 10;
// Ensure perPage is a positive integer to avoid issues
        // Fetch paginated cars
        return response()->json(Car::paginate($perPage));
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to retrieve cars'], 500);
    }
}

    /**
     * @OA\Get(
     *     path="/api/cars/{id}",
     *     summary="Get car by ID",
     *     description="Retrieve a specific car by its ID",
     *     tags={"Cars"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the car to retrieve",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(ref="#/components/schemas/Car")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Car not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Car not found")
     *         )
     *     )
     * )
     */
    public function getById($id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        return response()->json($car);
    }

    /**
     * @OA\Post(
     *     path="/api/cars",
     *     summary="Create a new car",
     *     description="Create a new car record",
     *     tags={"Cars"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"company", "model", "license_plate", "price_per_day"},
     *             @OA\Property(property="company", type="string", example="Honda"),
     *             @OA\Property(property="model", type="string", example="Civic"),
     *             @OA\Property(property="license_plate", type="string", example="XYZ-789"),
     *             @OA\Property(property="price_per_day", type="number", format="float", example=45.50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Car created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Car")
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
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'license_plate'=> 'required|string|unique:cars',
            'price_per_day' => 'required|numeric',
        ]);

        $car = Car::create($validated);

        return response()->json($car, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/cars/{id}",
     *     summary="Update a car",
     *     description="Update an existing car record",
     *     tags={"Cars"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the car to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"company", "model", "price_per_day"},
     *             @OA\Property(property="company", type="string", example="Honda"),
     *             @OA\Property(property="model", type="string", example="Civic"),
     *             @OA\Property(property="price_per_day", type="number", format="float", example=45.50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Car updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Car")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Car not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Car not found")
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
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'model' => 'required|string|max:255',
             
            'price_per_day' => 'required|numeric',
        ]);

        $car->update($validated);

        return response()->json($car);
    }

    /**
     * @OA\Delete(
     *     path="/api/cars/{id}",
     *     summary="Delete a car",
     *     description="Delete a car record by ID",
     *     tags={"Cars"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the car to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Car deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Car deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Car not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Car not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function delete($id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        $car->delete();

        return response()->json(['message' => 'Car deleted successfully']);
    }
/**
 * @OA\Get(
 *     path="/api/cars/filter",
 *     summary="Filter cars",
 *     description="Filter cars by optional query parameters like mark, model, year, color, or price.",
 *     tags={"Cars"},
 *     @OA\Parameter(
 *         name="mark",
 *         in="query",
 *         description="Car mark",
 *         required=false,
 *         @OA\Schema(type="string", example="Toyota")
 *     ),
 *     @OA\Parameter(
 *         name="model",
 *         in="query",
 *         description="Car model",
 *         required=false,
 *         @OA\Schema(type="string", example="Corolla")
 *     ),
 *     @OA\Parameter(
 *         name="year",
 *         in="query",
 *         description="Car year",
 *         required=false,
 *         @OA\Schema(type="string", example="2022")
 *     ),
 *     @OA\Parameter(
 *         name="color",
 *         in="query",
 *         description="Car color",
 *         required=false,
 *         @OA\Schema(type="string", example="Blue")
 *     ),
 *     @OA\Parameter(
 *         name="price",
 *         in="query",
 *         description="Filter by price (less than or equal to)",
 *         required=false,
 *         @OA\Schema(type="number", format="float", example="20000")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Filtered list of cars"
 *     )
 * )
 */
public function filter(Request $request)
{
    $query = Car::query();

    if ($request->filled('mark')) {
        $query->where('mark', $request->input('mark'));
    }

    if ($request->filled('model')) {
        $query->where('model', $request->input('model'));
    }

    if ($request->filled('year')) {
        $query->where('year', $request->input('year'));
    }

    if ($request->filled('color')) {
        $query->where('color', $request->input('color'));
    }

    if ($request->filled('price')) {
        $query->where('price', '<=', $request->input('price'));
    }

    return response()->json($query->get());
}
}