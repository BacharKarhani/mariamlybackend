<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactInfoController extends Controller
{
    /**
     * Get contact information (public endpoint)
     */
    public function index()
    {
        $contact = Contact::first();
        
        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact information not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'contact' => $contact
        ]);
    }

    /**
     * Update contact information (admin only)
     */
    public function update(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'location' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
        ]);

        $contact = Contact::first();
        
        if (!$contact) {
            $contact = Contact::create([
                'phone' => $request->phone,
                'email' => $request->email,
                'location' => $request->location,
                'instagram' => $request->instagram,
            ]);
        } else {
            $contact->update([
                'phone' => $request->phone,
                'email' => $request->email,
                'location' => $request->location,
                'instagram' => $request->instagram,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Contact information updated successfully',
            'contact' => $contact
        ]);
    }

    /**
     * Get contact information for admin (admin only)
     */
    public function adminIndex()
    {
        $contact = Contact::first();
        
        if (!$contact) {
            return response()->json([
                'success' => true,
                'contact' => null,
                'message' => 'No contact information found'
            ]);
        }

        return response()->json([
            'success' => true,
            'contact' => $contact
        ]);
    }
}
