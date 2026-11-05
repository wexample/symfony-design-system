// Where a video plays, as the video component reads it: a host's page for it
// (YouTube, Vimeo) is played by the host's own player, embedded; any other
// address is a file the browser plays. The twin of src/Helper/VideoHelper.php,
// which the server video follows: a change to either is a change to both.

const VIDEO_YOUTUBE = /^https?:\/\/(?:www\.|m\.)?(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{6,})/i;

const VIDEO_VIMEO = /^https?:\/\/(?:www\.|player\.)?vimeo\.com\/(?:video\/)?(\d+)/i;

// The host's player for a host's page; null for a file.
export function videoEmbedUrl(src: string): string | null {
  const address = String(src ?? '').trim();
  const youtube = VIDEO_YOUTUBE.exec(address);

  if (youtube) {
    return `https://www.youtube-nocookie.com/embed/${youtube[1]}`;
  }

  const vimeo = VIDEO_VIMEO.exec(address);

  return vimeo ? `https://player.vimeo.com/video/${vimeo[1]}` : null;
}
