<?php
// api/gamification/submit-answer.php
header('Content-Type: application/json');
session_start();

$input = json_decode(file_get_contents('php://input'), true);
$question_id = isset($input['question_id']) ? (int)$input['question_id'] : 0;
$selected = isset($input['selected_answer']) ? strtoupper(trim($input['selected_answer'])) : '';

if (!$question_id || !$selected) {
    echo json_encode(['success' => false, 'message' => 'Missing question_id or selected_answer']);
    exit;
}

// All questions with correct answers
$answers = [
    // Certification questions
    1001=>['correct'=>'B','explanation'=>'Drop, Cover, and Hold On protects you from falling debris. Running outside risks injury from debris.','points'=>10],
    1002=>['correct'=>'D','explanation'=>'All are serious secondary hazards after an earthquake: aftershocks, tsunamis, and gas-line fires.','points'=>10],
    1003=>['correct'=>'B','explanation'=>'Under a sturdy table or desk protects you from falling objects.','points'=>10],
    1004=>['correct'=>'B','explanation'=>'Signal No. 4 means very destructive typhoon-force winds of 185 kph or more are expected within 12 hours.','points'=>10],
    1005=>['correct'=>'B','explanation'=>'A complete emergency kit includes food, water, flashlight, first aid, and important documents.','points'=>10],
    1006=>['correct'=>'C','explanation'=>'Find a sturdy building immediately. Trees can fall and driving during a typhoon is dangerous.','points'=>10],
    1007=>['correct'=>'A','explanation'=>'PASS: Pull, Aim at the base, Squeeze the handle, Sweep side to side.','points'=>10],
    1008=>['correct'=>'C','explanation'=>'Seal door gaps to slow smoke, stay low, and signal rescuers from a window. Never use elevators.','points'=>10],
    1009=>['correct'=>'C','explanation'=>'Always treat fire alarms as real and evacuate immediately using stairs.','points'=>10],
    1010=>['correct'=>'B','explanation'=>'Only 6 inches of fast-moving water can knock an adult off their feet.','points'=>10],
    1011=>['correct'=>'B','explanation'=>'Abandon the vehicle immediately and move to higher ground. Vehicles can be swept away rapidly.','points'=>10],
    1012=>['correct'=>'C','explanation'=>'Red rainfall warning means intense to torrential rainfall and flooding is imminent.','points'=>10],
    1013=>['correct'=>'B','explanation'=>'Ground cracks, tilting trees, and unusual sounds are warning signs of an impending landslide.','points'=>10],
    1014=>['correct'=>'B','explanation'=>'Move away from steep slopes, riverbanks, and unstable areas during heavy rain.','points'=>10],
    1015=>['correct'=>'B','explanation'=>'Heavy and prolonged rainfall is the primary trigger for landslides in Baguio and the Cordillera.','points'=>10],
    1016=>['correct'=>'C','explanation'=>'At least 2 liters of water per person per day is recommended for drinking and sanitation.','points'=>10],
    1017=>['correct'=>'A','explanation'=>'CERT stands for Community Emergency Response Team — trained community volunteers.','points'=>10],
    1018=>['correct'=>'B','explanation'=>'A Go Bag is a pre-packed bag with essentials ready for immediate evacuation.','points'=>10],
    1019=>['correct'=>'C','explanation'=>'The NDRRMC is the primary government body for disaster risk reduction in the Philippines.','points'=>10],
    1020=>['correct'=>'B','explanation'=>'911 is the emergency hotline for police, fire, and medical emergencies in the Philippines.','points'=>10],
    // Category quiz questions
    101=>['correct'=>'B','explanation'=>'Drop, Cover, and Hold On protects you from falling debris.','points'=>10],
    102=>['correct'=>'B','explanation'=>'Check for injuries and gas leaks first after shaking stops.','points'=>10],
    103=>['correct'=>'B','explanation'=>'Under a sturdy table protects you from falling objects.','points'=>10],
    104=>['correct'=>'B','explanation'=>'Stay in bed and cover your head with a pillow.','points'=>10],
    105=>['correct'=>'B','explanation'=>'Always wait for official clearance before re-entering buildings.','points'=>10],
    201=>['correct'=>'B','explanation'=>'Signal No. 1 means winds of 30-60 kph are expected within 36 hours.','points'=>10],
    202=>['correct'=>'B','explanation'=>'Prepare emergency kit, secure loose items, and know your evacuation route.','points'=>10],
    203=>['correct'=>'B','explanation'=>'Never go outside during the eye — the back half of the typhoon is still coming.','points'=>10],
    204=>['correct'=>'B','explanation'=>'Storm surge is an abnormal rise in sea level caused by typhoon winds.','points'=>10],
    205=>['correct'=>'B','explanation'=>'Floodwaters are contaminated. Avoid contact after a typhoon.','points'=>10],
    301=>['correct'=>'A','explanation'=>'PASS: Pull, Aim at the base, Squeeze, Sweep side to side.','points'=>10],
    302=>['correct'=>'B','explanation'=>'Stop, Drop, and Roll smothers flames by cutting off oxygen.','points'=>10],
    303=>['correct'=>'B','explanation'=>'Stay low where air is cleaner. Smoke rises and is the leading cause of fire deaths.','points'=>10],
    304=>['correct'=>'B','explanation'=>'Feel the door for heat first. If hot, find another exit.','points'=>10],
    305=>['correct'=>'B','explanation'=>'Cooking is the leading cause of home fires.','points'=>10],
    401=>['correct'=>'B','explanation'=>'Just 2 feet of rushing water can carry away most vehicles.','points'=>10],
    402=>['correct'=>'B','explanation'=>'Follow evacuation orders immediately — floods rise very quickly.','points'=>10],
    403=>['correct'=>'B','explanation'=>'Boil or treat water after floods until authorities declare it safe.','points'=>10],
    404=>['correct'=>'B','explanation'=>'Flash floods are caused by sudden intense rainfall with little warning.','points'=>10],
    405=>['correct'=>'B','explanation'=>'Move to higher ground or upper floors. Never go to basements during flooding.','points'=>10],
    501=>['correct'=>'B','explanation'=>'Ground cracks, tilting trees, and unusual sounds warn of landslides.','points'=>10],
    502=>['correct'=>'B','explanation'=>'Evacuate proactively during prolonged heavy rain in mountain areas.','points'=>10],
    503=>['correct'=>'B','explanation'=>'Steep slopes, deforested hills, and riverbanks are highest risk for landslides.','points'=>10],
    504=>['correct'=>'B','explanation'=>'Move quickly sideways out of the landslide path.','points'=>10],
    505=>['correct'=>'B','explanation'=>'Deforestation removes tree roots that bind soil, dramatically increasing landslide risk.','points'=>10],
    601=>['correct'=>'B','explanation'=>'At least 72 hours (3 days) of supplies are recommended in emergency kits.','points'=>10],
    602=>['correct'=>'B','explanation'=>'911 is the national emergency hotline in the Philippines.','points'=>10],
    603=>['correct'=>'A','explanation'=>'NDRRMC is the National Disaster Risk Reduction and Management Council.','points'=>10],
    604=>['correct'=>'B','explanation'=>'A complete first aid kit includes bandages, antiseptic, pain reliever, scissors, gloves, and manual.','points'=>10],
    605=>['correct'=>'B','explanation'=>'A family evacuation plan ensures everyone knows what to do and where to go in an emergency.','points'=>10],
];

if (!isset($answers[$question_id])) {
    echo json_encode(['success' => false, 'message' => 'Question not found']);
    exit;
}

$answer_data  = $answers[$question_id];
$is_correct   = ($selected === $answer_data['correct']);
$points_earned = $is_correct ? $answer_data['points'] : 0;

// Save to session for tracking
if (!isset($_SESSION['quiz_score'])) $_SESSION['quiz_score'] = 0;
if ($is_correct) $_SESSION['quiz_score'] += $points_earned;

echo json_encode([
    'success'        => true,
    'is_correct'     => $is_correct,
    'correct_answer' => $answer_data['correct'],
    'explanation'    => $answer_data['explanation'],
    'points_earned'  => $points_earned,
]);
