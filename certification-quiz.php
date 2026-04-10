<?php
session_start();
require 'db_connect.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_name = $_SESSION['name'] ?? 'User';

$guest_email = null;
$guest_barangay = null;
if (!$user_id) {
    $guest_email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);
    $guest_barangay = filter_input(INPUT_GET, 'barangay', FILTER_SANITIZE_STRING);
}

$existing_cert = null;
if ($user_id) {
    $stmt = $pdo->prepare("SELECT id, score, created_at FROM certifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $existing_cert = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certification Quiz | Bantay Bayanihan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/design-system.css">
    <style>
        body {
            background: linear-gradient(135deg, #00167a 0%, #6161ff 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 80px 20px 40px;
            min-height: 100vh;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .page-hero {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            margin-bottom: 40px;
            box-shadow: 0 20px 60px rgba(0, 22, 122, 0.15);
            text-align: center;
        }

        .page-hero h1 {
            color: #00167a;
            font-size: 42px;
            margin: 0 0 15px 0;
            font-weight: 900;
        }

        .page-hero p {
            color: #64748b;
            font-size: 18px;
            margin: 0;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cert-badge {
            display: inline-block;
            background: linear-gradient(135deg, #6161ff, #ff0065);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            margin-top: 15px;
        }

        .quiz-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 22, 122, 0.15);
            margin-bottom: 30px;
        }

        .quiz-intro {
            text-align: center;
            margin-bottom: 30px;
        }

        .quiz-intro h2 {
            color: #00167a;
            font-size: 28px;
            margin: 0 0 15px 0;
        }

        .quiz-intro p {
            color: #64748b;
            font-size: 16px;
            margin: 8px 0;
        }

        .quiz-requirements {
            background: rgba(97, 97, 255, 0.1);
            border-left: 4px solid #6161ff;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .quiz-requirements h3 {
            margin: 0 0 15px 0;
            color: #6161ff;
            font-size: 16px;
        }

        .req-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .req-list li {
            padding: 8px 0;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .req-list li:before {
            content: '✓';
            color: #10b981;
            font-weight: bold;
            font-size: 18px;
        }

        .existing-cert {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            border: 2px solid #10b981;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
        }

        .existing-cert h3 {
            color: #10b981;
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        .existing-cert p {
            color: #064e3b;
            margin: 5px 0;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn {
            padding: 14px 32px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6161ff, #ff0065);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(97, 97, 255, 0.4);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #00167a;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
            transform: translateY(-3px);
        }

        .quiz-timeline {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .timeline-item {
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .timeline-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .timeline-item h4 {
            color: #00167a;
            margin: 10px 0 5px 0;
            font-size: 14px;
        }

        .timeline-item p {
            color: #64748b;
            margin: 0;
            font-size: 13px;
        }

        .cert-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .cert-icon {
            font-size: 48px;
        }

        .cert-header-text h2 {
            margin: 0;
            color: #00167a;
            font-size: 28px;
        }

        .cert-header-text p {
            margin: 5px 0 0 0;
            color: #64748b;
            font-size: 14px;
        }

        /* ── QUIZ MODAL ── */
        #quiz-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 22, 122, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        #quiz-modal.show {
            display: flex;
        }

        .quiz-content {
            background: white;
            border-radius: 24px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            border: 4px solid #6161ff;
            box-shadow: 0 30px 80px rgba(0, 22, 122, 0.4);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-10px); }
        }

        .quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .quiz-title {
            font-size: 20px;
            font-weight: 900;
            color: #00167a;
        }

        .quiz-close {
            background: none;
            border: none;
            font-size: 32px;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.3s;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .quiz-close:hover {
            background: #fee;
            color: #ff0065;
            transform: rotate(90deg);
        }

        .quiz-progress { margin-bottom: 30px; }

        .progress-text {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: 700;
            color: #64748b;
        }

        .progress-bar {
            height: 12px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #6161ff, #ff0065);
            transition: width 0.3s ease;
            border-radius: 10px;
        }

        .question-container { margin: 30px 0; }

        .question-text {
            font-size: 18px;
            font-weight: 700;
            color: #00167a;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            line-height: 1.5;
        }

        .options-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option-btn {
            background: white;
            border: 2px solid #e2e8f0;
            padding: 15px 20px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.2s;
            text-align: left;
            color: #475569;
            width: 100%;
        }

        .option-btn:hover:not(.disabled) {
            border-color: #6161ff;
            background: #f0f4ff;
            transform: translateX(5px);
        }

        .option-btn.correct  { background: #d1fae5; border-color: #10b981; color: #064e3b; }
        .option-btn.incorrect { background: #fee2e2; border-color: #ef4444; color: #7f1d1d; }
        .option-btn.disabled  { opacity: 0.7; cursor: not-allowed; pointer-events: none; }

        .feedback-box {
            margin-top: 20px;
            padding: 15px;
            border-radius: 12px;
            border-left: 4px solid;
        }

        .feedback-box.correct   { background: #d1fae5; border-left-color: #10b981; }
        .feedback-box.incorrect { background: #fee2e2; border-left-color: #ef4444; }

        .feedback-title {
            color: #1f2937;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .points-earned {
            background: #10b981;
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 12px;
        }

        .feedback-text {
            color: #374151;
            font-size: 14px;
            margin: 0;
        }

        .quiz-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .btn-next {
            background: linear-gradient(135deg, #6161ff, #ff0065);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            font-size: 15px;
            flex: 1;
            transition: all 0.3s;
        }

        .btn-next:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(97, 97, 255, 0.3);
        }

        .quiz-complete {
            text-align: center;
            padding: 40px 20px;
        }

        .complete-icon {
            font-size: 64px;
            margin-bottom: 20px;
            display: block;
            animation: bounce 0.6s ease;
        }

        .complete-title {
            font-size: 28px;
            font-weight: 900;
            color: #00167a;
            margin-bottom: 15px;
        }

        .complete-score {
            font-size: 48px;
            font-weight: 900;
            color: #6161ff;
            margin: 20px 0;
        }

        .complete-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 30px 0;
        }

        .stat-item {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 900;
            color: #6161ff;
        }

        .stat-label {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 5px;
        }

        .spinner {
            border: 4px solid #e2e8f0;
            border-top: 4px solid #6161ff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .page-hero { padding: 40px 25px; }
            .page-hero h1 { font-size: 28px; }
            .quiz-section { padding: 25px; }
            .quiz-timeline { grid-template-columns: 1fr; }
            .quiz-content { padding: 25px; }
            .complete-stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php if ($user_id): ?>
        <?php include 'includes/header.php'; ?>
    <?php endif; ?>

    <div class="container">
        <div class="page-hero">
            <div style="font-size: 64px; margin-bottom: 20px;">🏆</div>
            <h1>Bantay Bayanihan Certification</h1>
            <p>Demonstrate your disaster preparedness knowledge and earn your certification</p>
            <span class="cert-badge">📜 Officially Recognized Certification</span>
        </div>

        <?php if (!$user_id && !$guest_email): ?>
            <div class="quiz-section">
                <div class="quiz-intro">
                    <h2>Enter Your Email and Barangay to Continue</h2>
                    <p>We'll email your certificate after you pass and use your barangay data to measure preparedness.</p>
                </div>
                <form method="get" action="certification-quiz.php" style="text-align:center; margin-top:30px;">
                    <input type="email" name="email" required placeholder="you@example.com"
                        style="padding:14px 20px; border-radius:12px; border:2px solid #e2e8f0; width:280px; max-width:90%; margin:5px;" />
                    <input type="text" name="barangay" required placeholder="Your Barangay"
                        style="padding:14px 20px; border-radius:12px; border:2px solid #e2e8f0; width:280px; max-width:90%; margin:5px;" />
                    <button type="submit" class="btn btn-primary" style="margin:5px;">Continue →</button>
                </form>
            </div>

        <?php else: ?>
            <div class="quiz-section">
                <div class="cert-header">
                    <div class="cert-icon">📋</div>
                    <div class="cert-header-text">
                        <h2>Comprehensive Preparedness Quiz</h2>
                        <p>Test your knowledge across all disaster safety categories</p>
                    </div>
                </div>

                <?php if ($existing_cert): ?>
                    <div class="existing-cert">
                        <h3>🎉 You Already Have a Certification!</h3>
                        <p><strong>Certification Date:</strong> <?php echo date('F d, Y', strtotime($existing_cert['created_at'])); ?></p>
                        <p><strong>Score:</strong> <?php echo $existing_cert['score']; ?>%</p>
                        <p style="margin-top: 15px; font-size: 14px;">You can retake the quiz to improve your score or earn a new certification.</p>
                        <div class="action-buttons">
                            <button class="btn btn-primary" onclick="startCertQuiz()">
                                <i class="fas fa-redo"></i> Retake Quiz
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-home"></i> Back Home
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="quiz-intro">
                        <h2>Ready to Get Certified?</h2>
                        <p>Complete this comprehensive quiz to demonstrate your preparedness knowledge</p>
                        <?php if (!$user_id && $guest_email): ?>
                            <p style="margin-top:10px; font-size:14px; color:#475569;">
                                Certificate will be sent to <strong><?php echo htmlspecialchars($guest_email); ?></strong>
                            </p>
                            <?php if ($guest_barangay): ?>
                                <p style="font-size:14px; color:#475569;">
                                    Barangay: <strong><?php echo htmlspecialchars($guest_barangay); ?></strong>
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="quiz-requirements">
                        <h3>📝 Quiz Details</h3>
                        <ul class="req-list">
                            <li>20 questions covering 6 disaster safety categories</li>
                            <li>Estimated time: 20-30 minutes</li>
                            <li>Passing score: 75% or higher</li>
                            <li>Instant results and certification upon passing</li>
                        </ul>
                    </div>

                    <div class="quiz-timeline">
                        <div class="timeline-item">
                            <div class="timeline-icon">❓</div>
                            <h4>20 Questions</h4>
                            <p>Comprehensive coverage of disaster preparedness</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon">⏱️</div>
                            <h4>20-30 Minutes</h4>
                            <p>Average completion time</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon">🎯</div>
                            <h4>75% Required</h4>
                            <p>Passing score for certification</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon">📜</div>
                            <h4>Instant Cert</h4>
                            <p>Get your certificate immediately</p>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary" onclick="startCertQuiz()">
                            <i class="fas fa-play"></i> Start Certification Quiz
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back Home
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── QUIZ MODAL ── -->
    <div id="quiz-modal">
        <div class="quiz-content">
            <div class="quiz-header">
                <div class="quiz-title">📋 Certification Quiz</div>
                <button class="quiz-close" onclick="closeQuiz()">×</button>
            </div>

            <div class="quiz-progress">
                <div class="progress-text">
                    <span>Question <span id="current-question">1</span> / <span id="total-questions">20</span></span>
                    <span>Points: <span id="current-score">0</span></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
                </div>
            </div>

            <div id="quiz-body" style="min-height: 300px;"></div>
        </div>
    </div>

    <script>
        // ── State ──────────────────────────────────────────────────────────────
        var currentQuestions     = [];
        var currentQuestionIndex = 0;
        var score                = 0;
        var totalPoints          = 0;
        var hasAnsweredCurrent   = false;
        var guestEmail           = <?php echo json_encode($guest_email ?? ''); ?>;
        var guestBarangay        = <?php echo json_encode($guest_barangay ?? ''); ?>;

        // ── Open / Close modal ─────────────────────────────────────────────────
        function startCertQuiz() {
            currentQuestionIndex = 0;
            score                = 0;
            totalPoints          = 0;
            hasAnsweredCurrent   = false;

            var modal = document.getElementById('quiz-modal');
            modal.classList.add('show');

            document.getElementById('quiz-body').innerHTML =
                '<div style="text-align:center;padding:40px;">' +
                '<div class="spinner"></div>' +
                '<p style="margin-top:15px;color:#64748b;">Loading certification quiz questions...</p>' +
                '</div>';

            fetch('api/gamification/get-quiz-certification.php')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success && data.questions && data.questions.length > 0) {
                        currentQuestions = data.questions;
                        document.getElementById('total-questions').textContent = data.questions.length;
                        showQuestion();
                    } else {
                        document.getElementById('quiz-body').innerHTML =
                            '<div style="text-align:center;padding:40px;">' +
                            '<p style="color:#ef4444;font-size:18px;">Error loading quiz questions: ' +
                            (data.message || 'No questions found') + '</p>' +
                            '<button class="btn-next" style="max-width:200px;margin:0 auto;" onclick="closeQuiz()">Close</button>' +
                            '</div>';
                    }
                })
                .catch(function(err) {
                    console.error('Quiz load error:', err);
                    document.getElementById('quiz-body').innerHTML =
                        '<div style="text-align:center;padding:40px;">' +
                        '<p style="color:#ef4444;font-size:18px;">Network error loading questions.<br>Please check your connection.</p>' +
                        '<button class="btn-next" style="max-width:200px;margin:10px auto 0;" onclick="closeQuiz()">Close</button>' +
                        '</div>';
                });
        }

        function closeQuiz() {
            document.getElementById('quiz-modal').classList.remove('show');
        }

        // ── Show question ──────────────────────────────────────────────────────
        function showQuestion() {
            hasAnsweredCurrent = false;

            var question      = currentQuestions[currentQuestionIndex];
            var questionNumber = currentQuestionIndex + 1;
            var total         = currentQuestions.length;
            var progress      = (questionNumber / total) * 100;

            document.getElementById('current-question').textContent = questionNumber;
            document.getElementById('current-score').textContent    = totalPoints;
            document.getElementById('progress-fill').style.width    = progress + '%';

            document.getElementById('quiz-body').innerHTML =
                '<div class="question-container">' +
                    '<div class="question-text">' + question.question + '</div>' +
                    '<div class="options-list">' +
                        '<button class="option-btn" onclick="selectAnswer(\'A\',' + question.id + ')"><strong>A:</strong> ' + question.option_a + '</button>' +
                        '<button class="option-btn" onclick="selectAnswer(\'B\',' + question.id + ')"><strong>B:</strong> ' + question.option_b + '</button>' +
                        '<button class="option-btn" onclick="selectAnswer(\'C\',' + question.id + ')"><strong>C:</strong> ' + question.option_c + '</button>' +
                        '<button class="option-btn" onclick="selectAnswer(\'D\',' + question.id + ')"><strong>D:</strong> ' + question.option_d + '</button>' +
                    '</div>' +
                    '<div id="feedback-area"></div>' +
                '</div>';
        }

        // ── Select answer ──────────────────────────────────────────────────────
        function selectAnswer(answer, questionId) {
            if (hasAnsweredCurrent) return;
            hasAnsweredCurrent = true;

            var buttons = document.querySelectorAll('.option-btn');
            buttons.forEach(function(btn) { btn.classList.add('disabled'); });

            fetch('api/gamification/submit-answer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ question_id: questionId, selected_answer: answer })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    alert(data.message || 'There was an issue submitting your answer.');
                    hasAnsweredCurrent = false;
                    buttons.forEach(function(btn) { btn.classList.remove('disabled'); });
                    return;
                }

                // Highlight correct / incorrect
                buttons.forEach(function(btn) {
                    var letter = btn.textContent.trim().charAt(0);
                    if (letter === answer)            btn.classList.add(data.is_correct ? 'correct' : 'incorrect');
                    if (letter === data.correct_answer) btn.classList.add('correct');
                });

                if (data.is_correct) {
                    score++;
                    totalPoints += data.points_earned;
                }

                var isLast = currentQuestionIndex >= currentQuestions.length - 1;

                document.getElementById('feedback-area').innerHTML =
                    '<div class="feedback-box ' + (data.is_correct ? 'correct' : 'incorrect') + '">' +
                        '<div class="feedback-title">' +
                            (data.is_correct ? '✅ Correct!' : '❌ Incorrect') +
                            (data.is_correct ? '<span class="points-earned">+' + data.points_earned + ' points</span>' : '') +
                        '</div>' +
                        '<p class="feedback-text">' + data.explanation + '</p>' +
                    '</div>' +
                    '<div class="quiz-actions">' +
                        '<button class="btn-next" onclick="nextQuestion()">' +
                            (isLast ? 'Finish Quiz 🎉' : 'Next Question →') +
                        '</button>' +
                    '</div>';
            })
            .catch(function(err) {
                console.error('Submit error:', err);
                alert('Error submitting answer. Please try again.');
                hasAnsweredCurrent = false;
                buttons.forEach(function(btn) { btn.classList.remove('disabled'); });
            });
        }

        // ── Next question ──────────────────────────────────────────────────────
        function nextQuestion() {
            currentQuestionIndex++;
            if (currentQuestionIndex < currentQuestions.length) {
                showQuestion();
            } else {
                showResults();
            }
        }

        // ── Results ────────────────────────────────────────────────────────────
        function showResults() {
            var percentage = Math.round((score / currentQuestions.length) * 100);
            var passed     = percentage >= 75;

            var message = percentage >= 80 ? '🌟 Excellent! You\'ve earned your Bantay Bayanihan Certification!' :
                          percentage >= 75 ? '🎉 Congratulations! You passed the certification quiz!' :
                          percentage >= 60 ? '👍 Good job! Try again to earn certification!' :
                          '📚 Keep studying to achieve certification!';

            document.getElementById('quiz-body').innerHTML =
                '<div class="quiz-complete">' +
                    '<span class="complete-icon">' + (passed ? '🎉' : '📚') + '</span>' +
                    '<div class="complete-title">' + (passed ? 'Certification Earned!' : 'Quiz Complete') + '</div>' +
                    '<div class="complete-score">' + score + ' / ' + currentQuestions.length + '</div>' +
                    '<div class="complete-stats">' +
                        '<div class="stat-item"><div class="stat-value">' + percentage + '%</div><div class="stat-label">Accuracy</div></div>' +
                        '<div class="stat-item"><div class="stat-value">' + totalPoints + '</div><div class="stat-label">Points Earned</div></div>' +
                    '</div>' +
                    '<p style="color:#64748b;margin:20px 0;">' + message + '</p>' +
                    '<div class="quiz-actions">' +
                        '<button class="btn-next" onclick="closeQuiz(); location.reload();">Close</button>' +
                    '</div>' +
                '</div>';

            if (passed) {
                saveCertification(percentage);
            }
        }

        // ── Save certification ─────────────────────────────────────────────────
        function saveCertification(pct) {
            var payload = { score: pct };
            <?php if ($user_id): ?>
                payload.user_id = <?php echo (int)$user_id; ?>;
            <?php else: ?>
                payload.email    = guestEmail;
                payload.barangay = guestBarangay;
            <?php endif; ?>

            fetch('api/gamification/save-certification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).catch(function(err) { console.error('Error saving certification:', err); });
        }
    </script>
</body>
</html>
