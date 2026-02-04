<?php

declare(strict_types=1);

namespace RadioAPI\Response;

/**
 * Response object for ColorThief API endpoint
 *
 * Provides convenient methods for accessing color data extracted from images,
 * including dominant colors, text colors, and color palettes in various formats.
 *
 * @package RadioAPI\Response
 */
class ColorResponse implements ResponseInterface
{
    /**
     * Raw response data from the API
     */
    private array $data;

    /**
     * Create a new ColorResponse instance
     *
     * @param array $data The raw response data from the ColorThief API
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function isSuccess(): bool
    {
        return !isset($this->data['error']);
    }

    /**
     * @inheritDoc
     */
    public function getError(): ?string
    {
        return $this->data['error'] ?? null;
    }

    /**
     * Get the dominant color as a hexadecimal string
     *
     * @return string|null The dominant color in hex format (e.g., "#FF5733")
     *
     * @example
     * ```php
     * $color = $response->getDominantColorHex();
     * echo "Dominant color: $color";
     * ```
     */
    public function getDominantColorHex(): ?string
    {
        return $this->data['dominant_color_hex'] ?? null;
    }

    /**
     * Get the recommended text color as a hexadecimal string
     *
     * Returns a color that provides good contrast against the dominant color
     * for text readability purposes.
     *
     * @return string|null The text color in hex format (e.g., "#FFFFFF")
     */
    public function getTextColorHex(): ?string
    {
        return $this->data['text_color_hex'] ?? null;
    }

    /**
     * Get the dominant color in Flutter-compatible hex format
     *
     * @return string|null The dominant color in Flutter hex format (e.g., "0xFFFF5733")
     */
    public function getDominantColorFlutterHex(): ?string
    {
        return $this->data['dominant_color_flutter_hex'] ?? null;
    }

    /**
     * Get the text color in Flutter-compatible hex format
     *
     * @return string|null The text color in Flutter hex format (e.g., "0xFFFFFFFF")
     */
    public function getTextColorFlutterHex(): ?string
    {
        return $this->data['text_color_flutter_hex'] ?? null;
    }

    /**
     * Get the dominant color as RGB values
     *
     * @return array|null Array with 'r', 'g', 'b' keys, or null if not available
     *
     * @example
     * ```php
     * $rgb = $response->getDominantColorRgb();
     * echo "RGB: {$rgb['r']}, {$rgb['g']}, {$rgb['b']}";
     * ```
     */
    public function getDominantColorRgb(): ?array
    {
        return $this->data['dominant_color_rgb'] ?? null;
    }

    /**
     * Get the text color as RGB values
     *
     * @return array|null Array with 'r', 'g', 'b' keys, or null if not available
     */
    public function getTextColorRgb(): ?array
    {
        return $this->data['text_color_rgb'] ?? null;
    }

    /**
     * Get the dominant color as CSS rgb() function
     *
     * @return string|null CSS rgb() function (e.g., "rgb(255, 87, 51)")
     *
     * @example
     * ```php
     * $css = $response->getDominantColorCss();
     * echo "<div style='background-color: $css;'>Content</div>";
     * ```
     */
    public function getDominantColorCss(): ?string
    {
        $rgb = $this->getDominantColorRgb();
        
        if ($rgb === null) {
            return null;
        }

        return sprintf('rgb(%d, %d, %d)', $rgb['r'], $rgb['g'], $rgb['b']);
    }

    /**
     * Get the text color as CSS rgb() function
     *
     * @return string|null CSS rgb() function (e.g., "rgb(255, 255, 255)")
     */
    public function getTextColorCss(): ?string
    {
        $rgb = $this->getTextColorRgb();
        
        if ($rgb === null) {
            return null;
        }

        return sprintf('rgb(%d, %d, %d)', $rgb['r'], $rgb['g'], $rgb['b']);
    }

    /**
     * Get the complete color palette extracted from the image
     *
     * Returns an array of colors representing the most prominent colors
     * found in the analyzed image.
     *
     * @return array Array of color information
     *
     * @example
     * ```php
     * foreach ($response->getPalette() as $color) {
     *     echo "Color: " . $color['hex'] . "\n";
     * }
     * ```
     */
    public function getPalette(): array
    {
        return $this->data['palette'] ?? [];
    }

    /**
     * Get a palette color by index
     *
     * @param int $index Zero-based index of the color in the palette
     * @return array|null Color data at the specified index
     */
    public function getPaletteColor(int $index): ?array
    {
        $palette = $this->getPalette();
        return $palette[$index] ?? null;
    }

    /**
     * Get the number of colors in the palette
     *
     * @return int The count of palette colors
     */
    public function getPaletteCount(): int
    {
        return count($this->getPalette());
    }
}
