<?php

namespace App\Support;

use App\Models\User;

/**
 * Resolves the current user's theme preference into the
 * `data-bs-theme` and `data-awt-theme` attributes the layouts use.
 *
 * Theme keys:
 *   sky / lake / eggplant — light variants (data-bs-theme=light)
 *   dark                  — dark variant   (data-bs-theme=dark)
 *   high-contrast         — dark PLUS an overlay: the element wears
 *                           `awt-theme-dark awt-theme-high-contrast` together,
 *                           so every dark rule applies and the overlay only has
 *                           to raise contrast. See classesFor().
 *
 * Theme mode:
 *   light  — force light variant
 *   dark   — force dark variant
 *   system — follow `prefers-color-scheme`; resolved client-side, server falls back to the saved variant
 */
class AppTheme
{
    /** Variants the user can actually pick / save. */
    public const VARIANTS = ['sky', 'lake', 'eggplant', 'dark', 'high-contrast'];

    /** Variants that render on a dark surface. */
    public const DARK_VARIANTS = ['dark', 'high-contrast'];

    public const MODES = ['light', 'dark', 'system'];

    public const DEFAULT_VARIANT = 'lake';

    public const DEFAULT_MODE = 'light';

    /**
     * The theme this user sees.
     *
     * @return array{variant: string, mode: string, bs_theme: string, classes: string, source: string}
     */
    public static function forUser(?User $user): array
    {
        $variant = null;
        $mode = null;
        $source = 'default';

        if ($user && isset($user->theme_variant) && in_array($user->theme_variant, self::VARIANTS, true)) {
            $variant = $user->theme_variant;
            $source = 'user';
        }

        if ($user && isset($user->theme_mode) && in_array($user->theme_mode, self::MODES, true)) {
            $mode = $user->theme_mode;
        }

        // Check session or cookie if user is not logged in or has no preference
        if ($variant === null && session()->has('theme_variant')) {
            $sessionVariant = session('theme_variant');
            if (in_array($sessionVariant, self::VARIANTS, true)) {
                $variant = $sessionVariant;
                $source = 'session';
            }
        }

        if ($mode === null && session()->has('theme_mode')) {
            $sessionMode = session('theme_mode');
            if (in_array($sessionMode, self::MODES, true)) {
                $mode = $sessionMode;
            }
        }

        $variant ??= self::DEFAULT_VARIANT;
        $mode ??= self::DEFAULT_MODE;

        return [
            'variant' => $variant,
            'mode' => $mode,
            'bs_theme' => self::resolveBsTheme($variant, $mode),
            'classes' => self::classesFor($variant),
            'source' => $source,
        ];
    }

    /**
     * The `awt-theme-*` classes the <html> element wears.
     */
    public static function classesFor(string $variant): string
    {
        return $variant === 'high-contrast'
            ? 'awt-theme-dark awt-theme-high-contrast'
            : 'awt-theme-'.$variant;
    }

    /**
     * Keep a variant and a mode consistent with one another.
     */
    public static function pairWithMode(string $variant, string $mode): string
    {
        if ($mode === 'dark' && ! in_array($variant, self::DARK_VARIANTS, true)) {
            return 'dark';
        }

        if ($mode === 'light' && in_array($variant, self::DARK_VARIANTS, true)) {
            return self::DEFAULT_VARIANT;
        }

        return $variant;
    }

    /**
     * Human-readable label for a variant.
     */
    public static function labelFor(string $variant): string
    {
        return match ($variant) {
            'sky' => 'Sky (Teal / Cyan)',
            'lake' => 'Lake (Default Blue)',
            'eggplant' => 'Eggplant (Purple)',
            'dark' => 'Dark',
            'high-contrast' => 'High Contrast',
            default => ucfirst($variant),
        };
    }

    /**
     * Resolve the Bootstrap 5 theme attribute (light or dark).
     */
    public static function resolveBsTheme(string $variant, string $mode): string
    {
        if ($mode === 'dark') {
            return 'dark';
        }

        if ($mode === 'light') {
            return 'light';
        }

        return in_array($variant, self::DARK_VARIANTS, true) ? 'dark' : 'light';
    }
}
