<?php
/**
 * Script d'optimisation des images uploadées
 * Compresse les images et crée des thumbnails
 * À appeler après un upload réussi
 */

function optimizeImage($imagePath, $maxWidth = 1200, $maxHeight = 1200) {
    try {
        if (!file_exists($imagePath)) {
            return false;
        }

        // Obtenir les infos de l'image
        $imageInfo = getimagesize($imagePath);
        if ($imageInfo === false) {
            return false;
        }

        list($width, $height, $type) = $imageInfo;

        // Supporter JPEG, PNG, WebP, GIF
        $supported = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];
        if (!in_array($type, $supported)) {
            return false;
        }

        // Charger l'image
        switch ($type) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($imagePath);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($imagePath);
                break;
            default:
                return false;
        }

        if ($image === false) {
            return false;
        }

        // Redimensionner si nécessaire
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $newImage;
        }

        // Sauvegarder l'image optimisée (JPEG par défaut pour meilleure compression)
        ob_start();
        
        if ($type === IMAGETYPE_WEBP) {
            $extension = '.webp';
            imagewebp($image, null, 85);
        } else {
            $extension = '.jpg';
            imagejpeg($image, null, 85); // 85% de qualité
        }
        
        $optimized = ob_get_clean();
        imagedestroy($image);

        // Sauvegarde le fichier optimisé
        $optimizedPath = str_replace(['..jpg', '.jpeg', '.png', '.gif', '.webp'], '.jpg', $imagePath);
        file_put_contents($optimizedPath, $optimized);

        // Créer un thumbnail (200x200)
        createThumbnail($optimizedPath);

        // Supprimer le fichier original s'il est différent de l'optimisé
        if ($imagePath !== $optimizedPath && file_exists($imagePath)) {
            unlink($imagePath);
        }

        return $optimizedPath;

    } catch (Exception $e) {
        error_log('Erreur optimisation image: ' . $e->getMessage());
        return false;
    }
}

/**
 * Crée un thumbnail pour une image
 * Stocké dans le même dossier avec prefix "thumb_"
 */
function createThumbnail($imagePath, $size = 200) {
    try {
        if (!file_exists($imagePath)) {
            return false;
        }

        $imageInfo = getimagesize($imagePath);
        if ($imageInfo === false) {
            return false;
        }

        list($width, $height, $type) = $imageInfo;

        // Charger l'image
        switch ($type) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($imagePath);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($imagePath);
                break;
            default:
                return false;
        }

        if ($image === false) {
            return false;
        }

        // Créer un carré centré
        $squareSize = min($width, $height);
        $x = ($width - $squareSize) / 2;
        $y = ($height - $squareSize) / 2;

        $thumbnail = imagecreatetruecolor($size, $size);
        imagecopyresampled($thumbnail, $image, 0, 0, $x, $y, $size, $size, $squareSize, $squareSize);
        imagedestroy($image);

        // Sauvegarder le thumbnail
        $dir = dirname($imagePath);
        $filename = basename($imagePath);
        $thumbPath = $dir . '/thumb_' . $filename;

        imagejpeg($thumbnail, $thumbPath, 85);
        imagedestroy($thumbnail);

        return $thumbPath;

    } catch (Exception $e) {
        error_log('Erreur création thumbnail: ' . $e->getMessage());
        return false;
    }
}

/**
 * Nettoie les anciens fichiers non utilisés
 * À appeler régulièrement (via cron)
 */
function cleanupOldImages($uploadDir = '../uploads/', $maxAge = 30 * 24 * 3600) {
    try {
        $now = time();
        $deleted = 0;

        if (!is_dir($uploadDir)) {
            return 0;
        }

        $files = scandir($uploadDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $uploadDir . $file;
            $fileAge = $now - filemtime($filePath);

            if ($fileAge > $maxAge && is_file($filePath)) {
                unlink($filePath);
                $deleted++;
            }
        }

        return $deleted;

    } catch (Exception $e) {
        error_log('Erreur nettoyage images: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Obtient la taille et les infos d'une image optimisée
 */
function getImageStats($imagePath) {
    try {
        if (!file_exists($imagePath)) {
            return false;
        }

        $filesize = filesize($imagePath);
        $imageInfo = getimagesize($imagePath);

        if ($imageInfo === false) {
            return false;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'size' => $filesize,
            'size_readable' => formatBytes($filesize),
            'type' => $imageInfo['mime'],
            'modified' => date('Y-m-d H:i:s', filemtime($imagePath))
        ];

    } catch (Exception $e) {
        error_log('Erreur stats image: ' . $e->getMessage());
        return false;
    }
}

/**
 * Formate les bytes en format lisible
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}
