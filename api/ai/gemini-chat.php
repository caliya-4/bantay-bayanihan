<?php
// api/ai/gemini-chat.php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

// Check if Gemini API key is configured
$geminiConfig = getGeminiConfig();
if (empty($geminiConfig['api_key'])) {
    echo json_encode([
        'success' => false,
        'message' => 'AI service is not configured. Please configure GEMINI_API_KEY.'
    ]);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
$userMessage = $data['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['success' => false, 'message' => 'No message provided']);
    exit;
}

// Enhanced system instruction
$systemInstruction = "You are Bantay Bot, a friendly and helpful disaster preparedness assistant for Baguio City, Philippines.

Your role is to:
- Provide clear, practical advice about emergencies, evacuation, and safety
- Give step-by-step guidance when needed
- Be empathetic and reassuring, especially for questions about children and family safety
- Use simple language that everyone can understand
- Include specific examples and tips when helpful

Provide thorough but conversational answers. Aim for 3-5 sentences for simple questions, and longer detailed responses for complex questions about safety and preparedness.";

// Full prompt
$fullPrompt = $systemInstruction . "\n\nUser: " . $userMessage . "\n\nAssistant:";

// Gemini API URL
$url = "https://generativelanguage.googleapis.com/v1/models/{$geminiConfig['model']}:generateContent?key=" . $geminiConfig['api_key'];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => $fullPrompt]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 1024,  // Increased for better responses
        'topP' => 0.95,
        'topK' => 40
    ],
    'safetySettings' => [
        [
            'category' => 'HARM_CATEGORY_HARASSMENT',
            'threshold' => 'BLOCK_ONLY_HIGH'
        ],
        [
            'category' => 'HARM_CATEGORY_HATE_SPEECH',
            'threshold' => 'BLOCK_ONLY_HIGH'
        ],
        [
            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
            'threshold' => 'BLOCK_ONLY_HIGH'
        ],
        [
            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
        ]
    ]
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode === 200) {
    $result = json_decode($response, true);
    
    // Check if response has the expected structure
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'];
        
        echo json_encode([
            'success' => true,
            'response' => $aiResponse
        ]);
    } else {
        // Check if blocked by safety filters
        if (isset($result['candidates'][0]['finishReason'])) {
            $reason = $result['candidates'][0]['finishReason'];
            
            echo json_encode([
                'success' => false,
                'message' => 'Response was filtered due to safety settings',
                'reason' => $reason
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Unexpected API response format',
                'debug' => $result
            ]);
        }
    }
} else {
    // Log the actual error for debugging
    error_log("Gemini API Error - HTTP $httpCode: " . $response);
    
    echo json_encode([
        'success' => false,
        'message' => 'AI request failed',
        'http_code' => $httpCode,
        'curl_error' => $error,
        'api_response' => json_decode($response, true)
    ]);
}