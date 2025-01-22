<?php

namespace App\Http\Controllers\Api\Cars;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index()
    {
        if (isset($_GET['user_id']) && $_GET['user_id'] != '') {
            $user_id = $_GET['user_id'];
        }else {
            return response()->json(['error' => 'please provide the user_id'], 422);
        }
        $cars = Car::where('user_id', $user_id)->get();
        return $cars; 
    }

    public function store(Request $request)
    {
        $request->validate([
            'make' => 'required|string',
            'model' => 'required|string',
            'license_plate' => 'required|string|unique:cars',
            'seats' => 'required|integer|min:1',
            'color' => 'nullable|string',
            'year' => 'nullable|integer',
        ]);
        $car = new Car;
        $car->make = $request->make;
        $car->model = $request->model;
        $car->license_plate = $request->license_plate;
        $car->seats = $request->seats;
        $car->color = $request->color;
        $car->year = $request->year;

        return response()->json(['message' => 'Car added successfully']);
    }

    public function show(Request $request, Car $car)
    {
        return $car;
    }
    
    public function update(Request $request, Car $car)
    {
        $validated = $request->validate([
            'make' => 'nullable|string',
            'model' => 'nullable|string',
            'license_plate' => 'nullable|string|unique:cars,license_plate,' . $car->id,
            'seats' => 'nullable|integer|min:1',
            'color' => 'nullable|string',
            'year' => 'nullable|integer',
        ]);

        $car->update($validated);

        return $car;
    }

    public function destroy(Car $car)
    {
        $car->delete();
        return response()->json(['message' => 'Car deleted successfully']);
    }

}
