<?php
declare(strict_types=1);

/**
 * Whitelist-based HTML sanitizer for rich text produced by the admin editor.
 * Removes scripts, event handlers, style attributes and unsafe URLs.
 */
function sanitize_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }
    $allowed = [
        'p' => ['class'], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'h2' => ['id'], 'h3' => ['id'], 'h4' => ['id'], 'blockquote' => [], 'pre' => ['class'], 'code' => [],
        'ul' => [], 'ol' => [], 'li' => ['data-list'], 'a' => ['href', 'target', 'rel'], 'img' => ['src', 'alt', 'width', 'height'],
        'figure' => [], 'figcaption' => [], 'hr' => [], 'span' => ['class'],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => [], 'td' => [], 'iframe' => ['src', 'width', 'height', 'allowfullscreen', 'frameborder'],
    ];
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $doc->getElementById('__root');
    if (!$root) {
        return e(strip_tags($html));
    }
    sanitize_node($root, $allowed);
    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }
    return $out;
}

function sanitize_node(DOMNode $node, array $allowed): void
{
    for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
        $child = $node->childNodes->item($i);
        if ($child instanceof DOMComment) {
            $node->removeChild($child);
            continue;
        }
        if (!$child instanceof DOMElement) {
            continue;
        }
        $tag = strtolower($child->tagName);
        if (in_array($tag, ['script', 'style', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'link', 'meta'], true)) {
            $node->removeChild($child);
            continue;
        }
        if (!isset($allowed[$tag])) {
            sanitize_node($child, $allowed);
            while ($child->firstChild) {
                $node->insertBefore($child->firstChild, $child);
            }
            $node->removeChild($child);
            continue;
        }
        foreach (iterator_to_array($child->attributes) as $attr) {
            $name = strtolower($attr->name);
            if (!in_array($name, $allowed[$tag], true)) {
                $child->removeAttribute($attr->name);
                continue;
            }
            if (in_array($name, ['href', 'src'], true) && !safe_url($attr->value, $tag === 'iframe')) {
                $child->removeAttribute($attr->name);
            }
        }
        if ($tag === 'iframe' && !$child->hasAttribute('src')) {
            $node->removeChild($child);
            continue;
        }
        if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
            $child->setAttribute('rel', 'noopener noreferrer');
        }
        if ($tag === 'img') {
            $child->setAttribute('loading', 'lazy');
        }
        sanitize_node($child, $allowed);
    }
}

function safe_url(string $url, bool $embed = false): bool
{
    $url = trim($url);
    if ($embed) {
        return (bool)preg_match('#^https://(www\.)?(youtube\.com|youtube-nocookie\.com|player\.vimeo\.com|www\.google\.com/maps)/#i', $url);
    }
    if ($url === '' || str_starts_with($url, '/') || str_starts_with($url, '#')) {
        return true;
    }
    return (bool)preg_match('#^(https?:|mailto:|tel:)#i', $url);
}
