<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * AIQuizService — Fixed version.
 *
 * Fixes:
 *  1. Class-wise questions now match exact NCERT/curriculum syllabus
 *  2. Zero duplicate questions (hash-tracked + forced unique constraint in prompt)
 *  3. Difficulty labels work correctly (beginner = simpler language/concepts)
 *  4. Tutor chat with subject context, history, and ChatGPT-style persistence
 *  5. Uniform source tagging across all generators
 *
 * config/services.php:
 *   'groq' => [
 *       'api_key'      => env('GROQ_API_KEY'),
 *       'model'        => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
 *       'vision_model' => env('GROQ_VISION_MODEL', 'meta-llama/llama-4-scout-17b-16e-instruct'),
 *   ],
 */
class AIQuizService
{
    private string $apiKey;
    private string $model;
    private string $visionModel;
    private string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    // NCERT chapter map: class → subject → list of chapters/topics
    // This ensures generated questions are curriculum-matched
    private array $curriculumMap = [
        '6'  => [
            'Mathematics'      => 'Knowing Our Numbers, Whole Numbers, Playing with Numbers, Basic Geometrical Ideas, Understanding Elementary Shapes, Integers, Fractions, Decimals, Data Handling, Mensuration, Algebra, Ratio and Proportion, Symmetry, Practical Geometry',
            'Science'          => 'Food: Where Does It Come From, Components of Food, Fibre to Fabric, Sorting Materials into Groups, Separation of Substances, Changes Around Us, Getting to Know Plants, Body Movements, The Living Organisms, Motion and Measurement of Distances, Light Shadows and Reflections, Electricity and Circuits, Fun with Magnets, Water, Air Around Us, Garbage In Garbage Out',
            'History'          => 'What Where How and When, On The Trail of the Earliest People, From Gathering to Growing Food, In the Earliest Cities, What Books and Burials Tell Us, Kingdoms Kings and an Early Republic, New Questions and Ideas, Ashoka the Emperor, Vital Villages Thriving Towns, Traders Kings and Pilgrims, New Empires and Kingdoms, Buildings Paintings and Books',
            'Geography'        => 'The Earth in the Solar System, Globe Latitudes and Longitudes, Motions of the Earth, Maps, Major Domains of the Earth, Major Landforms of the Earth, Our Country India, India Climate Vegetation and Wildlife',
            'English'          => 'Grammar: Nouns Pronouns Verbs Adjectives Adverbs Tenses, Comprehension, Essay Writing, Letter Writing',
        ],
        '7'  => [
            'Mathematics'      => 'Integers, Fractions and Decimals, Data Handling, Simple Equations, Lines and Angles, The Triangle and its Properties, Congruence of Triangles, Comparing Quantities, Rational Numbers, Practical Geometry, Perimeter and Area, Algebraic Expressions, Exponents and Powers, Symmetry, Visualising Solid Shapes',
            'Science'          => 'Nutrition in Plants, Nutrition in Animals, Fibre to Fabric, Heat, Acids Bases and Salts, Physical and Chemical Changes, Weather Climate and Adaptations, Winds Storms and Cyclones, Soil, Respiration in Organisms, Transportation in Animals and Plants, Reproduction in Plants, Motion and Time, Electric Current and its Effects, Light, Water: A Precious Resource, Forests Our Lifeline, Wastewater Story',
            'History'          => 'Tracing Changes Through a Thousand Years, New Kings and Kingdoms, The Delhi Sultans, The Mughal Empire, Rulers and Buildings, Towns Traders and Craftspersons, Tribes Nomads and Settled Communities, Devotional Paths to the Divine, The Making of Regional Cultures, Eighteenth Century Political Formations',
            'Geography'        => 'Environment, Inside Our Earth, Our Changing Earth, Air, Water, Natural Vegetation and Wildlife, Human Environment Settlement Transport and Communication, Human Environment Interactions, Life in the Temperate Grasslands, Life in the Deserts',
            'English'          => 'Grammar: Parts of Speech, Tenses, Active Passive Voice, Direct Indirect Speech, Comprehension, Essay and Letter Writing',
        ],
        '8'  => [
            'Mathematics'      => 'Rational Numbers, Linear Equations in One Variable, Understanding Quadrilaterals, Practical Geometry, Data Handling, Squares and Square Roots, Cubes and Cube Roots, Comparing Quantities, Algebraic Expressions and Identities, Visualising Solid Shapes, Mensuration, Exponents and Powers, Direct and Inverse Proportions, Factorisation, Introduction to Graphs, Playing with Numbers',
            'Science'          => 'Crop Production and Management, Microorganisms Friend and Foe, Synthetic Fibres and Plastics, Materials Metals and Non-Metals, Coal and Petroleum, Combustion and Flame, Conservation of Plants and Animals, Cell Structure and Functions, Reproduction in Animals, Reaching the Age of Adolescence, Force and Pressure, Friction, Sound, Chemical Effects of Electric Current, Some Natural Phenomena, Light, Stars and the Solar System, Pollution of Air and Water',
            'History'          => 'How When and Where, From Trade to Territory, Ruling the Countryside, Tribals Dikus and the Vision of a Golden Age, When People Rebel, Colonialism and the City, Weavers Iron Smelters and Factory Owners, Civilising the Native Educating the Nation, Women Caste and Reform, The Changing World of Visual Arts, The Making of the National Movement, India After Independence',
            'Geography'        => 'Resources, Land Soil Water Natural Vegetation and Wildlife, Mineral and Power Resources, Agriculture, Industries, Human Resources',
            'English'          => 'Grammar: Clauses Phrases, Reported Speech, Modals, Conditionals, Reading Comprehension, Creative Writing',
        ],
        '9'  => [
            'Mathematics'      => 'Number Systems, Polynomials, Coordinate Geometry, Linear Equations in Two Variables, Introduction to Euclids Geometry, Lines and Angles, Triangles, Quadrilaterals, Areas of Parallelograms and Triangles, Circles, Constructions, Herons Formula, Surface Areas and Volumes, Statistics, Probability',
            'Physics'          => 'Motion, Force and Newtons Laws, Gravitation, Work and Energy, Sound, Matter in Our Surroundings',
            'Chemistry'        => 'Is Matter Around Us Pure, Atoms and Molecules, Structure of the Atom',
            'Biology'          => 'The Fundamental Unit of Life - Cell, Tissues, Diversity in Living Organisms, Why Do We Fall Ill, Natural Resources, Improvement in Food Resources',
            'History'          => 'The French Revolution, Socialism in Europe and the Russian Revolution, Nazism and the Rise of Hitler, Forest Society and Colonialism, Pastoralists in the Modern World',
            'Geography'        => 'India Size and Location, Physical Features of India, Drainage, Climate, Natural Vegetation and Wildlife, Population',
            'Economics'        => 'The Story of Village Palampur, People as Resource, Poverty as a Challenge, Food Security in India',
            'English'          => 'Beehive and Moments textbooks, Grammar: Tenses Articles Prepositions, Essay Letter Report Writing',
        ],
        '10' => [
            'Mathematics'      => 'Real Numbers, Polynomials, Pair of Linear Equations in Two Variables, Quadratic Equations, Arithmetic Progressions, Triangles, Coordinate Geometry, Introduction to Trigonometry, Some Applications of Trigonometry, Circles, Constructions, Areas Related to Circles, Surface Areas and Volumes, Statistics, Probability',
            'Physics'          => 'Light Reflection and Refraction, Human Eye and the Colourful World, Electricity, Magnetic Effects of Electric Current, Sources of Energy',
            'Chemistry'        => 'Chemical Reactions and Equations, Acids Bases and Salts, Metals and Non-Metals, Carbon and its Compounds, Periodic Classification of Elements',
            'Biology'          => 'Life Processes, Control and Coordination, How do Organisms Reproduce, Heredity and Evolution, Our Environment, Management of Natural Resources',
            'History'          => 'The Rise of Nationalism in Europe, Nationalism in India, The Making of a Global World, The Age of Industrialisation, Print Culture and the Modern World',
            'Geography'        => 'Resources and Development, Forest and Wildlife Resources, Water Resources, Agriculture, Minerals and Energy Resources, Manufacturing Industries, Lifelines of National Economy',
            'Economics'        => 'Development, Sectors of the Indian Economy, Money and Credit, Globalisation and the Indian Economy, Consumer Rights',
            'English'          => 'First Flight and Footprints textbooks, Grammar: Advanced Tenses, Writing Skills, Literature Analysis',
        ],
        '11' => [
            'Mathematics'      => 'Sets, Relations and Functions, Trigonometric Functions, Principle of Mathematical Induction, Complex Numbers and Quadratic Equations, Linear Inequalities, Permutations and Combinations, Binomial Theorem, Sequences and Series, Straight Lines, Conic Sections, Introduction to Three Dimensional Geometry, Limits and Derivatives, Mathematical Reasoning, Statistics, Probability',
            'Physics'          => 'Physical World and Measurement, Kinematics, Laws of Motion, Work Energy and Power, Motion of System of Particles, Gravitation, Properties of Bulk Matter, Thermodynamics, Behaviour of Perfect Gas and Kinetic Theory, Oscillations, Waves',
            'Chemistry'        => 'Some Basic Concepts of Chemistry, Structure of Atom, Classification of Elements and Periodicity, Chemical Bonding and Molecular Structure, States of Matter, Thermodynamics, Equilibrium, Redox Reactions, Hydrogen, s-Block Elements, p-Block Elements, Organic Chemistry Basics, Hydrocarbons, Environmental Chemistry',
            'Biology'          => 'The Living World, Biological Classification, Plant Kingdom, Animal Kingdom, Morphology of Flowering Plants, Anatomy of Flowering Plants, Structural Organisation in Animals, Cell Unit of Life, Biomolecules, Cell Cycle and Division, Transport in Plants, Mineral Nutrition, Photosynthesis, Respiration in Plants, Plant Growth and Development, Digestion and Absorption, Breathing and Exchange of Gases, Body Fluids and Circulation, Excretory Products, Locomotion and Movement, Neural Control, Chemical Coordination',
            'History'          => 'Bricks Beads and Bones, Kings Farmers and Towns, Kinship Caste and Class, Thinkers Beliefs and Buildings, Through the Eyes of Travellers, Bhakti Sufi Traditions, An Imperial Capital Vijayanagara, Peasants Zamindars and the State, Kings and Chronicles, Colonialism and the Countryside, Rebels and the Raj, Colonial Cities, Mahatma Gandhi and the Nationalist Movement, Understanding Partition, Framing the Constitution',
            'Economics'        => 'Introduction to Statistics, Collection of Data, Organisation of Data, Presentation of Data, Measures of Central Tendency, Measures of Dispersion, Correlation, Index Numbers, Introduction to Microeconomics, Consumer Behaviour, Producer Behaviour, Forms of Market',
            'Computer Science' => 'Computer Fundamentals, Programming with Python Basics, Data Types Variables Operators, Control Structures, Functions, Lists Tuples Dictionaries, File Handling, Introduction to MySQL',
        ],
        '12' => [
            'Mathematics'      => 'Relations and Functions, Inverse Trigonometric Functions, Matrices, Determinants, Continuity and Differentiability, Application of Derivatives, Integrals, Application of Integrals, Differential Equations, Vector Algebra, Three Dimensional Geometry, Linear Programming, Probability',
            'Physics'          => 'Electric Charges and Fields, Electrostatic Potential and Capacitance, Current Electricity, Moving Charges and Magnetism, Magnetism and Matter, Electromagnetic Induction, Alternating Current, Electromagnetic Waves, Ray Optics, Wave Optics, Dual Nature of Radiation, Atoms, Nuclei, Semiconductor Devices, Communication Systems',
            'Chemistry'        => 'The Solid State, Solutions, Electrochemistry, Chemical Kinetics, Surface Chemistry, General Principles of Isolation of Elements, p-Block Elements, d and f Block Elements, Coordination Compounds, Haloalkanes and Haloarenes, Alcohols Phenols and Ethers, Aldehydes Ketones Carboxylic Acids, Amines, Biomolecules, Polymers, Chemistry in Everyday Life',
            'Biology'          => 'Reproduction in Organisms, Sexual Reproduction in Flowering Plants, Human Reproduction, Reproductive Health, Principles of Inheritance and Variation, Molecular Basis of Inheritance, Evolution, Human Health and Disease, Strategies for Enhancement in Food Production, Microbes in Human Welfare, Biotechnology Principles and Processes, Biotechnology and its Applications, Organisms and Populations, Ecosystem, Biodiversity and Conservation, Environmental Issues',
            'History'          => 'Bricks Beads and Bones Harappan Civilisation, Kings Farmers and Towns, Kinship Caste and Class, Thinkers Beliefs and Buildings, Travellers Accounts, Bhakti Sufi Traditions, Vijayanagara Empire, Peasants Zamindars and the State, Mughal Court Chronicles, Colonial Countryside, Revolt of 1857, Colonial Cities, Gandhian Nationalism, Partition, Indian Constitution',
            'Economics'        => 'Macroeconomics: National Income, Money and Banking, Determination of Income and Employment, Government Budget, Balance of Payments. Microeconomics: Introduction, Consumer Behaviour, Production and Costs, Revenue, Forms of Market, Price Determination, Factors of Production',
            'Computer Science' => 'Python Advanced: OOP Recursion Sorting Searching, Data Structures Stacks Queues Linked Lists, Database Management MySQL, Networking Internet Basics, Cybersecurity Basics, Boolean Algebra Logic Gates',
            'Political Science' => 'Cold War Era, End of Bipolarity, Contemporary Centres of Power, Contemporary South Asia, International Organisations, Security in Contemporary World, Environment and Natural Resources, Globalisation',
        ],
        'UG-1' => [
            'Mathematics'      => 'Calculus, Linear Algebra, Differential Equations, Discrete Mathematics, Statistics and Probability',
            'Physics'          => 'Classical Mechanics, Electrodynamics, Optics, Thermal Physics, Quantum Mechanics Basics',
            'Chemistry'        => 'Physical Chemistry, Organic Chemistry, Inorganic Chemistry, Analytical Chemistry',
            'Biology'          => 'Cell Biology, Genetics, Biochemistry, Microbiology, Ecology',
            'Computer Science' => 'Data Structures, Algorithms, Object Oriented Programming, Database Management, Operating Systems',
            'Economics'        => 'Microeconomics, Macroeconomics, Statistical Methods, Indian Economy',
        ],
    ];

    public function __construct()
    {
        $this->apiKey      = config('services.groq.api_key');
        $this->model       = config('services.groq.model', 'llama-3.3-70b-versatile');
        $this->visionModel = config('services.groq.vision_model', 'meta-llama/llama-4-scout-17b-16e-instruct');

        if (empty($this->apiKey)) {
            throw new \RuntimeException('GROQ_API_KEY is not set in .env');
        }
    }

    // ── Difficulty descriptions (used in prompts) ────────────────────────────
    private function difficultyGuide(string $difficulty): string
    {
        return match ($difficulty) {
            'beginner'     => 'BEGINNER level: Very simple, direct recall questions. Use basic vocabulary. Questions like "What is...?", "Which of the following...?". Suitable for first-time learners. Avoid complex reasoning.',
            'intermediate' => 'INTERMEDIATE level: Moderate complexity. Mix of recall and application. Questions test understanding, not just memorization. Some "why" and "how" questions.',
            'advanced'     => 'ADVANCED level: High-order thinking. Analysis, synthesis, evaluation. Multi-step reasoning, edge cases, exceptions, comparisons between concepts. Competitive exam style (JEE/NEET/UPSC level).',
            default        => 'INTERMEDIATE level: Moderate complexity.',
        };
    }

    // ── Build strict no-duplicate MCQ prompt ────────────────────────────────
    private function buildPrompt(string $context, int $count, string $difficulty, array $usedQuestions = []): string
    {
        $diffGuide = $this->difficultyGuide($difficulty);
        $avoidStr  = '';

        if (!empty($usedQuestions)) {
            $avoidList = implode("\n- ", array_slice($usedQuestions, 0, 20));
            $avoidStr  = "\n\nCRITICAL - DO NOT repeat these question topics (already used):\n- {$avoidList}";
        }

        return <<<PROMPT
You are an expert educational quiz maker for Indian students.

{$diffGuide}

Topic/Content: {$context}{$avoidStr}

Generate EXACTLY {$count} UNIQUE MCQ questions. Each question MUST be completely different - different facts, different concepts, different aspects.

STRICT RULES:
1. Return ONLY a valid JSON array — NO markdown, NO extra text, NO explanation outside JSON
2. Each question: exactly 4 options (A, B, C, D)
3. "answer" field = zero-based index (0=A, 1=B, 2=C, 3=D)
4. Vary correct answers: spread across A, B, C, D positions across questions
5. Explanation must be 1-2 clear, factual sentences
6. For BEGINNER: use simple words, avoid jargon
7. For ADVANCED: include tricky distractors, edge cases
8. All {$count} questions must cover DIFFERENT sub-topics/concepts
9. No two questions should test the same fact

Output format (JSON array only):
[
  {
    "question": "Question text here?",
    "options": ["Option A", "Option B", "Option C", "Option D"],
    "answer": 0,
    "explanation": "Brief explanation of why this is correct.",
    "topic": "Sub-topic name"
  }
]
PROMPT;
    }

    // ── Parse & validate AI JSON response ───────────────────────────────────
    private function parseQuestions(string $raw, string $source): array
    {
        // Strip markdown code blocks
        $cleaned = preg_replace('/```(?:json)?/i', '', $raw);
        $cleaned = trim($cleaned);

        // Extract JSON array if wrapped in text
        if (!str_starts_with($cleaned, '[')) {
            if (preg_match('/\[[\s\S]*\]/m', $cleaned, $matches)) {
                $cleaned = $matches[0];
            }
        }

        $data = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            Log::error('AIQuizService parse error', ['raw' => substr($raw, 0, 500)]);
            throw new \Exception('AI returned invalid JSON. Please try again.');
        }

        // Handle wrapped format: {"questions": [...]}
        if (isset($data['questions']) && is_array($data['questions'])) {
            $data = $data['questions'];
        }

        // Validate and deduplicate
        $seen   = [];
        $result = [];

        foreach ($data as $q) {
            if (!isset($q['question'], $q['options'], $q['answer'])) continue;
            if (!is_array($q['options']) || count($q['options']) < 4)  continue;
            if (!is_numeric($q['answer']) || $q['answer'] < 0 || $q['answer'] > 3) continue;

            // Dedup by normalized question text
            $key = strtolower(preg_replace('/\W+/', '', substr($q['question'], 0, 60)));
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $result[] = [
                'question'    => trim($q['question']),
                'options'     => array_values(array_slice($q['options'], 0, 4)),
                'answer'      => (int) $q['answer'],
                'explanation' => trim($q['explanation'] ?? ''),
                'topic'       => trim($q['topic'] ?? ''),
                'source'      => $source,
            ];
        }

        if (empty($result)) {
            throw new \Exception('No valid questions generated. Please try again.');
        }

        return $result;
    }

    // ── Core Groq API call ───────────────────────────────────────────────────
    private function callGroq(array $messages, float $temperature = 0.7, int $maxTokens = 4096, bool $vision = false): string
    {
        $model = $vision ? $this->visionModel : $this->model;

        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->withoutVerifying()
            ->post($this->baseUrl, [
                'model'       => $model,
                'temperature' => $temperature,
                'max_tokens'  => $maxTokens,
                'messages'    => $messages,
            ]);

        if (!$response->successful()) {
            Log::error('Groq API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception('AI service error: ' . ($response->json('error.message') ?? 'Unknown error'));
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    // ── 1. Generate from Topic ───────────────────────────────────────────────
    public function generateFromTopic(string $topic, string $subject, int $count, string $difficulty): array
    {
        $context = "Subject: {$subject}\nTopic: {$topic}\nGenerate questions specifically about: {$topic}";

        // Get previously used question fingerprints for this user/topic combo
        $cacheKey = 'quiz_used_' . auth()->id() . '_' . md5($topic);
        $used     = Cache::get($cacheKey, []);

        $messages = [
            ['role' => 'system', 'content' => 'You are an expert educational quiz generator for Indian students. Always return valid JSON arrays only.'],
            ['role' => 'user',   'content' => $this->buildPrompt($context, $count, $difficulty, $used)],
        ];

        $raw       = $this->callGroq($messages, 0.8); // slightly higher temp for diversity
        $questions = $this->parseQuestions($raw, 'topic');

        // Cache used question topics to avoid future duplicates
        $newUsed = array_merge($used, array_column($questions, 'question'));
        Cache::put($cacheKey, array_slice($newUsed, -50), now()->addDays(7));

        return $questions;
    }

    // ── 2. Generate from PDF text ────────────────────────────────────────────
    public function generateFromPdfText(string $text, int $count, string $difficulty): array
    {
        // Clean and chunk text
        $text    = preg_replace('/\s+/', ' ', $text);
        $context = "Generate questions based ONLY on this document content:\n\n" . substr($text, 0, 5000);

        $messages = [
            ['role' => 'system', 'content' => 'You are an expert quiz generator. Create questions ONLY from the provided document. Return valid JSON array only.'],
            ['role' => 'user',   'content' => $this->buildPrompt($context, $count, $difficulty)],
        ];

        $raw = $this->callGroq($messages, 0.6); // lower temp = closer to source
        return $this->parseQuestions($raw, 'pdf');
    }

    // ── 3. Generate from Image ───────────────────────────────────────────────
    public function generateFromImage(string $base64, string $mimeType, int $count): array
    {
        $diffGuide = $this->difficultyGuide('intermediate');

        $prompt = <<<PROMPT
Analyze this educational diagram carefully. Identify what it shows.

If it is NOT an educational diagram (not biology/physics/chemistry/geography/science), respond with:
{"detectedLabel":"non_educational","questions":[]}

If it IS educational, generate EXACTLY {$count} MCQ questions about what is shown.
{$diffGuide}

Return ONLY valid JSON in this exact format:
{
  "detectedLabel": "Name of what the diagram shows (e.g. 'Human Heart', 'Electric Circuit', 'Water Cycle')",
  "questions": [
    {
      "question": "Question text?",
      "options": ["Option A", "Option B", "Option C", "Option D"],
      "answer": 0,
      "explanation": "Brief explanation.",
      "topic": "Sub-topic"
    }
  ]
}
PROMPT;

        $messages = [[
            'role'    => 'user',
            'content' => [
                ['type' => 'text',      'text'      => str_replace('{$count}', $count, $prompt)],
                ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$base64}"]],
            ],
        ]];

        $raw    = $this->callGroq($messages, 0.5, 4096, true);
        $cleaned = preg_replace('/```(?:json)?/i', '', $raw);

        // Extract JSON object
        if (preg_match('/\{[\s\S]*\}/m', trim($cleaned), $matches)) {
            $cleaned = $matches[0];
        }

        $parsed = json_decode($cleaned, true);

        if (!$parsed) {
            throw new \Exception('AI returned invalid response for image.');
        }

        if (($parsed['detectedLabel'] ?? '') === 'non_educational') {
            throw new \Exception('Not an educational diagram. Please upload biology/physics/chemistry/geography diagrams only.');
        }

        $questions = array_map(function ($q) {
            return array_merge($q, ['source' => 'image']);
        }, $parsed['questions'] ?? []);

        if (empty($questions)) {
            throw new \Exception('Could not generate questions from this image.');
        }

        return ['questions' => $questions, 'label' => $parsed['detectedLabel'] ?? 'Educational Diagram'];
    }

    // ── 4. Generate Standard (class + subject) — FIXED ──────────────────────
    public function generateStandard(string $subject, string $class, int $count, string $difficulty): array
    {
        // Get curriculum for this class/subject
        $syllabusMap = $this->curriculumMap[$class] ?? ($this->curriculumMap['UG-1'] ?? []);

        // Normalize subject name (handle "Physics" matching "Physics" in map)
        $syllabus    = null;
        foreach ($syllabusMap as $subj => $topics) {
            if (stripos($subj, $subject) !== false || stripos($subject, $subj) !== false) {
                $syllabus = $topics;
                break;
            }
        }

        if (!$syllabus) {
            // Fallback: generate without syllabus constraint
            $syllabus = "Class {$class} {$subject} curriculum";
        }

        $diffGuide = $this->difficultyGuide($difficulty);

        // Pick random subset of topics from syllabus for this call to ensure variety
        $topicsList  = explode(',', $syllabus);
        shuffle($topicsList);
        $selectedTopics = implode(', ', array_slice($topicsList, 0, min(8, count($topicsList))));

        $context = <<<CTX
Indian School Curriculum — Class {$class} {$subject}

Syllabus chapters/topics available: {$selectedTopics}

IMPORTANT:
- Generate questions that are APPROPRIATE for Class {$class} students
- Questions must be from the actual NCERT/CBSE syllabus for Class {$class} {$subject}
- DO NOT generate questions too easy (primary level) or too hard (university level)
- Cover DIFFERENT topics from the syllabus
CTX;

        // Track used questions per class+subject to avoid duplicates across sessions
        $cacheKey = 'quiz_std_' . auth()->id() . '_' . md5($class . $subject);
        $used     = Cache::get($cacheKey, []);

        $messages = [
            ['role' => 'system', 'content' => "You are an expert Indian school educator. Generate curriculum-accurate MCQ questions for Class {$class} {$subject}. {$diffGuide} Return valid JSON only."],
            ['role' => 'user',   'content' => $this->buildPrompt($context, $count, $difficulty, $used)],
        ];

        $raw       = $this->callGroq($messages, 0.75, 4096);
        $questions = $this->parseQuestions($raw, 'standard');

        // Cache to avoid future duplicates
        $newUsed = array_merge($used, array_column($questions, 'question'));
        Cache::put($cacheKey, array_slice($newUsed, -100), now()->addDays(14));

        return $questions;
    }

    // ── 5. Save Manual MCQ (no AI needed) ────────────────────────────────────
    // (this is handled in the controller, but keeping for consistency)

    // ── 6. Tutor Chat — ChatGPT style with subject context ───────────────────
    public function tutorChat(string $message, string $subject, array $history): string
    {
        // Step 1: Safety/relevance check
        $checkResponse = $this->callGroq([
            [
                'role'    => 'system',
                'content' => 'You are a classifier. Reply ONLY one word: "allowed" if the message is academic, study, homework, exam, career, general knowledge, science, history, math, coding, language related. "blocked" only for harmful, adult, illegal, or completely off-topic chat. When in doubt, reply "allowed".',
            ],
            ['role' => 'user', 'content' => $message],
        ], 0.0, 10);

        if (trim(strtolower($checkResponse)) === 'blocked') {
            return "⚠️ I'm your **Study Tutor** — I can help with any academic subject: Math, Science, History, Languages, Coding, and more.\n\nPlease ask a study-related question! 📚";
        }

        // Step 2: Subject-specific teaching guide
        $isGeneral    = in_array(strtolower($subject), ['general', '']);

        $subjectGuide = match (strtolower($subject)) {
            'mathematics', 'math' => "
- ALWAYS solve step-by-step. Never skip steps or jump to the answer.
- Format: **Formula** → **Substitution** → **Calculation** → **✅ Answer**
- Point out common mistakes students make for this type of problem.
- For word problems: identify given values → set up equation → solve.
- End with: 'Want a similar practice problem? 📝'",

            'physics' => "
- Start with a real-life example BEFORE stating the theory.
- State the formula clearly, then explain each variable (what it means, units).
- For numericals: **Given** | **Find** | **Formula** | **Solution** | **Unit check**
- Use ASCII diagrams when helpful (force diagrams, ray diagrams etc.)
- Connect new concepts to previously studied ones.",

            'chemistry' => "
- Balance equations step-by-step, explaining each step.
- Explain WHY reactions happen (bonding, electron transfer, oxidation states).
- For organic: show structural logic and functional group behavior.
- Real-world connections (medicines, materials, food chemistry).
- Safety notes for any lab-related concepts.",

            'biology' => "
- Use scientific names with common names in brackets on first mention.
- Explain processes as a clear numbered sequence: Step 1 → Step 2 → ...
- Relate to the human body or visible nature wherever possible.
- For diagrams: describe what each part does, not just labels.
- Offer mnemonics for memorization when helpful.",

            'computer science' => "
- Show working code examples in Python by default (state if switching language).
- Explain the LOGIC first, then the code: 'We need to... so we write...'
- For algorithms: trace through with a small example input/output.
- Highlight time and space complexity for DSA topics.
- For debugging: explain WHY the error occurs, not just the fix.",

            'history' => "
- Structure: **Date** | **Key Figures** | **Cause → Event → Effect**
- Connect events to broader context: 'This happened because... which led to...'
- Use timelines or comparisons across eras when helpful.
- Distinguish between historical fact and interpretation.
- Memory hooks: acronyms, stories, timelines.",

            'geography' => "
- Always ground answers with location (continent, region, country).
- Explain: physical feature → climate → human activity chain.
- Use comparisons when helpful: 'Similar to X, this region also...'
- For map-based questions: describe spatial relationships clearly.",

            'english' => "
- Grammar: **Rule** → **Wrong example** ✗ → **Correct example** ✓
- Writing: Give structure outline first, then content tips.
- Literature: Context → Theme → Literary devices → Student's analysis.
- Vocabulary: word origin + example sentence + usage context.",

            'economics' => "
- Use real India/global examples (RBI, inflation, GDP, budget etc.)
- For graphs: explain the axes, then the curve/shift, then the implication.
- Connect micro and macro concepts when relevant.
- Numericals: **Formula** → **Substitution** → **Result** → **Interpretation**",

            default => "
- Explain clearly with relevant, relatable examples.
- Use structured responses with clear headings for complex topics.
- Adapt explanation depth to the complexity of the question.",
        };

        $subjectContext = $isGeneral
            ? "The student has not selected a specific subject — answer any academic question clearly, accurately, and helpfully."
            : "The student is studying **{$subject}**. Keep focus on {$subject} concepts but help with related topics if needed.";

        $systemPrompt = <<<SYS
You are QuizMaster AI Tutor — a brilliant, warm, and highly effective personal teacher for Indian students (Class 6 to College/UG level).
 
{$subjectContext}
 
TEACHING APPROACH:
{$subjectGuide}
 
CORE RULES (follow every response):
1. Respond like a great teacher in a 1-on-1 session — not like a generic chatbot.
2. Concept explanations: 1-2 line simple summary first, then go deeper.
3. Problems/numericals: Always show full METHOD and working. Never just give the answer.
4. Use **bold** for key terms, formulas, and critical facts.
5. Use numbered steps for processes, bullet points for lists.
6. Emojis for engagement: 📌 key points · 💡 tips · ✅ correct answers · ⚠️ common mistakes.
7. If student seems confused (short reply, "I don't get it", "huh?") — ask ONE specific clarifying question before re-explaining.
8. Build on previous messages in this conversation — reference earlier concepts when relevant.
9. Keep responses under 450 words unless a complex problem genuinely needs more.
10. NEVER be dismissive. Never say "this is easy/simple/basic". Every question deserves full effort.
11. End complex explanations with: "Does this make sense? Want a practice problem? 📝"
SYS;

        // Build messages with last 12 turns of history for full context
        $msgs = [['role' => 'system', 'content' => $systemPrompt]];

        foreach (array_slice($history, -12) as $h) {
            $role = ($h['role'] ?? '') === 'user' ? 'user' : 'assistant';
            $text = trim($h['content'] ?? $h['text'] ?? '');
            if ($text) {
                $msgs[] = ['role' => $role, 'content' => $text];
            }
        }

        $msgs[] = ['role' => 'user', 'content' => $message];

        return $this->callGroq($msgs, 0.65, 900);
    }

    public function generateFromChat(array $history, string $subject, int $count = 5, string $difficulty = 'mixed'): array
    {
        // ✅ STEP 1: SAFE CHAT BUILD
        $chatText = collect($history)
            ->map(function ($m) {

                $content = $m['content'] ?? '';

                // 🔥 FORCE STRING AGAIN (DOUBLE SAFETY)
                if (!is_string($content)) {
                    $content = json_encode($content, JSON_UNESCAPED_UNICODE);
                }

                $role = ($m['role'] ?? 'user') === 'user' ? 'Student' : 'Tutor';

                return $role . ': ' . $content;
            })
            ->implode("\n\n");

        // Trim long chat
        if (strlen($chatText) > 3500) {
            $chatText = substr($chatText, -3500);
        }

        // ✅ STEP 2: CACHE SAFE
        $cacheKey = 'quiz_chat_' . auth()->id() . '_' . strtolower($subject) . '_' . md5($chatText);
        $used = Cache::get($cacheKey, []);

        if (!is_array($used)) {
            $used = [];
        }

        // 🔥 FIX ARRAY ERROR HERE
        $usedText = implode("\n", array_map(function ($u) {
            return is_string($u) ? $u : json_encode($u);
        }, $used));

        // ✅ STEP 3: PROMPT
        $prompt = <<<PROMPT
Generate EXACTLY {$count} MCQs from this conversation.

STRICT:
- ONLY MCQs
- NO theory
- NO extra text

FORMAT:
{"questions":[
{"question":"...","options":["A","B","C","D"],"answer":0,"explanation":"..."}
]}

Rules:
- answer = 0–3
- 4 options only
- 1 correct
- explanation = 1 short line

Avoid repeating:
{$usedText}

SUBJECT: {$subject}

CHAT:
{$chatText}
PROMPT;

        // ✅ STEP 4: CALL AI
        $raw = $this->callGroq([
            ['role' => 'system', 'content' => 'Return ONLY JSON'],
            ['role' => 'user', 'content' => $prompt],
        ], 0.5, 1000);

        // ✅ STEP 5: CLEAN RESPONSE
        $cleaned = trim($raw);
        $cleaned = preg_replace('/^```(?:json)?/i', '', $cleaned);
        $cleaned = preg_replace('/```$/', '', $cleaned);

        if (!str_starts_with($cleaned, '{')) {
            preg_match('/\{[\s\S]*\}/', $cleaned, $m);
            $cleaned = $m[0] ?? '';
        }

        $parsed = json_decode($cleaned, true);

        if (!$parsed || empty($parsed['questions'])) {
            return [];
        }

        // ✅ STEP 6: VALIDATE OUTPUT
        $questions = collect($parsed['questions'])
            ->filter(function ($q) {
                return isset($q['question'], $q['options'], $q['answer'])
                    && is_array($q['options'])
                    && count($q['options']) === 4
                    && is_numeric($q['answer'])
                    && $q['answer'] >= 0
                    && $q['answer'] <= 3;
            })
            ->map(function ($q) use ($subject) { // ✅ FIX HERE

                return [
                    'question'    => is_string($q['question']) ? $q['question'] : json_encode($q['question']),
                    'options'     => array_map(fn($o) => is_string($o) ? $o : json_encode($o), $q['options']),
                    'answer'      => (int) $q['answer'],
                    'explanation' => is_string($q['explanation'] ?? '') ? $q['explanation'] : json_encode($q['explanation'] ?? ''),
                    'source'      => 'chat',
                    'topic'       => $subject, // ✅ NOW WORKS
                ];
            })
            ->values()
            ->toArray();

        // ✅ STEP 7: UPDATE CACHE
        $newUsed = array_merge($used, array_column($questions, 'question'));
        Cache::put($cacheKey, array_slice($newUsed, -50), now()->addDays(7));

        return $questions;
    }

    // ── 7. Performance Suggestions ────────────────────────────────────────────
    public function suggestions(array $weakSubjects): string
    {
        $data   = empty($weakSubjects) ? ['Performing well in all subjects tested so far'] : $weakSubjects;
        $prompt = "Student performance analysis:\n" . implode("\n", $data) . "\n\nGive a short (2-3 sentences), specific, encouraging study suggestion. Mention the exact weak subjects. Use friendly tone and 1-2 relevant emojis. Be actionable.";

        return $this->callGroq([
            ['role' => 'system', 'content' => 'You are an encouraging academic coach for Indian students.'],
            ['role' => 'user',   'content' => $prompt],
        ], 0.8, 200);
    }
}
