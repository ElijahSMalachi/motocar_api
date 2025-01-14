<?php

namespace App\Http\Controllers\Api\Documnets;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        // Retrieve documents for the user
        return Document::where('user_id', $request->user()->id)->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'documentable_type' => 'required|string', // e.g., Car, User
            'documentable_id' => 'required|integer', // ID of the related model
            'type' => 'required|string', // Document type (e.g., car_photo, driver_license)
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048', // Max 2MB
        ]);

        $filePath = $request->file('file')->store('documents', 'public');

        $document = Document::create([
            'user_id' => $request->user()->id,
            'documentable_type' => $validated['documentable_type'],
            'documentable_id' => $validated['documentable_id'],
            'type' => $validated['type'],
            'file_path' => $filePath,
        ]);

        return response()->json($document, 201);
    }

    public function destroy(Document $document)
    {
        // Ensure the user owns the document
        $this->authorize('delete', $document);

        // Delete the file from storage
        Storage::disk('public')->delete($document->file_path);

        // Delete the record
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }

}
