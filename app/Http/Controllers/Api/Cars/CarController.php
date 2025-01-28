<?php

namespace App\Http\Controllers\Api\Cars;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CarsResource;
use App\Models\Car;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    public function index()
    {
        if (isset($_GET['user_id']) && $_GET['user_id'] != '') {
            $user_id = $_GET['user_id'];
        } else {
            return response()->json(['error' => 'please provide the user_id'], 422);
        }
        $cars = Car::with(['documents'])->where('user_id', $user_id)->get();
        return response()->json(CarsResource::collection($cars));
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
            'images' => 'required|array',
            'images.*' => 'required|file|mimes:jpeg,png,jpg|max:2048', 
        ]);

        $car = new Car;
        $car->make = $request->make;
        $car->user_id = Auth::user()->id;
        $car->model = $request->model;
        $car->license_plate = $request->license_plate;
        $car->seats = $request->seats;
        $car->color = $request->color;
        $car->year = $request->year;
        $car->save();

        $imagePaths = [];
        foreach ($request->file('images') as $image) {
            // Save the file and generate the path
            $storedPath = storeFile($image, 'car_images');
            $imagePaths[] = $storedPath;
        
            // Save the document record
            $document = new Document;
            $document->user_id = Auth::user()->id;
            $document->documentable_type = 'Car';
            $document->documentable_id = $car->id;
            $document->type = 'car_photo';
            $document->file_path = $storedPath; // Use the generated path
            $document->save();
        }

        return response()->json([
            'message' => 'Car added successfully',
        ]);
    }

    public function show(Request $request, Car $car)
    {
        return response()->json(new CarsResource($car));
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
