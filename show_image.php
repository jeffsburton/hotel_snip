<?php

/**
 * Shows an image from the image_room table.
 * The content type is set automatically based on the image type.
 *
 * @param image_id id of the image_room record.
 *
 * @return binary data of image.
 */

// Include the database connection script
require_once 'ajax/db.php';


/**
 * Resize a binary image while keeping its aspect ratio.
 *
 * @param string $binaryImage The binary content of the original image.
 * @param int $targetWidth The desired width of the resized image.
 * @return string|false The binary content of the resized image on success, or false on failure.
 */
function resizeImage($binaryImage, $targetWidth)
{
	// Create a temporary file for the binary image
	$tempFile = tempnam(sys_get_temp_dir(), 'img');
	if (!$tempFile) {
		return false; // Could not create temporary file
	}

	// Write the binary content to the temporary file
	file_put_contents($tempFile, $binaryImage);

	// Get the image info
	$imageInfo = getimagesize($tempFile);
	if (!$imageInfo) {
		unlink($tempFile); // Delete temp file
		return false; // Could not read image info
	}

	list($originalWidth, $originalHeight, $imageType) = $imageInfo;

	// Calculate target height to maintain aspect ratio
	$aspectRatio = $originalHeight / $originalWidth;
	$targetHeight = intval($targetWidth * $aspectRatio);

	// Create the appropriate image resource from the binary content
	$sourceImage = null;
	switch ($imageType) {
		case IMAGETYPE_JPEG:
			$sourceImage = imagecreatefromjpeg($tempFile);
			break;
		case IMAGETYPE_PNG:
			$sourceImage = imagecreatefrompng($tempFile);
			break;
		default:
			unlink($tempFile); // Delete temp file
			return false; // Unsupported image type
	}

	// Delete the temporary source file as it's no longer needed
	unlink($tempFile);

	// Create a blank canvas for the resized image
	$resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);

	// Preserve transparency for PNG images
	if ($imageType == IMAGETYPE_PNG) {
		imagealphablending($resizedImage, false);
		imagesavealpha($resizedImage, true);
	}

	// Resize the image
	if (!imagecopyresampled(
		$resizedImage,
		$sourceImage,
		0, 0, 0, 0, // Destination and source x, y positions
		$targetWidth, $targetHeight, // Destination dimensions
		$originalWidth, $originalHeight // Source dimensions
	)) {
		imagedestroy($sourceImage);
		imagedestroy($resizedImage);
		return false;
	}

	// Create a new temporary file to store the resized image
	$resizedTempFile = tempnam(sys_get_temp_dir(), 'img');
	if (!$resizedTempFile) {
		imagedestroy($sourceImage);
		imagedestroy($resizedImage);
		return false;
	}

	// Save the resized image to a temporary file
	$success = false;
	switch ($imageType) {
		case IMAGETYPE_JPEG:
			$success = imagejpeg($resizedImage, $resizedTempFile, 90); // 90 = Quality
			break;
		case IMAGETYPE_PNG:
			$success = imagepng($resizedImage, $resizedTempFile, 9); // 9 = Compression
			break;
	}

	// Free image resources
	imagedestroy($sourceImage);
	imagedestroy($resizedImage);

	if (!$success) {
		unlink($resizedTempFile);
		return false;
	}

	// Read the resized image content as binary
	$resizedImageBinary = file_get_contents($resizedTempFile);

	// Delete the resized temporary file
	unlink($resizedTempFile);

	return $resizedImageBinary;
}


// Check if the `image_id` parameter is provided in the request
if (!isset($_GET['image_id']) || empty($_GET['image_id'])) {
	http_response_code(400); // Bad Request
	echo "Image ID is required.";
	exit();
}

// Get the image_id parameter (ensure it is safely sanitized)
$image_id = intval($_GET['image_id']); // Cast as integer for safety

if (!$image_id) {
	echo "Image ID may not be 0";
	exit();
}



try {

	function detectImageType(string $binaryData): string {
		// Check if binary data starts with JPEG magic numbers
		if (substr($binaryData, 0, 2) === "\xFF\xD8" && substr($binaryData, -2) === "\xFF\xD9") {
			return 'jpeg';
		}
		// Check if binary data starts with PNG magic numbers
		elseif (substr($binaryData, 0, 8) === "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") {
			return 'png';
		}

		// If neither matches, return unknown
		return 'unknown';
	}


	$records = select("SELECT `file` FROM image_room WHERE id = ?", $image_id);



	if (count($records) > 0) {

		$file = $records[0]['file'];

		if (isset($_GET['width'])) {
			$file = resizeImage($file, intval($_GET['width']));
		}

		ob_clean(); // Clear any output buffers

		// Set headers
		header("Content-Type: image/" . detectImageType($file));
		header("Content-Length: " . strlen($file));
		header('Cache-Control: public');

		echo $file; // Output the file data
		flush();
	}else {
		// No image found
		http_response_code(404); // Not Found
		echo "Image not found.";
	}
}catch (Exception $e) {
	echo $e->getMessage();
	exit();
}
?>