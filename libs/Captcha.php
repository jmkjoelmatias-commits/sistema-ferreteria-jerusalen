<?php
/**
 * Clase Captcha - Validación local de retos de imágenes 3x3
 * Mapeo funcional equivalente para mantener la consistencia del MVC
 */

class Captcha {
    private static $categories = [
        "escaleras" => ["stairs_1.jpg", "stairs_2.jpg", "stairs_3.jpg"],
        "herramientas" => ["hammer.jpg", "shovel.jpg", "wheelbarrow.jpg"],
        "materiales" => ["cement.jpg", "brick.jpg"],
        "seguridad" => ["helmet.jpg"]
    ];

    private static $categoryNames = [
        "escaleras" => "escaleras",
        "herramientas" => "herramientas de construcción",
        "materiales" => "materiales de construcción (cemento/ladrillo)",
        "seguridad" => "elementos de seguridad (cascos)"
    ];

    private static $allImages = [
        "stairs_1.jpg", "stairs_2.jpg", "stairs_3.jpg",
        "hammer.jpg", "shovel.jpg", "wheelbarrow.jpg",
        "cement.jpg", "brick.jpg", "helmet.jpg"
    ];

    public static function getChallenge() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Seleccionar categoría aleatoria
        $keys = array_keys(self::$categories);
        $targetCat = $keys[array_rand($keys)];
        $targetImages = self::$categories[$targetCat];

        // Barajar imágenes
        $shuffledImages = self::$allImages;
        shuffle($shuffledImages);

        // Encontrar índices correctos
        $correctIndices = [];
        foreach ($shuffledImages as $idx => $img) {
            if (in_array($img, $targetImages)) {
                $correctIndices[] = $idx;
            }
        }

        // Guardar en sesión PHP
        $_SESSION['captcha_indices'] = $correctIndices;
        $_SESSION['captcha_target'] = self::$categoryNames[$targetCat];

        return [
            'target' => self::$categoryNames[$targetCat],
            'images' => $shuffledImages
        ];
    }

    public static function verify($selectedIndices) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $correctIndices = isset($_SESSION['captcha_indices']) ? $_SESSION['captcha_indices'] : [];
        if (empty($correctIndices)) {
            return false;
        }

        sort($selectedIndices);
        sort($correctIndices);

        return $selectedIndices === $correctIndices;
    }
}
