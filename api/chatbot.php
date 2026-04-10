<?php
// api/chatbot.php - Main chatbot backend endpoint
session_start();
header('Content-Type: application/json');

// Allow from same origin
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed'
    ]);
    exit;
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['message']) || empty(trim($data['message']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Message is required'
    ]);
    exit;
}

$userMessage = trim($data['message']);

// Log for debugging (optional)
error_log("Chatbot received: " . $userMessage);

// ============================================
// SIMPLE FALLBACK RESPONSES
// ============================================
// This is a fallback handler for queries that the frontend 
// pattern matching didn't catch. You can enhance this with
// actual AI integration later.

$response = generateFallbackResponse($userMessage);

echo json_encode([
    'success' => true,
    'response' => $response
]);
exit;

// ============================================
// FALLBACK RESPONSE GENERATOR
// ============================================
function generateFallbackResponse($message) {
    $lowerMsg = strtolower($message);
    
    // Detect language
    $isTagalog = preg_match('/\b(ano|paano|saan|kumusta|sino|kailan|bakit|ako|ikaw|tayo|sa|ng|ang|mga|po)\b/i', $message);
    
    // Check for common keywords and provide helpful responses
    if (preg_match('/\b(baguio|city)\b/i', $lowerMsg)) {
        return $isTagalog 
            ? "Nandito ako para tumulong sa disaster preparedness sa Baguio City. Magtanong tungkol sa evacuation centers, emergency contacts, o disaster safety tips!"
            : "I'm here to help with disaster preparedness in Baguio City. Ask me about evacuation centers, emergency contacts, or disaster safety tips!";
    }
    
    if (preg_match('/\b(safe|safety|secure|protect)\b/i', $lowerMsg)) {
        return $isTagalog
            ? "Ang iyong kaligtasan ay mahalaga! Magtanong tungkol sa:<br>• Emergency contacts (\"numero ng emergency\")<br>• Evacuation centers (\"saan lumikas\")<br>• Disaster tips (\"pag may lindol\")<br>• Emergency kit (\"ano dalhin\")"
            : "Your safety is important! Ask me about:<br>• Emergency contacts<br>• Evacuation centers<br>• Disaster tips (earthquake, typhoon, etc.)<br>• Emergency kit preparation";
    }
    
    if (preg_match('/\b(thank|thanks|salamat)\b/i', $lowerMsg)) {
        return $isTagalog
            ? "Walang anuman! Mag-ingat palagi at maging handa. 🛡️"
            : "You're welcome! Stay safe and be prepared. 🛡️";
    }
    
    if (preg_match('/\b(bye|goodbye|paalam)\b/i', $lowerMsg)) {
        return $isTagalog
            ? "Paalam! Bumalik anytime para sa disaster preparedness tips. Mag-ingat! 👋"
            : "Goodbye! Come back anytime for disaster preparedness help. Stay safe! 👋";
    }
    
    // Default helpful response
    return $isTagalog
        ? "Hindi ko masyadong maintindihan ang tanong mo, pero nandito ako para tumulong! 😊<br><br>Subukan ang mga ito:<br>• \"emergency contacts\" - Para sa mga numero<br>• \"nearest evacuation center\" - Hanapin ang malapit na evacuation<br>• \"earthquake tips\" - Safety tips sa lindol<br>• \"help\" - Listahan ng lahat ng commands"
        : "I'm not quite sure about that, but I'm here to help! 😊<br><br>Try asking:<br>• \"emergency contacts\" - For hotline numbers<br>• \"nearest evacuation center\" - Find nearby centers<br>• \"earthquake tips\" - Safety during earthquakes<br>• \"help\" - See all available commands";
}