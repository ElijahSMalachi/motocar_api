<?php

namespace App\Http\Controllers\Api\Cars;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index()
    {
        return Car::all(); // List all cars
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'make' => 'required|string',
            'model' => 'required|string',
            'license_plate' => 'required|string|unique:cars',
            'seats' => 'required|integer|min:1',
            'color' => 'nullable|string',
            'year' => 'nullable|integer',
            'user_id' => 'required|exists:users,id',
        ]);

        return Car::create($validated);
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
