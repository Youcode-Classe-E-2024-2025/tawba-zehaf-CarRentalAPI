<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Car::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mark' => 'required',
            'model' => 'required',
            'color' => 'required',
            'price' => 'required',
            'year' => 'required',          
        ]);

        $car = Car::create([
            'mark' => $request->mark,
            'model' => $request->model,
            'color' => $request->color,
            'price' => $request->price,
            'year' => $request->year,
        ]);

        return response()->json([
            'message' => 'Car created successfully',
            'car' => $car
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Car $car)
    {
        return $car;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Car $car)
    {
        $car->update($request->all());
        return $car;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Car $car)
    {
        $car->delete();
        return response()->json([
            'message' => 'Car deleted successfully',
            'car' => $car 
        ]);
    }
}