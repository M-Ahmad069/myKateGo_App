<?php

namespace App\Support;

final class AiJson
{
    public static function stripFences(string $content): string
    {
        $c = trim($content);
        $c = preg_replace('/^```(?:json)?\s*/i', '', $c) ?? $c;
        $c = preg_replace('/```$/', '', $c) ?? $c;

        return trim($c);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function decodeChatResponse(string $content): ?array
    {
        $c = self::stripFences($content);
        $decoded = json_decode($c, true);

        return is_array($decoded) ? $decoded : null;
    }
}
