// Where a video plays. src/Helper/VideoHelper.php is its twin, and
// tests/Unit/Helper/VideoHelperTest.php holds the same cases.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { videoEmbedUrl } from '../../assets/js/Helper/VideoHelper.ts';

test('a host page is played by its player, a file by the browser', () => {
  assert.equal(videoEmbedUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ'), 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
  assert.equal(videoEmbedUrl('https://youtube.com/watch?feature=share&v=dQw4w9WgXcQ&t=3'), 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
  assert.equal(videoEmbedUrl('https://youtu.be/dQw4w9WgXcQ'), 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
  assert.equal(videoEmbedUrl('https://www.youtube.com/shorts/dQw4w9WgXcQ'), 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
  assert.equal(videoEmbedUrl('https://vimeo.com/76979871'), 'https://player.vimeo.com/video/76979871');
  assert.equal(videoEmbedUrl('https://example.com/clip.mp4'), null);
});
