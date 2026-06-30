<?php
// DO NOT ALTER WITHOUT APPROVAL — SF9 Module
// Last modified: 2026-06-29
// Hardcoded SF9 Non-Graded indicator list
// Source: DepEd SF9 Non-Graded SPED Report Card Form (CARD raw)
// Used by PDSPModel::seedQuarterlyRatings() to populate student_quarterly_ratings

return [

    'Daily Living Skills' => [
        // Self-Feeding
        'Holds and uses spoon/fork correctly',
        'Drinks from a glass without spilling',
        'Eats using hands with minimal mess',
        'Opens food containers independently',
        // Toileting
        'Indicates need to use the toilet',
        'Goes to the toilet independently',
        'Flushes the toilet after use',
        'Washes hands after toileting',
        // Dressing
        'Puts on and removes clothing independently',
        'Buttons and unbuttons shirt',
        'Zips and unzips clothing',
        'Wears shoes and socks correctly',
        // Grooming and Hygiene
        'Brushes teeth with minimal supervision',
        'Washes and dries hands properly',
        'Combs/brushes hair independently',
        'Maintains personal cleanliness',
    ],

    'Socio-Emotional' => [
        'Shows awareness of own feelings and emotions',
        'Interacts appropriately with peers and adults',
        'Takes turns and shares materials',
        'Expresses needs and wants appropriately',
        'Follows classroom rules and routines',
        'Shows appropriate emotional responses',
        'Develops and maintains peer relationships',
        'Demonstrates self-control in social situations',
        'Shows empathy toward others',
        'Seeks help from adults when needed',
    ],

    'Language Development' => [
        // Listening
        'Listens attentively during instruction',
        'Follows one-step verbal directions',
        'Follows two-step verbal directions',
        'Identifies objects/pictures when named',
        // Speaking
        'Communicates basic needs verbally or through AAC',
        'Uses simple sentences to express ideas',
        'Participates in simple conversations',
        'Answers simple questions appropriately',
        // Reading
        'Recognizes own name in print',
        'Identifies letters of the alphabet',
        'Matches letters to sounds (phonics)',
        'Reads simple words and short phrases',
        // Writing
        'Holds pencil/crayon with correct grip',
        'Copies simple shapes and lines',
        'Writes own name legibly',
        'Writes simple words from dictation',
    ],

    'Psychomotor' => [
        // Basic Movement
        'Walks with balance and coordination',
        'Runs with control and coordination',
        'Climbs stairs using alternating feet',
        // Gross Motor
        'Catches and throws a ball',
        'Jumps on two feet and hops on one foot',
        'Participates in physical activities with peers',
        // Fine Motor
        'Cuts along a straight and curved line with scissors',
        'Strings beads and manipulates small objects',
        'Completes puzzles with multiple pieces',
        'Colors within boundaries',
        // Perceptual Motor
        'Copies simple geometric shapes',
        'Demonstrates eye-hand coordination in tasks',
        'Identifies left and right body sides',
        'Tracks moving objects with eyes',
    ],

    'Cognitive' => [
        'Identifies and matches colors',
        'Identifies and matches basic shapes',
        'Sorts and classifies objects by attributes',
        'Counts objects up to 10',
        'Identifies numbers 1–10',
        'Understands concepts of more/less, big/small',
        'Completes simple cause-and-effect tasks',
        'Demonstrates problem-solving in daily tasks',
        'Identifies body parts correctly',
        'Understands time concepts (before/after, today/tomorrow)',
    ],

    'Aesthetic/Creative' => [
        'Participates in music and rhythm activities',
        'Expresses appreciation for music and sound',
        'Moves body in response to music/rhythm',
        'Engages in creative art activities (drawing, painting)',
        'Expresses creativity through play',
        'Demonstrates appreciation for cultural arts',
        'Watches and enjoys dramatic play/media',
        'Communicates feelings through facial expressions',
    ],

    'Behavioral Development' => [
        'Uses appropriate verbal communication for social interaction',
        'Learns how to speak in a lower tone',
        'Familiarizes with and takes relocated direction',
        'Follows classroom/court instructions',
        'Performs simple tasks (e.g., throwing trash in the garbage)',
        'Puts body materials and used items in proper place',
        'Follows teacher\'s commands/inspection',
        'Participates well in the lesson executed by the teacher',
        'Responds to questions and activities given to him/her',
        'Attends to task without getting out from the chair',
        'Watches/listens to videos/music for 5 minutes or more',
        'Responds positively to behavior management procedures',
        'Eliminates inappropriate and aggressive behavior during session',
        'Reduces separation anxiety during the session',
        'Plays with other children',
        'Takes turn in game activities',
        'Knows how to wait when playing games',
        'Shares things/food without teacher prompt',
        'Sits for 30 minutes to one hour',
        'Develops longer attention span to complete the task',
        'Completes task on hand',
    ],

    'Orientation and Mobility' => [
        'Tells the difference between places (from/to)',
        'Positions body parts on the right/left sides',
        'Tells the spatial relations of objects between tables',
        'Follows directions given to find objects',
        'Uses position of classroom objects as reference to self',
        'Performs bilateral arm and leg movements simultaneously with coordination',
        'Shows the body with balance and rhythm',
        'Identifies landmarks as clues',
        'Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',
        'Uses parallel walk as guide',
        'Works independently',
        'Squeezes soft rubber ball of convenient size',
        'Expresses appreciation for the dance that they learned',
    ],

];
