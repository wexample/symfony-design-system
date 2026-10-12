// An image made the size it is used at before it leaves the browser: a photo
// from a stock library, thousands of pixels and megabytes wide, is sent at
// the size a page shows, not at the size it was taken.

// The kinds a canvas reads and writes back as they were. A GIF would lose its
// frames, an SVG has no pixels to spare: they go as they are.
const SHRINKABLE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

// A photo re-encoded loses next to nothing at this quality, and half its weight.
const DEFAULT_QUALITY = 0.88;

// The size width × height takes once its longer side is at most maxSize, the
// ratio kept; as it is when it already fits.
export function imageFitWithin(
  width: number,
  height: number,
  maxSize: number
): { width: number; height: number } {
  const longest = Math.max(width, height);

  if (longest <= maxSize) {
    return { width, height };
  }

  const ratio = maxSize / longest;

  return { width: Math.round(width * ratio), height: Math.round(height * ratio) };
}

// The file itself when there is nothing to gain — not an image a canvas
// redraws, already small enough, unreadable —, else a smaller copy of the
// same type and name, its longer side at maxSize.
export async function imageShrinkFile(
  file: File,
  maxSize: number,
  quality = DEFAULT_QUALITY
): Promise<File> {
  if (!SHRINKABLE_TYPES.includes(file.type)) {
    return file;
  }

  let bitmap: ImageBitmap;

  try {
    // Turned as the camera said it was held.
    bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
  } catch {
    return file;
  }

  const size = imageFitWithin(bitmap.width, bitmap.height, maxSize);

  if (size.width === bitmap.width && size.height === bitmap.height) {
    bitmap.close();

    return file;
  }

  const canvas = document.createElement('canvas');
  canvas.width = size.width;
  canvas.height = size.height;
  const context = canvas.getContext('2d');
  context.imageSmoothingQuality = 'high';
  context.drawImage(bitmap, 0, 0, size.width, size.height);
  bitmap.close();

  const blob = await new Promise<Blob | null>((resolve) => canvas.toBlob(resolve, file.type, quality));

  return blob ? new File([blob], file.name, { type: file.type, lastModified: file.lastModified }) : file;
}
