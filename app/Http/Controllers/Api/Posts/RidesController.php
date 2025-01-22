<?php

namespace App\Http\Controllers\Api\Posts;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RideResource;
use App\Models\Location;
use App\Models\Ride;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RidesController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            // Pickup Location Validation
            'pickup_location' => 'required|array',
            'pickup_location.name' => 'required|string',
            'pickup_location.country' => 'required|string',
            'pickup_location.state' => 'required|string',
            'pickup_location.street' => 'required|string',
            'pickup_location.county' => 'required|string',
            'pickup_location.latitude' => 'required|numeric',
            'pickup_location.longitude' => 'required|numeric',

            // Destination Location Validation
            'destination_location' => 'required|array',
            'destination_location.name' => 'required|string',
            'destination_location.country' => 'required|string',
            'destination_location.state' => 'required|string',
            'destination_location.street' => 'required|string',
            'destination_location.county' => 'required|string',
            'destination_location.latitude' => 'required|numeric',
            'destination_location.longitude' => 'required|numeric',

            // Ride Validation
            'seats' => 'required|integer|min:1',
            'car_id' => 'required|integer',
            'price_per_seat' => 'required|numeric|min:0',
            'start_off_date' => 'required|date_format:Y-m-d H:i|after_or_equal:now',
            'return_date' => 'nullable|sometimes|date_format:Y-m-d H:i|after_or_equal:start_off_date',
        ]);

        if (!empty($request->pickup_location)) {
            $pickupLocation = new Location;
            $pickupLocation->name = $request->pickup_location['name'];
            $pickupLocation->country = $request->pickup_location['country'];
            $pickupLocation->state = $request->pickup_location['state'];
            $pickupLocation->street = $request->pickup_location['street'];
            $pickupLocation->county = $request->pickup_location['county'];
            $pickupLocation->latitude = $request->pickup_location['latitude'];
            $pickupLocation->longitude = $request->pickup_location['longitude'];
            $pickupLocation->save();
        }

        if (!empty($request->destination_location)) {
            $destinationLocation = new Location;
            $destinationLocation->name = $request->destination_location['name'];
            $destinationLocation->country = $request->destination_location['country'];
            $destinationLocation->state = $request->destination_location['state'];
            $destinationLocation->street = $request->destination_location['street'];
            $destinationLocation->county = $request->destination_location['county'];
            $destinationLocation->latitude = $request->destination_location['latitude'];
            $destinationLocation->longitude = $request->destination_location['longitude'];
            $destinationLocation->save();
        }

        if ($pickupLocation->id && $destinationLocation->id) {
            $user = Auth::user();
            $ride = new Ride;
            $ride->driver_id = $user->id;
            $ride->car_id = $request->car_id;
            $ride->pickup_location_id = $pickupLocation->id;
            $ride->destination_location_id = $destinationLocation->id;
            $ride->seats = $request->seats;
            $ride->price_per_seat = $request->price_per_seat;
            $ride->start_off_date = $request->start_off_date;
            $ride->return_date = $request->return_date;
            $ride->save();
            return response()->json(['message' => 'Ride created successfully']);
        } else {
            if ($pickupLocation) {
                $pickupLocation->delete();
            }

            if ($destinationLocation) {
                $destinationLocation->delete();
            }
            return response()->json(['error' => 'Failed to create ride. Locations were not saved.'], 400);
        }
    }

    public function searchRides(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'pickup_location.latitude' => 'required|numeric',
            'pickup_location.longitude' => 'required|numeric',
            'destination_location.latitude' => 'required|numeric',
            'destination_location.longitude' => 'required|numeric',
            'start_off_date' => 'required|date',
            'radius' => 'nullable|numeric|min:1', // Radius in kilometers
        ]);

        $pickupLatitude = $validated['pickup_location']['latitude'];
        $pickupLongitude = $validated['pickup_location']['longitude'];
        $destinationLatitude = $validated['destination_location']['latitude'];
        $destinationLongitude = $validated['destination_location']['longitude'];
        $startOffDate = $validated['start_off_date'];
        $radius = $validated['radius'] ?? 10; // Default to 10 km radius if not provided

        // Define the allowed date range (+/- 3 days from the requested start date)
        $dateRangeStart = Carbon::parse($startOffDate)->subDays(3);
        $dateRangeEnd = Carbon::parse($startOffDate)->addDays(3);

        // Fetch rides with the location proximity and date range check
        $rides = Ride::with(['pickupLocation', 'destinationLocation', 'driver']) // Eager load relationships
            ->get();

        $matchingRides = $rides->filter(function ($ride) use ($pickupLatitude, $pickupLongitude, $destinationLatitude, $destinationLongitude, $radius, $dateRangeStart, $dateRangeEnd) {
            // Calculate the distance between the pickup locations
            $pickupDistance = $this->haversineDistance($pickupLatitude, $pickupLongitude, $ride->pickupLocation->latitude, $ride->pickupLocation->longitude);

            // Calculate the distance between the destination locations
            $destinationDistance = $this->haversineDistance($destinationLatitude, $destinationLongitude, $ride->destinationLocation->latitude, $ride->destinationLocation->longitude);

            // Check if the pickup or destination location is within the radius
            $isPickupClose = $pickupDistance <= $radius;
            $isDestinationClose = $destinationDistance <= $radius;

            // Check if the ride's start date is within the allowed range (+/- 3 days)
            $isDateClose = Carbon::parse($ride->start_off_date)->between($dateRangeStart, $dateRangeEnd);

            // Return true if either pickup or destination is close, and the date is close
            return ($isPickupClose || $isDestinationClose) && $isDateClose;
        });

        if ($matchingRides->isEmpty()) {
            return response()->json(['message' => 'No rides found matching your criteria'], 404);
        }

        // Use RideResource to format the response
        return RideResource::collection($matchingRides);
    }





    function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;  // Earth radius in kilometers

        // Convert degrees to radians
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        // Haversine formula
        $lonDiff = $lonTo - $lonFrom;
        $latDiff = $latTo - $latFrom;
        $a = sin($latDiff / 2) * sin($latDiff / 2) + cos($latFrom) * cos($latTo) * sin($lonDiff / 2) * sin($lonDiff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance; // Distance in kilometers
    }
}
