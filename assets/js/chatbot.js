/**
 * ENHANCED BAGUIO DISASTER PREPAREDNESS CHATBOT
 * With Real-Time API Integration + Gemini AI
 * 
 * Features:
 * - Bilingual (English/Tagalog)
 * - API-powered evacuation center search
 * - Real-time road closure alerts
 * - Active emergency updates
 * - Published drills information
 * - AI-powered responses for unknown questions
 */

(function() {
    'use strict';

    // ============================================
    // CONFIGURATION
    // ============================================
    const CONFIG = {
        apiBaseUrl: window.location.origin + '/cbantay-bayanihan/api',
        colors: {
            primary: '#6161ff',
            secondary: '#ff0065',
            navy: '#00167a'
        }
    };

    // ============================================
    // KNOWLEDGE BASE (Hardcoded Fallbacks)
    // ============================================
    const KNOWLEDGE_BASE = {
        emergencyContacts: [
            { name: 'Baguio MDRRMO', number: '(074) 442-5377', type: 'emergency' },
            { name: 'Baguio Police', number: '(074) 444-1133', type: 'police' },
            { name: 'Baguio Fire Department', number: '(074) 442-2222', type: 'fire' },
            { name: 'Baguio General Hospital', number: '(074) 442-4216', type: 'medical' },
            { name: 'Red Cross Baguio', number: '(074) 442-5557', type: 'medical' },
            { name: 'NDRRMC Hotline', number: '(02) 8911-1406', type: 'emergency' },
            { name: 'Emergency 911', number: '911', type: 'emergency' }
        ],

        disasterTips: {
            earthquake: {
                en: [
                    "DROP to the ground",
                    "Take COVER under a sturdy table",
                    "HOLD ON until shaking stops",
                    "Stay away from windows and heavy objects",
                    "If outdoors, move to an open area",
                    "After shaking, check for injuries and damage"
                ],
                tl: [
                    "DUMAPA sa sahig",
                    "KUMUHA ng PROTEKSYON sa ilalim ng matibay na mesa",
                    "HUMAWAK hanggang tumigil ang lindol",
                    "Lumayo sa bintana at mabibigat na bagay",
                    "Kung nasa labas, pumunta sa lugarang walang hadlang",
                    "Pagkatapos ng lindol, tignan kung may sugat o pinsala"
                ]
            },
            typhoon: {
                en: [
                    "Monitor weather updates regularly",
                    "Secure loose objects outside your home",
                    "Stock up on food, water, and medicine",
                    "Charge all devices and prepare flashlights",
                    "Stay indoors during the typhoon",
                    "Evacuate if authorities advise"
                ],
                tl: [
                    "Makinig sa ulat ng panahon",
                    "Siguruhing nakatali ang mga bagay sa labas",
                    "Mag-imbak ng pagkain, tubig, at gamot",
                    "I-charge ang lahat ng gadget at maghanda ng ilaw",
                    "Manatili sa loob ng bahay",
                    "Lumikas kung may utos mula sa authorities"
                ]
            },
            landslide: {
                en: [
                    "Evacuate immediately if landslide is imminent",
                    "Move away from the path of the landslide",
                    "Listen for unusual sounds (trees cracking, boulders)",
                    "Watch for tilted trees or cracks in the ground",
                    "Alert neighbors and emergency services",
                    "Do not return until area is declared safe"
                ],
                tl: [
                    "Lumikas kaagad kung may pagguho",
                    "Lumayo sa daan ng pagguho",
                    "Makinig sa kakaibang tunog (yumuyanig na puno, gumugulong na bato)",
                    "Bantayan ang puno na nakatagilid o bitak sa lupa",
                    "Abisuhan ang kapitbahay at emergency services",
                    "Huwag bumalik hanggang ligtas na ang lugar"
                ]
            },
            flood: {
                en: [
                    "Move to higher ground immediately",
                    "Avoid walking or driving through floodwater",
                    "Turn off electricity at the main breaker",
                    "Do not touch electrical equipment if wet",
                    "Evacuate if water rises rapidly",
                    "Wait for official all-clear before returning"
                ],
                tl: [
                    "Pumunta sa mas mataas na lugar",
                    "Iwasan ang paglakad o pagmamaneho sa baha",
                    "Patayin ang kuryente sa main breaker",
                    "Huwag hawakan ang electrical equipment kung basa",
                    "Lumikas kung mabilis ang pagtaas ng tubig",
                    "Maghintay ng opisyal na pahintulot bago bumalik"
                ]
            },
            fire: {
                en: [
                    "Alert everyone - shout 'FIRE!'",
                    "Evacuate immediately, don't collect belongings",
                    "Crawl low under smoke",
                    "Feel doors before opening (hot = fire behind)",
                    "Once out, stay out - never go back inside",
                    "Call fire department: (074) 442-2222"
                ],
                tl: [
                    "Abisuhan ang lahat - sumigaw ng 'SUNOG!'",
                    "Lumikas kaagad, huwag nang kumuha ng gamit",
                    "Gumapang kung may usok",
                    "Damhin ang pinto bago buksan (mainit = may apoy sa likod)",
                    "Kapag labas na, huwag nang bumalik",
                    "Tumawag sa bumbero: (074) 442-2222"
                ]
            }
        },

        baguioBarangays: [
            "Pacdal", "Guisad Central", "Irisan", "Bakakeng Central", "Bakakeng North",
            "City Camp Central", "City Camp Proper", "Scout Barrio", "Bayan Park East",
            "Bayan Park West (Bayan Park)", "Bayan Park Village", "Session Road Area",
            "Malcolm Square-Perfecto", "Legarda-Burnham-Kisad", "Abanao-Zandueta-Kayong-Chugum",
            "Market Subdivision, Upper", "Magsaysay, Lower", "Magsaysay, Upper", "Lualhati",
            "Kayang-Hilltop", "Kias", "Pinget", "Pinsao Proper", "Pinsao Pilot Project",
            "Ambiong", "Aurora Hill Proper", "Aurora Hill, North Central", "Aurora Hill, South Central",
            "Bal-Marcoville", "Balsigan", "BGH Compound", "Brookside", "Bonifacio-Caguioa-Rimando",
            "Camdas Subdivision", "Campo Filipino", "Capitan Perez", "Cabinet Hill-Teacher's Camp",
            "Cresencia Village", "Country Club Village", "Cristina Homes", "Dagsian, Lower",
            "Dagsian, Upper", "Dizon Subdivision", "Dominican Hill-Mirador", "Dontogan",
            "DPS Area", "Engineers Hill", "Fairview Village", "Fort del Pilar", "Gabriel Silang",
            "General Emilio F. Aguinaldo", "General Luna, Lower", "General Luna, Upper", "Gibraltar",
            "Greenwater Village", "Happy Hollow", "Happy Homes", "Harrison-Claudio Carantes",
            "Hillside", "Holy Ghost Extension", "Holy Ghost Proper", "Honeymoon", "Imelda R. Marcos",
            "Imelda Village", "Kabayanihan", "Kagitingan", "Kayang Extension", "Lopez Jaena",
            "Loakan Proper", "Loakan-Liwanag", "Loakan-Apugan", "Lourdes Subdivision Extension",
            "Lourdes Subdivision, Lower", "Lourdes Subdivision Proper", "Lucnab", "Liwanag-Loakan",
            "Marcoville", "Maricoville", "Middle Quezon Hill", "Military Cut-off", "Mines View Park",
            "Modern Site, East", "Modern Site, West", "MRR-Queen of Peace", "Naguilian Road",
            "Outlook Drive", "Pacdal Heights", "Phil-Am", "Pinget, Tanor", "Pucsusan",
            "Quezon Hill Proper", "Quezon Hill, Upper", "Quirino Hill, East", "Quirino Hill, West",
            "Quirino Hill, Lower", "Quirino Hill, Middle", "Quirino-Magsaysay, Lower",
            "Quirino-Magsaysay, Upper", "Rizal Monument Area", "Rock Quarry, Lower",
            "Rock Quarry, Middle", "Rock Quarry, Upper", "Saint Joseph Village", "Salud Mitra",
            "San Antonio Village", "San Luis Village", "San Roque Village", "Sanitary Camp, North",
            "Sanitary Camp, South", "Santa Escolastica", "Santo Rosario", "Santo Tomas Proper",
            "Santo Tomas School Area", "Scaddan", "SLU-SVP Housing Village", "South Drive",
            "Slaughter House Area", "Teodora Alonzo", "Trancoville", "Victoria Village",
            "SIR", "Apugan-Loakan"
        ]
    };

    // ============================================
    // API INTEGRATION FUNCTIONS
    // ============================================
    
    async function fetchAllEvacuationSites() {
        try {
            const response = await fetch(`${CONFIG.apiBaseUrl}/evacuation/get-sites.php`);
            const data = await response.json();
            
            if (data.success && data.data) {
                return data.data;
            }
            return [];
        } catch (error) {
            console.error('Error fetching evacuation sites:', error);
            return [];
        }
    }

    async function searchEvacuationByBarangay(barangay) {
        try {
            const sites = await fetchAllEvacuationSites();
            return sites.filter(site => 
                site.barangay.toLowerCase().includes(barangay.toLowerCase())
            );
        } catch (error) {
            console.error('Error searching by barangay:', error);
            return [];
        }
    }

    async function fetchRoadClosures() {
        try {
            const response = await fetch(`${CONFIG.apiBaseUrl}/admin/get-closures.php?status=active`);
            const data = await response.json();
            
            if (data.success && data.data) {
                return data.data;
            }
            return [];
        } catch (error) {
            console.error('Error fetching road closures:', error);
            return [];
        }
    }

    async function fetchPublishedDrills() {
        try {
            const response = await fetch(`${CONFIG.apiBaseUrl}/drills/get-published.php`);
            const data = await response.json();
            
            if (data.success && data.data) {
                return data.data;
            }
            return [];
        } catch (error) {
            console.error('Error fetching drills:', error);
            return [];
        }
    }

    async function fetchAnnouncements() {
        try {
            const response = await fetch(`${CONFIG.apiBaseUrl}/announcements/get-latest.php?limit=3`);
            const data = await response.json();
            
            if (data.success && data.data) {
                return data.data;
            }
            return [];
        } catch (error) {
            console.error('Error fetching announcements:', error);
            return [];
        }
    }

    // ============================================
    // AI INTEGRATION (GEMINI)
    // ============================================
    
    /**
     * Ask AI for unknown questions using Gemini
     */
    async function askAI(message, lang) {
        try {
            const response = await fetch(`${CONFIG.apiBaseUrl}/ai/gemini-chat.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    message: message,
                    language: lang 
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                return data.response;
            } else {
                // AI failed, return fallback message
                return lang === 'tl'
                    ? "Pasensya po, may problema sa AI. Magtanong po ng tungkol sa:\n• Emergency contacts\n• Evacuation centers\n• Disaster safety tips\n• Road closures\n• Emergency kit"
                    : "I'm sorry, I'm having trouble understanding that. You can ask about:\n• Emergency contacts\n• Evacuation centers\n• Disaster safety tips\n• Road closures\n• Emergency kit";
            }
        } catch (error) {
            console.error('AI Error:', error);
            
            // Network error, return fallback
            return lang === 'tl'
                ? "Pasensya po, may koneksyon problema. Maari po kayong magtanong tungkol sa emergency contacts, evacuation centers, disaster safety tips, road closures, o emergency kit."
                : "Sorry, I'm having connection issues. You can ask about emergency contacts, evacuation centers, disaster safety tips, road closures, or emergency kit.";
        }
    }

    // ============================================
    // LANGUAGE DETECTION
    // ============================================
    function detectLanguage(text) {
        const tagalogWords = [
            'ano', 'paano', 'saan', 'kumusta', 'po', 'opo', 'salamat',
            'ako', 'ikaw', 'tayo', 'kami', 'sila', 'sa', 'ng', 'ang',
            'mga', 'na', 'ay', 'ni', 'kung', 'kapag', 'pero', 'at',
            'lumikas', 'lindol', 'bagyo', 'sunog', 'baha', 'tulong',
            'malapit', 'pumunta', 'bahay', 'pamilya', 'ligtas'
        ];
        
        const lowerText = text.toLowerCase();
        const hasTagalog = tagalogWords.some(word => lowerText.includes(word));
        
        return hasTagalog ? 'tl' : 'en';
    }

    // ============================================
    // INTENT DETECTION
    // ============================================
    function detectIntent(text) {
        const lower = text.toLowerCase();

        // Greetings
        if (/^(hi|hello|hey|kumusta|kamusta|mabuhay|good morning|good afternoon)/i.test(lower)) {
            return 'greeting';
        }

        // Emergency contacts
        if (/emergency|hotline|contact|number|911|help|tulong|numero|tawagan/i.test(lower)) {
            return 'emergency_contacts';
        }

        // Evacuation
        if (/evacuat|lumikas|center|nearest|malapit|saan.*pumunta|safe.*place/i.test(lower)) {
            return 'evacuation';
        }

        // Road closures
        if (/road|closure|sarado|daan|traffic|blocked|hindi.*dumaan/i.test(lower)) {
            return 'road_closures';
        }

        // Specific disasters
        if (/earthquake|lindol/i.test(lower)) return 'earthquake';
        if (/typhoon|bagyo/i.test(lower)) return 'typhoon';
        if (/landslide|pagguho/i.test(lower)) return 'landslide';
        if (/flood|baha/i.test(lower)) return 'flood';
        if (/fire|sunog/i.test(lower)) return 'fire';

        // Emergency kit
        if (/kit|bag|dadalhin|emergency.*bag|go.*bag|preparedness|handa/i.test(lower)) {
            return 'emergency_kit';
        }

        // Drills
        if (/drill|practice|ensayo|training|sanay/i.test(lower)) {
            return 'drills';
        }

        // Announcements
        if (/announcement|balita|news|update|anunsyo/i.test(lower)) {
            return 'announcements';
        }

        return 'unknown';
    }

    // ============================================
    // RESPONSE GENERATION
    // ============================================
    async function generateResponse(intent, userMessage, language) {
        const lang = language || detectLanguage(userMessage);

        switch(intent) {
            case 'greeting':
                return lang === 'tl' 
                    ? "Kumusta! Ako po ay Bantay Bot, inyong gabay sa disaster preparedness sa Baguio City. Paano ko kayo matutulungan?"
                    : "Hello! I'm Bantay Bot, your Baguio City disaster preparedness assistant. How can I help you today?";

            case 'emergency_contacts':
                return formatEmergencyContacts(lang);

            case 'evacuation':
                return await handleEvacuationQuery(userMessage, lang);

            case 'road_closures':
                return await handleRoadClosuresQuery(lang);

            case 'earthquake':
            case 'typhoon':
            case 'landslide':
            case 'flood':
            case 'fire':
                return formatDisasterTips(intent, lang);

            case 'emergency_kit':
                return formatEmergencyKit(lang);

            case 'drills':
                return await handleDrillsQuery(lang);

            case 'announcements':
                return await handleAnnouncementsQuery(lang);

            default:
                // Unknown question -> Ask AI
                return await askAI(userMessage, lang);
        }
    }

    // ============================================
    // FORMAT FUNCTIONS WITH API DATA
    // ============================================

    function formatEmergencyContacts(lang) {
        let response = lang === 'tl' 
            ? "📞 MGA EMERGENCY HOTLINES NG BAGUIO:\n\n"
            : "📞 BAGUIO EMERGENCY HOTLINES:\n\n";

        KNOWLEDGE_BASE.emergencyContacts.forEach(contact => {
            response += `${contact.name}: ${contact.number}\n`;
        });

        response += lang === 'tl'
            ? "\nTawagan kaagad ang mga ito kung may emergency!"
            : "\nCall these numbers immediately in case of emergency!";

        return response;
    }

    async function handleEvacuationQuery(userMessage, lang) {
        const lower = userMessage.toLowerCase();
        
        const barangayMatch = KNOWLEDGE_BASE.baguioBarangays.find(brgy => 
            lower.includes(brgy.toLowerCase())
        );

        if (barangayMatch) {
            const sites = await searchEvacuationByBarangay(barangayMatch);
            
            if (sites && sites.length > 0) {
                let response = lang === 'tl'
                    ? `🏛️ EVACUATION SITES SA ${barangayMatch.toUpperCase()}:\n\n`
                    : `🏛️ EVACUATION SITES IN ${barangayMatch.toUpperCase()}:\n\n`;

                sites.slice(0, 5).forEach((site, index) => {
                    response += `${index + 1}. ${site.name}\n`;
                    response += `   Type: ${site.type.replace('_', ' ')}\n`;
                    if (site.capacity) response += `   Capacity: ${site.capacity} persons\n`;
                    response += '\n';
                });

                return response;
            }
        }

        return lang === 'tl'
            ? "🏛️ Para malaman ang pinakamalapit na evacuation center, pumunta sa aming map sa homepage at i-click ang 'Find Nearest Centers' button.\n\nMaaari din kayong magtanong ng specific na barangay, halimbawa: 'evacuation centers sa Pacdal'"
            : "🏛️ To find the nearest evacuation center, go to our map on the homepage and click the 'Find Nearest Centers' button.\n\nYou can also ask about a specific barangay, for example: 'evacuation centers in Pacdal'";
    }

    async function handleRoadClosuresQuery(lang) {
        const closures = await fetchRoadClosures();

        if (!closures || closures.length === 0) {
            return lang === 'tl'
                ? "✅ Walang road closures sa ngayon. Lahat ng daan ay bukas at ligtas."
                : "✅ No active road closures at the moment. All roads are open and safe.";
        }

        let response = lang === 'tl'
            ? "⚠️ AKTIBONG ROAD CLOSURES SA BAGUIO:\n\n"
            : "⚠️ ACTIVE ROAD CLOSURES IN BAGUIO:\n\n";

        closures.forEach((closure, index) => {
            response += `${index + 1}. ${closure.description}\n`;
            if (closure.severity) response += `   Severity: ${closure.severity}\n`;
            response += '\n';
        });

        response += lang === 'tl'
            ? "Mag-ingat sa pagmamaneho at gumamit ng alternative routes."
            : "Please drive carefully and use alternative routes.";

        return response;
    }

    async function handleDrillsQuery(lang) {
        const drills = await fetchPublishedDrills();

        if (!drills || drills.length === 0) {
            return lang === 'tl'
                ? "ℹ️ Walang scheduled drills sa ngayon. Abangan ang aming announcements para sa susunod na drill."
                : "ℹ️ No scheduled drills at the moment. Watch for our announcements for upcoming drills.";
        }

        let response = lang === 'tl'
            ? "🎯 MGA PUBLISHED DRILLS:\n\n"
            : "🎯 PUBLISHED DRILLS:\n\n";

        drills.slice(0, 3).forEach((drill, index) => {
            response += `${index + 1}. ${drill.title}\n`;
            if (drill.description) response += `   ${drill.description}\n`;
            if (drill.duration_minutes) response += `   Duration: ${drill.duration_minutes} minutes\n`;
            response += '\n';
        });

        response += lang === 'tl'
            ? "Makita ang buong detalye sa homepage."
            : "See full details on the homepage.";

        return response;
    }

    async function handleAnnouncementsQuery(lang) {
        const announcements = await fetchAnnouncements();

        if (!announcements || announcements.length === 0) {
            return lang === 'tl'
                ? "ℹ️ Walang bagong announcements sa ngayon."
                : "ℹ️ No new announcements at the moment.";
        }

        let response = lang === 'tl'
            ? "📢 MGA LATEST ANNOUNCEMENTS:\n\n"
            : "📢 LATEST ANNOUNCEMENTS:\n\n";

        announcements.forEach((ann, index) => {
            response += `${index + 1}. ${ann.title}\n`;
            if (ann.message) {
                const preview = ann.message.substring(0, 100);
                response += `   ${preview}${ann.message.length > 100 ? '...' : ''}\n`;
            }
            response += '\n';
        });

        return response;
    }

    function formatDisasterTips(disaster, lang) {
        const tips = KNOWLEDGE_BASE.disasterTips[disaster][lang];
        const title = {
            en: {
                earthquake: "🏚️ EARTHQUAKE SAFETY",
                typhoon: "🌀 TYPHOON SAFETY",
                landslide: "⛰️ LANDSLIDE SAFETY",
                flood: "🌊 FLOOD SAFETY",
                fire: "🔥 FIRE SAFETY"
            },
            tl: {
                earthquake: "🏚️ KALIGTASAN SA LINDOL",
                typhoon: "🌀 KALIGTASAN SA BAGYO",
                landslide: "⛰️ KALIGTASAN SA PAGGUHO",
                flood: "🌊 KALIGTASAN SA BAHA",
                fire: "🔥 KALIGTASAN SA SUNOG"
            }
        };

        let response = title[lang][disaster] + "\n\n";
        tips.forEach((tip, index) => {
            response += `${index + 1}. ${tip}\n`;
        });

        return response;
    }

    function formatEmergencyKit(lang) {
        const items = {
            en: [
                "Water (1 gallon per person per day for 3 days)",
                "Non-perishable food (3-day supply)",
                "Flashlight and extra batteries",
                "First aid kit",
                "Whistle (to signal for help)",
                "Dust masks",
                "Important documents (IDs, insurance papers)",
                "Cash and credit cards",
                "Emergency contact list"
            ],
            tl: [
                "Tubig (1 galon bawat tao kada araw para sa 3 araw)",
                "Pagkaing hindi madaling masira (3 araw)",
                "Flashlight at extra batteries",
                "First aid kit",
                "Pito (para humingi ng tulong)",
                "Face mask",
                "Mahahalagang dokumento (ID, insurance)",
                "Pera at credit cards",
                "Listahan ng emergency contacts"
            ]
        };

        let response = lang === 'tl'
            ? "🎒 MGA DAPAT NASA EMERGENCY KIT:\n\n"
            : "🎒 EMERGENCY KIT ESSENTIALS:\n\n";

        items[lang].forEach((item, index) => {
            response += `${index + 1}. ${item}\n`;
        });

        response += lang === 'tl'
            ? "\nSiguruhing laging handa ang inyong emergency kit!"
            : "\nMake sure your emergency kit is always ready!";

        return response;
    }

    // ============================================
    // UI CREATION
    // ============================================
    function createChatUI() {
        const container = document.createElement('div');
        container.id = 'bantay-chatbot';
        container.innerHTML = `
            <button id="chatbot-trigger" class="chatbot-trigger" aria-label="Open chatbot">
                <span class="trigger-icon">💬</span>
            </button>

            <div id="chatbot-window" class="chatbot-window">
                <div class="chatbot-header">
                    <div class="header-content">
                        <span class="bot-avatar">🛡️</span>
                        <div>
                            <div class="bot-name">Bantay Bot</div>
                            <div class="bot-status">Online • AI-Powered</div>
                        </div>
                    </div>
                    <button id="chatbot-close" class="close-btn" aria-label="Close chatbot">×</button>
                </div>

                <div id="chatbot-messages" class="chatbot-messages">
                    <div class="message bot-message">
                        <div class="message-content">
                            Kumusta! I'm Bantay Bot, your AI-powered Baguio disaster preparedness assistant. 🛡️
                        </div>
                    </div>
                </div>

                <div class="chatbot-quick-actions">
                    <button class="quick-btn" data-message="emergency contacts">📞 Contacts</button>
                    <button class="quick-btn" data-message="evacuation centers">🏛️ Evacuation</button>
                    <button class="quick-btn" data-message="earthquake safety">🏚️ Earthquake</button>
                    <button class="quick-btn" data-message="kit">🎒 Kit</button>
                </div>

                <div class="chatbot-input-area">
                    <input 
                        type="text" 
                        id="chatbot-input" 
                        placeholder="Ask me anything..." 
                        autocomplete="off"
                    />
                    <button id="chatbot-send" class="send-btn" aria-label="Send message">
                        <span>➤</span>
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(container);
        attachStyles();
        attachEventListeners();
    }

    function attachStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .chatbot-trigger {
                position: fixed;
                bottom: 25px;
                right: 25px;
                width: 65px;
                height: 65px;
                border-radius: 50%;
                background: linear-gradient(135deg, ${CONFIG.colors.primary}, ${CONFIG.colors.secondary});
                border: none;
                box-shadow: 0 8px 25px rgba(97, 97, 255, 0.4);
                cursor: pointer;
                z-index: 9998;
                transition: all 0.3s ease;
                animation: pulse 2s infinite;
            }

            .chatbot-trigger:hover {
                transform: scale(1.1);
                box-shadow: 0 12px 35px rgba(97, 97, 255, 0.5);
            }

            .trigger-icon {
                font-size: 32px;
                display: block;
            }

            @keyframes pulse {
                0%, 100% { box-shadow: 0 8px 25px rgba(97, 97, 255, 0.4); }
                50% { box-shadow: 0 8px 35px rgba(255, 0, 101, 0.5); }
            }

            .chatbot-window {
                position: fixed;
                bottom: 25px;
                right: 25px;
                width: 400px;
                height: 600px;
                background: white;
                border-radius: 20px;
                box-shadow: 0 15px 50px rgba(0, 22, 122, 0.25);
                display: none;
                flex-direction: column;
                z-index: 9999;
                overflow: hidden;
                border: 3px solid ${CONFIG.colors.primary};
            }

            .chatbot-window.open {
                display: flex;
                animation: slideUp 0.3s ease;
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .chatbot-header {
                background: linear-gradient(135deg, ${CONFIG.colors.primary}, ${CONFIG.colors.secondary});
                color: white;
                padding: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .header-content {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .bot-avatar {
                font-size: 32px;
            }

            .bot-name {
                font-weight: 800;
                font-size: 18px;
            }

            .bot-status {
                font-size: 12px;
                opacity: 0.9;
            }

            .close-btn {
                background: none;
                border: none;
                color: white;
                font-size: 32px;
                cursor: pointer;
                padding: 0;
                width: 36px;
                height: 36px;
                border-radius: 50%;
                transition: all 0.2s;
            }

            .close-btn:hover {
                background: rgba(255, 255, 255, 0.2);
                transform: rotate(90deg);
            }

            .chatbot-messages {
                flex: 1;
                overflow-y: auto;
                padding: 20px;
                background: linear-gradient(135deg, #f8f9ff, #fef3f8);
            }

            .message {
                margin-bottom: 15px;
                animation: fadeIn 0.3s ease;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .message-content {
                padding: 12px 16px;
                border-radius: 12px;
                max-width: 80%;
                word-wrap: break-word;
                white-space: pre-wrap;
                line-height: 1.5;
            }

            .bot-message .message-content {
                background: white;
                color: ${CONFIG.colors.navy};
                border: 2px solid ${CONFIG.colors.primary};
                border-radius: 12px 12px 12px 4px;
            }

            .user-message {
                text-align: right;
            }

            .user-message .message-content {
                background: linear-gradient(135deg, ${CONFIG.colors.primary}, ${CONFIG.colors.secondary});
                color: white;
                display: inline-block;
                border-radius: 12px 12px 4px 12px;
                margin-left: auto;
            }

            .chatbot-quick-actions {
                padding: 15px;
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                border-top: 2px solid #f0f4ff;
            }

            .quick-btn {
                background: linear-gradient(135deg, #f0f4ff, #fef3f8);
                border: 2px solid ${CONFIG.colors.primary};
                color: ${CONFIG.colors.navy};
                padding: 8px 14px;
                border-radius: 20px;
                cursor: pointer;
                font-size: 13px;
                font-weight: 700;
                transition: all 0.2s;
            }

            .quick-btn:hover {
                background: ${CONFIG.colors.primary};
                color: white;
                transform: translateY(-2px);
            }

            .chatbot-input-area {
                display: flex;
                gap: 10px;
                padding: 15px;
                border-top: 2px solid #f0f4ff;
            }

            #chatbot-input {
                flex: 1;
                padding: 12px 16px;
                border: 2px solid #e2e8f0;
                border-radius: 25px;
                font-size: 14px;
                outline: none;
                transition: all 0.3s;
            }

            #chatbot-input:focus {
                border-color: ${CONFIG.colors.primary};
                box-shadow: 0 0 0 4px rgba(97, 97, 255, 0.1);
            }

            .send-btn {
                width: 45px;
                height: 45px;
                border-radius: 50%;
                background: linear-gradient(135deg, ${CONFIG.colors.primary}, ${CONFIG.colors.secondary});
                border: none;
                color: white;
                font-size: 20px;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .send-btn:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(97, 97, 255, 0.4);
            }

            .chatbot-messages::-webkit-scrollbar {
                width: 6px;
            }

            .chatbot-messages::-webkit-scrollbar-track {
                background: #f0f4ff;
            }

            .chatbot-messages::-webkit-scrollbar-thumb {
                background: ${CONFIG.colors.primary};
                border-radius: 3px;
            }

            @media (max-width: 768px) {
                .chatbot-window {
                    width: calc(100% - 20px);
                    height: calc(100% - 20px);
                    bottom: 10px;
                    right: 10px;
                }
            }

            .typing-indicator {
                display: flex;
                gap: 4px;
                padding: 12px 16px;
            }

            .typing-indicator span {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: ${CONFIG.colors.primary};
                animation: typing 1.4s infinite;
            }

            .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
            .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

            @keyframes typing {
                0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
                30% { transform: translateY(-10px); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }

    function attachEventListeners() {
        const trigger = document.getElementById('chatbot-trigger');
        const closeBtn = document.getElementById('chatbot-close');
        const window = document.getElementById('chatbot-window');
        const sendBtn = document.getElementById('chatbot-send');
        const input = document.getElementById('chatbot-input');
        const quickBtns = document.querySelectorAll('.quick-btn');

        trigger.addEventListener('click', () => {
            window.classList.add('open');
            trigger.style.display = 'none';
            input.focus();
        });

        closeBtn.addEventListener('click', () => {
            window.classList.remove('open');
            trigger.style.display = 'flex';
        });

        sendBtn.addEventListener('click', handleSend);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') handleSend();
        });

        quickBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const message = btn.dataset.message;
                input.value = message;
                handleSend();
            });
        });
    }

    async function handleSend() {
        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();
        
        if (!message) return;

        addMessage(message, 'user');
        input.value = '';

        showTyping();

        const intent = detectIntent(message);
        const language = detectLanguage(message);
        const response = await generateResponse(intent, message, language);

        hideTyping();
        addMessage(response, 'bot');
    }

    function addMessage(text, sender) {
        const messagesDiv = document.getElementById('chatbot-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.textContent = text;
        
        messageDiv.appendChild(contentDiv);
        messagesDiv.appendChild(messageDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function showTyping() {
        const messagesDiv = document.getElementById('chatbot-messages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message bot-message';
        typingDiv.id = 'typing-indicator';
        typingDiv.innerHTML = `
            <div class="message-content typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;
        messagesDiv.appendChild(typingDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function hideTyping() {
        const typing = document.getElementById('typing-indicator');
        if (typing) typing.remove();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createChatUI);
    } else {
        createChatUI();
    }

})();