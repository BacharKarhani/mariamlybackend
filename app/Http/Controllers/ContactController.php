<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'message' => 'required|string|max:1000',
        ]);

        // Save to database
        ContactMessage::create($validated);

        // Send email to fixed address
        Mail::raw("Message from {$validated['name']} ({$validated['email']}):\n\n{$validated['message']}", function ($mail) {
            $mail->to('bacharkarhani0@gmail.com')
                ->subject('New Contact Form Message');
        });

        return response()->json([
            'success' => true,
            'message' => 'Message sent and stored successfully!',
        ], 201);
    }
}
