<?php

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
     *
     * @var array
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
     * Get the raw response data from the API
     *
     * @return array The complete, unprocessed response data
     */
    public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * Check if the API request was successful
     *
     * @return bool True if the request succeeded, false otherwise
     */
    public function isSuccess(): bool
    {
        return !isset($this->data['error']);
    }

    /**
     * Get the error message if the request failed
     *
     * @return string|null The error message, or null if no error occurred
     */
    public function getError(): ?string
    {
        return $this->data['error'] ?? null;
    }

    /**
     * Get the dominant color as a hexadecimal string
     *
     * @return string|null The dominant color in hex format (e.g., "#FF5733"), or null if not available
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
     * @return string|null The text color in hex format (e.g., "#FFFFFF"), or null if not available
     */
    public function getTextColorHex(): ?string
    {
        return $this->data['text_color_hex'] ?? null;
    }

    /**
     * Get the dominant color in Flutter-compatible hex format
     *
     * @return string|null The dominant color in Flutter hex format (e.g., "0xFFFF5733"), or null if not available
     */
    public function getDominantColorFlutterHex(): ?string
    {
        return $this->data['dominant_color_flutter_hex'] ?? null;
    }

    /**
     * Get the text color in Flutter-compatible hex format
     *
     * @return string|null The text color in Flutter hex format (e.g., "0xFFFFFFFF"), or null if not available
     */
    public function getTextColorFlutterHex(): ?string
    {
        return $this->data['text_color_flutter_hex'] ?? null;
    }

    /**
     * Get the complete color palette extracted from the image
     *
     * Returns an array of colors representing the most prominent colors
     * found in the analyzed image.
     *
     * @return array Array of color information, or empty array if not available
     */
    public function getPalette(): array
    {
        return $this->data['palette'] ?? [];
    }

    /**
     * Get the dominant color as RGB values
     *
     * @return array|null Array with 'r', 'g', 'b' keys, or null if not available
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
}