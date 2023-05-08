<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Chat;
use App\Models\ChatTopic;
use Auth;

class ChatController extends Controller
{
    // use ChatTrait;
    //
    public function index()
    {
        return view('chat.index');
    }

    
    public function createTopic(Request $request)
    {
        $title = $request->input('prompt');
        $topic = new ChatTopic();
        $topic->title = $title;
        $topic->user_id = Auth::user()->id;
        $topic->save();

        $topic_id = $topic->id;

        return response()->json([
            'topic_id' => $topic_id
        ]);
    }

    public function sendChatGPTMessage(Request $request)
    {
        $prompt = $request->input('prompt');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('settings.openai_key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            "model"=> config('settings.openai_completions_model'),
            "messages"=> [
                [
                    "role"=> "user", 
                    "content"=> trim(preg_replace('/(?:\s{2,}+|[^\S ])/', ' ', $prompt))
                ]
            ],
            "temperature"=> 0.5,
            "n"=> 1,
            "frequency_penalty"=> 0,
            "presence_penalty"=> 0,
            "user"=> 'user' . $request->user()->id
        ]);
        
        $response = $response->json();
        $answer = $response['choices'][0]['message']['content'];

        $chat = new Chat();
        $chat->prompt = $prompt;
        $chat->result = $answer;
        $chat->topic_id = 111;
        $chat->save();

        // Send the answer back to the user
        return response()->json([
            'answer' => $answer
        ]);
    }
}
