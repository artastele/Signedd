<?php
// SignED — HTML Sanitizer for Lesson Content
// Protects against Stored XSS while preserving rich educational formatting and FSL interactive spans.

class HtmlSanitizer {

    /**
     * Allowed HTML tags in lesson slide content.
     */
    private static array $allowedTags = [
        // Text & Typography
        'p', 'br', 'hr', 'span', 'div', 'b', 'strong', 'i', 'em', 'u', 's', 'strike',
        'del', 'sub', 'sup', 'mark', 'small', 'blockquote', 'pre', 'code',
        // Headings
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        // Lists
        'ul', 'ol', 'li',
        // Tables
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption',
        // Media & Links
        'a', 'img', 'video', 'source',
        // Interactive Elements
        'button'
    ];

    /**
     * Allowed attributes per tag or globally.
     */
    private static array $globalAllowedAttributes = [
        'class', 'id', 'style', 'title', 'dir', 'lang',
        // Custom FSL and Summernote Shape attributes
        'data-word', 'data-video', 'data-category', 'data-desc', 'data-fsl',
        'data-shape-box', 'data-shape', 'data-speed'
    ];

    private static array $tagSpecificAttributes = [
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height', 'loading', 'data-filename'],
        'video' => ['src', 'controls', 'autoplay', 'loop', 'muted', 'playsinline', 'width', 'height', 'poster'],
        'source' => ['src', 'type'],
        'table' => ['border', 'cellpadding', 'cellspacing', 'width'],
        'th' => ['colspan', 'rowspan', 'width', 'align', 'valign'],
        'td' => ['colspan', 'rowspan', 'width', 'align', 'valign'],
        'button' => ['type', 'disabled']
    ];

    /**
     * Clean and sanitize HTML string.
     */
    public static function cleanLessonHtml(?string $html): string {
        if ($html === null) {
            return '';
        }
        $trimmed = trim($html);
        if ($trimmed === '' || $trimmed === '<p><br></p>') {
            return '';
        }

        // 1. First-pass removal of high-risk executable blocks and their contents
        $sanitized = preg_replace('#<(script|style|iframe|object|embed|applet|form|svg)[\s\S]*?<\s*/\s*\1\s*>#i', '', $trimmed);
        // Remove any self-closing or unclosed dangerous tags
        $sanitized = preg_replace('#<\s*(script|style|iframe|object|embed|applet|form|svg|input|textarea|meta|link|base)[^>]*>#i', '', $sanitized);

        if (trim($sanitized) === '') {
            return '';
        }

        // 2. Parse with DOMDocument for robust tree-based attribute filtering
        $dom = new DOMDocument();
        // Prevent XML errors from breaking execution and enforce UTF-8
        libxml_use_internal_errors(true);
        $encodedHtml = '<?xml encoding="utf-8" ?><div>' . $sanitized . '</div>';
        $dom->loadHTML($encodedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $container = $dom->getElementsByTagName('div')->item(0);
        if (!$container) {
            return '';
        }

        self::cleanNode($container);

        // 3. Extract inner HTML of container
        $output = '';
        foreach ($container->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    /**
     * Recursively clean a DOM node and its descendants.
     */
    private static function cleanNode(DOMNode $node): void {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tagName = strtolower($node->nodeName);

            // If tag is not in allowlist, replace with text or children
            if (!in_array($tagName, self::$allowedTags, true)) {
                $parent = $node->parentNode;
                if ($parent) {
                    while ($node->hasChildNodes()) {
                        $child = $node->firstChild;
                        self::cleanNode($child);
                        $parent->insertBefore($child, $node);
                    }
                    $parent->removeChild($node);
                    return;
                }
            }

            // Clean attributes
            if ($node->hasAttributes()) {
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    $attrName = strtolower($attr->name);
                    $attrValue = $attr->value;

                    // Immediately reject any on* event handler
                    if (str_starts_with($attrName, 'on')) {
                        $attrsToRemove[] = $attr->name;
                        continue;
                    }

                    // Check if attribute is allowed on this tag or globally
                    $isAllowed = in_array($attrName, self::$globalAllowedAttributes, true)
                        || (isset(self::$tagSpecificAttributes[$tagName]) && in_array($attrName, self::$tagSpecificAttributes[$tagName], true));

                    if (!$isAllowed) {
                        $attrsToRemove[] = $attr->name;
                        continue;
                    }

                    // Validate URL attributes
                    if (in_array($attrName, ['href', 'src', 'poster'], true)) {
                        $cleanUrl = self::sanitizeUrl($attrValue, $tagName, $attrName);
                        if ($cleanUrl === null) {
                            $attrsToRemove[] = $attr->name;
                            continue;
                        }
                        $node->setAttribute($attr->name, $cleanUrl);
                    }

                    // Validate inline style attribute
                    if ($attrName === 'style') {
                        $cleanStyle = self::sanitizeStyle($attrValue);
                        if (empty($cleanStyle)) {
                            $attrsToRemove[] = $attr->name;
                            continue;
                        }
                        $node->setAttribute($attr->name, $cleanStyle);
                    }
                }

                foreach ($attrsToRemove as $rName) {
                    $node->removeAttribute($rName);
                }
            }
        }

        // Recursively clean all children
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }
        foreach ($children as $child) {
            self::cleanNode($child);
        }
    }

    /**
     * Sanitize URL attribute values and normalize local environment prefixes.
     */
    private static function sanitizeUrl(string $url, string $tagName, string $attrName): ?string {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        // Block executable pseudo-protocols
        if (preg_match('/^(javascript|vbscript|data):/i', $trimmed)) {
            // Allow only safe base64 images on <img> tags
            if ($tagName === 'img' && $attrName === 'src' && preg_match('/^data:image\/(png|jpe?g|gif|webp|svg\+xml);base64,/i', $trimmed)) {
                return $trimmed;
            }
            return null;
        }

        // Normalize local environment-dependent upload paths for BUG-02
        // e.g. /Signedd/public/uploads/lesson_editor/... or http://localhost/Signedd/public/uploads/... -> uploads/lesson_editor/...
        if (preg_match('#(?:https?://[^/]+)?(?:/.*?/)?(uploads/(?:lesson_editor|lesson_pages|fsl_custom_videos)/[^\s"\'<>]+)#i', $trimmed, $m)) {
            return $m[1];
        }

        return htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Strip dangerous CSS properties/functions from inline style strings.
     */
    private static function sanitizeStyle(string $style): string {
        // Disallow CSS expressions, behaviors, javascript: URLs
        if (preg_match('/(expression|behavior|-moz-binding|javascript:|vbscript:)/i', $style)) {
            return '';
        }
        return trim($style);
    }
}
