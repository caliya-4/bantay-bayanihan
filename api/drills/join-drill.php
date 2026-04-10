<?php
// api/drills/join-drill.php
session_start();
header('Content-Type: application/json');

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader (for PHPMailer)
require __DIR__ . '/../../vendor/autoload.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    require_once __DIR__ . '/../../db_connect.php';
    
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }
    
    // Validate required fields
    $drill_id = isset($data['drill_id']) ? intval($data['drill_id']) : 0;
    $name = isset($data['name']) ? trim($data['name']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $barangay = isset($data['barangay']) ? trim($data['barangay']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    
    // Validation
    if (!$drill_id) {
        echo json_encode(['success' => false, 'message' => 'Drill ID is required']);
        exit;
    }
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Name is required']);
        exit;
    }
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }

    if (empty($barangay)) {
        echo json_encode(['success' => false, 'message' => 'Barangay is required']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Check if drill exists
    $drill_stmt = $pdo->prepare("SELECT * FROM drills WHERE id = ? AND status = 'published'");
    $drill_stmt->execute([$drill_id]);
    $drill = $drill_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$drill) {
        echo json_encode(['success' => false, 'message' => 'Drill not found or not published']);
        exit;
    }
    
    // ============================================
    // IMPORTANT: Using drill_participants table
    // (NOT drill_participations)
    // ============================================
    
    // Check if already registered (by email)
    $check_stmt = $pdo->prepare("SELECT id FROM drill_participants WHERE drill_id = ? AND email = ?");
    $check_stmt->execute([$drill_id, $email]);
    
    if ($check_stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'You have already registered for this drill'
        ]);
        exit;
    }
    
    // Insert participation record into drill_participants
    $insert_sql = "INSERT INTO drill_participants 
                   (drill_id, name, email, barangay, phone, joined_at) 
                   VALUES (?, ?, ?, ?, ?, NOW())";
    
    $insert_stmt = $pdo->prepare($insert_sql);
    $result = $insert_stmt->execute([
        $drill_id,
        $name,
        $email,
        $barangay,
        $phone
    ]);
    
    if ($result) {
        $participation_id = $pdo->lastInsertId();
        
        // Send notification to drill creator
        $creator_stmt = $pdo->prepare("SELECT created_by FROM drills WHERE id = ?");
        $creator_stmt->execute([$drill_id]);
        $creator_id = $creator_stmt->fetchColumn();
        
        if ($creator_id) {
            $notification_message = "New participant joined your drill: {$name} ({$email})";
            $notification_url = "barangay_responder/drills-and-trainings.php?leaderboard={$drill_id}";
            
            $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, url) VALUES (?, ?, ?)");
            $notif_stmt->execute([$creator_id, $notification_message, $notification_url]);
        }
        
        // Try to send confirmation email
        $email_sent = false;
        try {
            $email_sent = sendDrillConfirmationEmail(
                $email,
                $name,
                $drill['title'],
                $drill['description'] ?? 'Emergency preparedness drill',
                $drill['duration_minutes'] ?? 30
            );
        } catch (Exception $e) {
            // Log error but don't fail the registration
            error_log("Email send failed: " . $e->getMessage());
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Successfully registered for the drill!',
            'email_sent' => $email_sent,
            'participation_id' => $participation_id,
            'data' => [
                'name' => $name,
                'drill_title' => $drill['title'],
                'drill_duration' => $drill['duration_minutes'] ?? 30
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to register for drill']);
    }
    
} catch (PDOException $e) {
    error_log("Join drill error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in join-drill.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

/**
 * Send drill confirmation email using PHPMailer
 */
function sendDrillConfirmationEmail($to_email, $participant_name, $drill_title, $drill_description, $duration_minutes) {
    try {
        $mail = new PHPMailer(true);
        
        // Get SMTP configuration from environment
        $smtpConfig = getSMTPConfig();
        
        if ($smtpConfig['enabled']) {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $smtpConfig['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpConfig['username'];
            $mail->Password   = $smtpConfig['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtpConfig['port'];
        }

        // Recipients
        $mail->setFrom($smtpConfig['from_email'], $smtpConfig['from_name']);
        $mail->addAddress($to_email, $participant_name);
        $mail->addReplyTo('emergency@baguio.gov.ph', 'Bantay Bayanihan');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Drill Registration Confirmed - Bantay Bayanihan';
        
        // HTML Email Template
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { 
                    font-family: 'Segoe UI', Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0;
                    padding: 0;
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: #ffffff;
                }
                .header { 
                    background: linear-gradient(135deg, #6161ff, #ff0065); 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center; 
                }
                .header h1 { 
                    margin: 0; 
                    font-size: 32px; 
                    font-weight: 900;
                }
                .header p {
                    margin: 10px 0 0;
                    opacity: 0.95;
                    font-size: 16px;
                }
                .content { 
                    padding: 40px 30px; 
                }
                .checkmark { 
                    width: 80px;
                    height: 80px;
                    background: #10b981;
                    border-radius: 50%;
                    margin: 0 auto 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 48px;
                    color: white;
                }
                .drill-info { 
                    background: linear-gradient(135deg, #f8f9ff, #fef3f8);
                    padding: 25px; 
                    border-radius: 12px; 
                    margin: 25px 0; 
                    border-left: 5px solid #6161ff; 
                }
                .drill-info h2 { 
                    color: #00167a; 
                    margin-top: 0; 
                    font-size: 22px;
                }
                .drill-info p {
                    margin: 10px 0;
                    color: #475569;
                }
                .section {
                    margin: 25px 0;
                }
                .section h3 { 
                    color: #00167a; 
                    font-size: 18px;
                    margin-bottom: 15px;
                }
                .section ul {
                    margin: 0;
                    padding-left: 25px;
                    color: #475569;
                }
                .section li {
                    margin: 8px 0;
                }
                .button { 
                    display: inline-block; 
                    background: linear-gradient(135deg, #6161ff, #ff0065); 
                    color: white; 
                    padding: 15px 35px; 
                    text-decoration: none; 
                    border-radius: 8px; 
                    font-weight: 800; 
                    margin-top: 25px;
                    font-size: 16px;
                }
                .footer { 
                    text-align: center; 
                    padding: 30px; 
                    color: #666; 
                    font-size: 14px; 
                    border-top: 2px solid #f0f4ff;
                    background: #f8f9ff;
                }
                .footer strong {
                    color: #00167a;
                    font-size: 16px;
                }
                .disclaimer {
                    font-size: 12px;
                    color: #999;
                    margin-top: 20px;
                    font-style: italic;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🛡️ Bantay Bayanihan</h1>
                    <p>Disaster Preparedness & Response</p>
                </div>
                
                <div class='content'>
                    <div class='checkmark'>✓</div>
                    <h2 style='text-align: center; color: #00167a; font-size: 28px; margin: 0 0 10px 0;'>Registration Confirmed!</h2>
                    <p style='text-align: center; color: #6161ff; font-size: 18px; font-weight: 700;'>Thank you, {$participant_name}! 🎉</p>
                    
                    <p style='margin-top: 30px;'>Thank you for registering for our safety drill! Your participation helps make Baguio City safer and more prepared for emergencies.</p>
                    
                    <div class='drill-info'>
                        <h2>{$drill_title}</h2>
                        <p><strong>Description:</strong><br>{$drill_description}</p>
                        <p><strong>Duration:</strong> {$duration_minutes} minutes</p>
                    </div>
                    
                    <div class='section'>
                        <h3>📌 What's Next?</h3>
                        <ul>
                            <li>Check your email for updates about the drill schedule</li>
                            <li>Review the drill instructions on our website</li>
                            <li>Prepare any required materials</li>
                            <li>Mark your calendar and be ready to participate</li>
                        </ul>
                    </div>
                    
                    <div class='section'>
                        <h3>⚠️ Important Reminders:</h3>
                        <ul>
                            <li><strong>⏰ Arrive on time</strong> - Punctuality is crucial</li>
                            <li><strong>📱 Bring your mobile phone</strong> - For communication</li>
                            <li><strong>👕 Wear comfortable clothing</strong> - Easy to move in</li>
                            <li><strong>💧 Stay hydrated</strong> - Bring water</li>
                        </ul>
                    </div>
                    
                    <p style='margin-top: 35px; padding-top: 25px; border-top: 2px solid #f0f4ff;'>If you have any questions or need to cancel your registration, please contact us immediately.</p>
                    
                    <div style='text-align: center;'>
                        <a href='http://bantaybayanihan.com' class='button'>Visit Our Portal</a>
                    </div>
                </div>
                
                <div class='footer'>
                    <p><strong>Bantay Bayanihan</strong><br>
                    Baguio City Emergency Management</p>
                    <p style='margin: 15px 0;'>
                        📞 Emergency Hotline: <strong>(074) 442-5377</strong><br>
                        📧 Email: <strong>emergency@baguio.gov.ph</strong>
                    </p>
                    
                    <p class='disclaimer'>
                        This is an automated message. Please do not reply to this email.<br>
                        For inquiries, contact our emergency hotline above.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Plain text version
        $mail->AltBody = "
DRILL REGISTRATION CONFIRMED

Dear {$participant_name},

Thank you for registering for: {$drill_title}

Description: {$drill_description}
Duration: {$duration_minutes} minutes

What's Next:
- Check email for updates
- Review drill instructions
- Prepare required materials

Important Reminders:
- Arrive on time
- Bring mobile phone
- Wear comfortable clothing
- Stay hydrated

Contact: (074) 442-5377
Email: emergency@baguio.gov.ph

Bantay Bayanihan
        ";
        
        // Send email
        if ($smtp_enabled) {
            $mail->send();
            error_log("Confirmation email sent to: $to_email");
            return true;
        } else {
            // SMTP not enabled, skip email
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        return false;
    }
}