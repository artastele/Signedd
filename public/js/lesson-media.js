// Resolve stored private-media paths against the app, independent of the current page URL.
(function (root) {
    'use strict';
    function resolveUrl(value, base) {
        if (typeof value !== 'string') return value;
        const path = value.trim();
        if (!/^(?:\.\/)?finance\/media\/[a-f0-9]{64}$/.test(path)) return value;
        return String(base || '').replace(/\/+$/, '') + '/' + path.replace(/^\.\//, '');
    }
    function resolveHtml(html, base) {
        if (!html || !html.includes('finance/media/')) return html || '';
        const template = root.document.createElement('template');
        template.innerHTML = html;
        let changed = false;
        for (const element of template.content.querySelectorAll('[src], [href], [poster], [data-video]')) {
            for (const attribute of ['src', 'href', 'poster', 'data-video']) {
                if (!element.hasAttribute(attribute)) continue;
                const value = element.getAttribute(attribute);
                const resolved = resolveUrl(value, base);
                if (value !== resolved) {
                    element.setAttribute(attribute, resolved);
                    changed = true;
                }
            }
        }
        return changed ? template.innerHTML : html;
    }
    root.LessonMedia = {resolveUrl, resolveHtml};
    if (typeof module !== 'undefined' && module.exports) module.exports = root.LessonMedia;
})(typeof window !== 'undefined' ? window : globalThis);
