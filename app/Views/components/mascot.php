<?php
/**
 * SignED Mascot Component - "Kami" the FSL Learning Buddy
 * Renders an inline SVG mascot based on a requested state: 'waving', 'pointing', 'cheering', or 'happy'
 */

$state = $mascotState ?? 'happy';
$cssClass = 'mascot-svg mascot-' . $state;
?>

<div class="mascot-container">
    <svg class="<?php echo $cssClass; ?>" viewBox="0 0 200 200" width="160" height="160" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <!-- Body Gradient -->
            <radialGradient id="mascotGrad" cx="45%" cy="35%" r="60%">
                <stop offset="0%" stop-color="#FFEC94" />
                <stop offset="60%" stop-color="#FFD93D" />
                <stop offset="100%" stop-color="#FF9A3D" />
            </radialGradient>
            
            <!-- Cheek Shadow -->
            <radialGradient id="cheekGrad" cx="50%" cy="50%" r="50%">
                <stop offset="0%" stop-color="#FF6B9D" stop-opacity="0.6" />
                <stop offset="100%" stop-color="#FF6B9D" stop-opacity="0" />
            </radialGradient>
            
            <!-- Confetti Gradients -->
            <linearGradient id="conf1" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="#FF6B9D" /><stop offset="100%" stop-color="#FF8E53" />
            </linearGradient>
            <linearGradient id="conf2" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="#4D96FF" /><stop offset="100%" stop-color="#6BCB77" />
            </linearGradient>
            <linearGradient id="conf3" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="#9D84FF" /><stop offset="100%" stop-color="#FFD93D" />
            </linearGradient>
        </defs>

        <!-- CELEBRATION EFFECT (Confetti - shown in cheering state) -->
        <?php if ($state === 'cheering'): ?>
        <g class="confetti-group">
            <!-- Confetti particles around the mascot -->
            <path class="confetti c1" d="M 30,30 L 38,25 L 35,35 Z" fill="url(#conf1)" />
            <circle class="confetti c2" cx="170" cy="40" r="6" fill="url(#conf2)" />
            <rect class="confetti c3" x="25" y="140" width="8" height="12" rx="2" fill="url(#conf3)" transform="rotate(45 29 146)" />
            <path class="confetti c4" d="M 165,130 L 175,135 L 170,145 Z" fill="url(#conf1)" />
            <circle class="confetti c5" cx="100" cy="15" r="5" fill="url(#conf3)" />
            <rect class="confetti c6" x="160" y="85" width="10" height="6" rx="1" fill="url(#conf2)" transform="rotate(-15 165 88)" />
            <path class="confetti c7" d="M 40,80 L 46,88 L 36,92 Z" fill="url(#conf3)" />
        </g>
        <?php endif; ?>

        <!-- SHADOW UNDER MASCOT -->
        <ellipse cx="100" cy="178" rx="55" ry="8" fill="#e2e8f0" class="mascot-shadow" />

        <!-- MAIN BODY -->
        <g class="mascot-body-group">
            <!-- Main round bubble body -->
            <circle cx="100" cy="105" r="62" fill="url(#mascotGrad)" stroke="#243042" stroke-width="7" />

            <!-- Cute little feet -->
            <g class="mascot-feet">
                <!-- Left Foot -->
                <path d="M 60,160 C 50,160 45,170 55,176 C 65,182 80,175 75,164 C 75,161 65,160 60,160 Z" fill="#FF9A3D" stroke="#243042" stroke-width="6" stroke-linejoin="round" />
                <!-- Right Foot -->
                <path d="M 140,160 C 150,160 155,170 145,176 C 135,182 120,175 125,164 C 125,161 135,160 140,160 Z" fill="#FF9A3D" stroke="#243042" stroke-width="6" stroke-linejoin="round" />
            </g>

            <!-- FACE ELEMENTS -->
            <!-- Rosy Cheeks -->
            <circle cx="62" cy="118" r="10" fill="url(#cheekGrad)" />
            <circle cx="138" cy="118" r="10" fill="url(#cheekGrad)" />

            <!-- Eyes -->
            <g class="mascot-eyes">
                <!-- Left Eye -->
                <ellipse cx="76" cy="98" rx="8" ry="12" fill="#243042" />
                <circle cx="73" cy="93" r="3.5" fill="#FFFFFF" />
                <circle cx="78" cy="102" r="1.5" fill="#FFFFFF" />
                <!-- Right Eye -->
                <ellipse cx="124" cy="98" rx="8" ry="12" fill="#243042" />
                <circle cx="121" cy="93" r="3.5" fill="#FFFFFF" />
                <circle cx="126" cy="102" r="1.5" fill="#FFFFFF" />
            </g>
            
            <!-- Happy Mouth -->
            <?php if ($state === 'cheering'): ?>
                <!-- Huge open mouth celebrating -->
                <path d="M 88,116 C 88,132 112,132 112,116 Z" fill="#FF6B6B" stroke="#243042" stroke-width="5" stroke-linejoin="round" />
                <path d="M 94,124 C 98,128 102,128 106,124 Z" fill="#FFFFFF" />
            <?php else: ?>
                <!-- Cute curved smiling mouth -->
                <path d="M 90,114 Q 100,126 110,114" fill="none" stroke="#243042" stroke-width="6" stroke-linecap="round" />
            <?php endif; ?>
        </g>

        <!-- HANDS & ACTIONS -->
        <!-- LEFT HAND (Always resting happily, unless cheering) -->
        <?php if ($state === 'cheering'): ?>
            <!-- Left Hand Raised cheering -->
            <g class="mascot-hand-left cheering-hand">
                <path d="M 45,95 C 32,80 20,70 12,80 C 4,90 20,105 35,110 C 38,105 42,100 45,95 Z" fill="#FFFFFF" stroke="#243042" stroke-width="6" stroke-linejoin="round" />
                <circle cx="15" cy="78" r="5" fill="#FFFFFF" stroke="#243042" stroke-width="4" />
            </g>
        <?php else: ?>
            <!-- Left Hand resting on hip/body -->
            <g class="mascot-hand-left">
                <path d="M 42,112 C 34,115 28,122 34,128 C 40,134 46,128 48,122" fill="#FFFFFF" stroke="#243042" stroke-width="6" stroke-linecap="round" />
            </g>
        <?php endif; ?>

        <!-- RIGHT HAND (Action Hand) -->
        <?php if ($state === 'waving'): ?>
            <!-- Waving Right Hand -->
            <g class="mascot-hand-right waving-hand">
                <!-- Arm -->
                <path d="M 155,100 C 168,85 182,75 188,85 C 194,95 178,110 162,115" fill="none" stroke="#243042" stroke-width="6" stroke-linecap="round" />
                <!-- Glove Hand waving -->
                <path d="M 175,76 C 172,70 166,66 164,72 C 162,78 168,84 174,86 C 176,82 178,80 175,76 Z" fill="#FFFFFF" stroke="#243042" stroke-width="5" />
                <path d="M 183,78 C 180,72 174,70 172,76 C 170,82 176,86 182,88 Z" fill="#FFFFFF" stroke="#243042" stroke-width="5" />
                <path d="M 190,83 C 188,77 182,76 180,82 C 178,88 184,90 190,92 Z" fill="#FFFFFF" stroke="#243042" stroke-width="5" />
                <!-- Palm base -->
                <circle cx="178" cy="85" r="9" fill="#FFFFFF" stroke="#243042" stroke-width="5" />
            </g>
        <?php elseif ($state === 'pointing'): ?>
            <!-- Pointing Right Hand (pointing downwards/rightwards at CTA) -->
            <g class="mascot-hand-right pointing-hand">
                <!-- Arm -->
                <path d="M 158,112 C 168,118 180,126 186,134 C 188,138 185,142 180,140 C 172,136 162,126 156,120" fill="none" stroke="#243042" stroke-width="6" stroke-linecap="round" />
                <!-- Glove Hand pointing -->
                <g transform="translate(172, 126)">
                    <!-- Pointing finger -->
                    <path d="M 6,6 C 14,14 18,18 14,22 C 10,26 6,22 -2,14 L 6,6 Z" fill="#FFFFFF" stroke="#243042" stroke-width="5" />
                    <!-- Folded fingers -->
                    <circle cx="0" cy="5" r="5" fill="#FFFFFF" stroke="#243042" stroke-width="4" />
                    <circle cx="-4" cy="1" r="5" fill="#FFFFFF" stroke="#243042" stroke-width="4" />
                </g>
            </g>
        <?php elseif ($state === 'cheering'): ?>
            <!-- Cheering Right Hand Raised -->
            <g class="mascot-hand-right cheering-hand">
                <path d="M 155,95 C 168,80 180,70 188,80 C 196,90 180,105 165,110 C 162,105 158,100 155,95 Z" fill="#FFFFFF" stroke="#243042" stroke-width="6" stroke-linejoin="round" />
                <circle cx="185" cy="78" r="5" fill="#FFFFFF" stroke="#243042" stroke-width="4" />
            </g>
        <?php else: ?>
            <!-- Thumbs Up / Happy hand -->
            <g class="mascot-hand-right happy-hand">
                <!-- Arm -->
                <path d="M 158,110 C 168,112 178,115 182,118" fill="none" stroke="#243042" stroke-width="6" stroke-linecap="round" />
                <!-- Glove Hand Thumbs Up -->
                <g transform="translate(170, 104)">
                    <path d="M 8,-4 C 8,-12 14,-12 14,-4 C 14,2 10,4 6,2 Z" fill="#FFFFFF" stroke="#243042" stroke-width="5" /> <!-- Thumb -->
                    <circle cx="4" cy="5" r="5" fill="#FFFFFF" stroke="#243042" stroke-width="4" />
                    <circle cx="4" cy="11" r="5" fill="#FFFFFF" stroke="#243042" stroke-width="4" />
                    <circle cx="2" cy="16" r="4.5" fill="#FFFFFF" stroke="#243042" stroke-width="4" />
                </g>
            </g>
        <?php endif; ?>
    </svg>
</div>
