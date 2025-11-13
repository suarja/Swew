<?php

declare(strict_types=1);

namespace App\Service;

final class MarkdownConverter
{
    public function convert(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", trim($text));

        $blockPlaceholders = [];
        $normalized = preg_replace_callback('/```(.*?)```/s', function (array $matches) use (&$blockPlaceholders): string {
            $key = sprintf('__CODE_BLOCK_%d__', count($blockPlaceholders));
            $raw = trim($matches[1]);
            $lines = preg_split('/\n/', $raw) ?: [];
            if ($lines !== [] && preg_match('/^[A-Za-z0-9_\-+.]+$/', $lines[0]) === 1) {
                array_shift($lines);
            }
            $code = htmlspecialchars(trim(implode("\n", $lines) ?: $raw), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $blockPlaceholders[$key] = sprintf('<pre class="code-block"><code>%s</code></pre>', $code);

            return $key;
        }, $normalized) ?? $normalized;

        $inlinePlaceholders = [];
        $normalized = preg_replace_callback('/`([^`]+)`/', function (array $matches) use (&$inlinePlaceholders): string {
            $key = sprintf('__INLINE_CODE_%d__', count($inlinePlaceholders));
            $inlinePlaceholders[$key] = sprintf('<code>%s</code>', htmlspecialchars($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

            return $key;
        }, $normalized) ?? $normalized;

        $blocks = array_filter(array_map('trim', preg_split('/\n{2,}/', $normalized) ?: []));
        $htmlBlocks = [];

        foreach ($blocks as $block) {
            if (isset($blockPlaceholders[$block])) {
                $htmlBlocks[] = $blockPlaceholders[$block];

                continue;
            }

            if (preg_match('/^(#{1,6})\s+(.+)$/', $block, $matches) === 1) {
                $level = min(6, strlen($matches[1]));
                $htmlBlocks[] = sprintf('<h%d>%s</h%d>', $level, $this->formatInline($matches[2], $inlinePlaceholders), $level);

                continue;
            }

            if (preg_match('/^(-|\*)\s/m', $block) === 1) {
                $items = array_filter(array_map('trim', explode("\n", $block)));
                $htmlItems = array_map(function (string $item) use ($inlinePlaceholders): string {
                    $item = preg_replace('/^(-|\*)\s+/', '', $item) ?? $item;

                    return sprintf('<li>%s</li>', $this->formatInline($item, $inlinePlaceholders));
                }, $items);
                $htmlBlocks[] = sprintf('<ul>%s</ul>', implode('', $htmlItems));

                continue;
            }

            $htmlBlocks[] = sprintf('<p>%s</p>', $this->formatInline($block, $inlinePlaceholders));
        }

        return implode("\n", $htmlBlocks);
    }

    /**
     * @param array<string, string> $inlinePlaceholders
     */
    private function formatInline(string $text, array $inlinePlaceholders): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escaped = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $escaped) ?? $escaped;
        $escaped = preg_replace('/(?<!_)_(?!_)(.+?)(?<!_)_(?!_)/s', '<em>$1</em>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\[(.+?)\]\((https?:\/\/[^\s)]+)\)/', '<a href="$2">$1</a>', $escaped) ?? $escaped;

        foreach ($inlinePlaceholders as $key => $replacement) {
            $escaped = str_replace($key, $replacement, $escaped);
        }

        return $escaped;
    }
}
