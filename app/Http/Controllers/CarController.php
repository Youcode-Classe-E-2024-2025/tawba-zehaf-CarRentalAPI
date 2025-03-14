<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


/**
 * @OA\Schema(
 *     schema="Car",
 *     type="object",
 *     required={"company", "model", "license_plate", "price_per_day"},
 *     @OA\Property(property="id", type="integer", description="The car's unique ID"),
 *     @OA\Property(property="company", type="string", description="The company that makes the car"),
 *     @OA\Property(property="model", type="string", description="The model of the car"),
 *     @OA\Property(property="license_plate", type="string", description="The unique license plate of the car"),
 *     @OA\Property(property="price_per_day", type="number", format="float", description="Price per day for renting the car"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The time when the car was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The time when the car was last updated")
 * )
 */

class CarController extends Controller
{
   
      /**
 * @OA\Get(
 *     path="/api/cars",
 *     summary="Get a list of all rentals",
 *     tags={"Cars"},
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

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Default to 10 items per page if not provided
        $cars = Car::paginate($perPage);

        return response()->json($cars);
    }

    /**
     * @OA\Post(
     *     path="/api/cars",
     *     summary="Store a new car",
     *     tags={"Cars"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *        @OA\JsonContent(
 *             required={"company", "model", "license_plate", "price_per_day"},
 *             @OA\Property(property="company", type="string", example="Toyota"),
 *             @OA\Property(property="model", type="string", example="Corolla"),
 *             @OA\Property(property="license_plate", type="string", example="ABC1234"),
 *             @OA\Property(property="price_per_day", type="number", format="float", example=50.00)
 *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Car created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Car")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required|string|max:255', // Company name must be a string and required
            'model' => 'required|string|max:255', // Model name must be a string and required
            'license_plate' => 'required|string|max:255|unique:cars,license_plate', // License plate must be unique and required
            'price_per_day' => 'required|numeric|min:0', // Price per day must be a positive number
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $car = Car::create($request->all());
        return response()->json($car, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/cars/{car}",
     *     summary="Show a specific car",
     *     tags={"Cars"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="car",
     *         in="path",
     *         description="Car ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A specific car",
     *         @OA\JsonContent(ref="#/components/schemas/Car")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Car not found"
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
    public function show(Car $car)
    {
        return response()->json($car);
    }

    /**
     * @OA\Put(
     *     path="/api/cars/{car}",
     *     summary="Update a specific car",
     *     tags={"Cars"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="car",
     *         in="path",
     *         description="Car ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"make", "model", "year", "price_per_day"},
     *             @OA\Property(property="make", type="string"),
     *             @OA\Property(property="model", type="string"),
     *             @OA\Property(property="year", type="integer"),
     *             @OA\Property(property="price_per_day", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Car updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Car")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Car not found"
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
    public function update(Request $request, Car $car)
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required|string|max:255', // Company name must be a string and required
            'model' => 'required|string|max:255', // Model name must be a string and required
            'license_plate' => 'required|string|max:255|unique:cars,license_plate', // License plate must be unique and required
            'price_per_day' => 'required|numeric|min:0', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $car->update($request->all());
        return response()->json($car);
    }

    /**
     * @OA\Delete(
     *     path="/api/cars/{car}",
     *     summary="Delete a specific car",
     *     tags={"Cars"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="car",
     *         in="path",
     *         description="Car ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Car deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Car not found"
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
    public function destroy(Car $car)
    {
        $car->delete();
        return response()->json(null, 204);
    }


  
   


    /**
     * @OA\Get(
     *     path="/api/selectCars",
     *     summary="Filter cars by company and model",
     *     tags={"Cars"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="company",
     *         in="query",
     *         required=false,
     *         description="Company name to filter by",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="model",
     *         in="query",
     *         required=false,
     *         description="Model name to filter by",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered cars list",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Car")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No cars found matching the criteria"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function filterByModelAndCompany(Request $request)
    {
       
        $query = Car::query();

        if ($request->has('company')) {
            $query->where('company', 'like', '%' . $request->company . '%');
        }

        if ($request->has('model')) {
            $query->where('model', 'like', '%' . $request->model . '%');
        }

        $cars = $query->get();

        if ($cars->isEmpty()) {
            return response()->json(['message' => 'No cars found matching the criteria'], 404);
        }

        return $cars;
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