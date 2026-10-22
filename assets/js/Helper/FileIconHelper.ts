// What a file looks like, from its name: the phosphor icon for its extension
// and the kind it belongs to. The twin of FileIconExtension.php — the same
// table on both sides, so a file drawn by the server and by a vue looks alike.

const KINDS: Record<string, string[]> = {
  pdf: ['pdf'],
  image: ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'ico', 'tif', 'tiff', 'avif', 'heic'],
  svg: ['svg'],
  archive: ['zip', 'tar', 'gz', 'tgz', 'bz2', 'xz', '7z', 'rar'],
  audio: ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac'],
  video: ['mp4', 'mov', 'avi', 'mkv', 'webm'],
  spreadsheet: ['xls', 'xlsx', 'ods'],
  csv: ['csv', 'tsv'],
  document: ['doc', 'docx', 'odt', 'rtf'],
  presentation: ['ppt', 'pptx', 'odp'],
  markdown: ['md', 'markdown'],
  html: ['html', 'htm', 'twig'],
  css: ['css', 'scss', 'sass', 'less'],
  javascript: ['js', 'mjs', 'cjs', 'jsx', 'vue'],
  typescript: ['ts', 'tsx'],
  python: ['py'],
  sql: ['sql'],
  code: ['php', 'java', 'go', 'rs', 'rb', 'c', 'h', 'cpp', 'cs', 'sh', 'bash', 'json', 'yml', 'yaml', 'xml', 'toml', 'ini', 'lock'],
  text: ['txt', 'log', 'env'],
};

const ICONS: Record<string, string> = {
  pdf: 'ph:bold/file-pdf',
  image: 'ph:bold/file-image',
  svg: 'ph:bold/file-svg',
  archive: 'ph:bold/file-zip',
  audio: 'ph:bold/file-audio',
  video: 'ph:bold/file-video',
  spreadsheet: 'ph:bold/file-xls',
  csv: 'ph:bold/file-csv',
  document: 'ph:bold/file-doc',
  presentation: 'ph:bold/file-ppt',
  markdown: 'ph:bold/file-md',
  html: 'ph:bold/file-html',
  css: 'ph:bold/file-css',
  javascript: 'ph:bold/file-js',
  typescript: 'ph:bold/file-ts',
  python: 'ph:bold/file-py',
  sql: 'ph:bold/file-sql',
  code: 'ph:bold/file-code',
  text: 'ph:bold/file-text',
};

const BY_EXTENSION: Record<string, string> = Object.fromEntries(
  Object.entries(KINDS).flatMap(([kind, extensions]) => extensions.map((extension) => [extension, kind]))
);

// What follows the last dot, as PHP's pathinfo reads it: `.env` is an `env`.
export function fileExtension(name: string): string {
  const dot = (name || '').lastIndexOf('.');

  return dot >= 0 ? name.slice(dot + 1).toLowerCase() : '';
}

// The kind a file belongs to — `directory`, one of the keys above, or `file`
// for what the table does not know.
export function fileKind(name: string, isDirectory: boolean = false): string {
  if (isDirectory) {
    return 'directory';
  }

  return BY_EXTENSION[fileExtension(name)] ?? 'file';
}

export function fileIcon(name: string, isDirectory: boolean = false, open: boolean = false): string {
  if (isDirectory) {
    return open ? 'ph:bold/folder-open' : 'ph:bold/folder';
  }

  return ICONS[fileKind(name)] ?? 'ph:bold/file';
}
