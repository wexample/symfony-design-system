export type FilePathParts = {
  folder: string;
  name: string;
};

/**
 * A path cut where it is read: the folders leading to the file, trailing slash
 * kept, and the file's own name. What the file-path component draws, for a
 * script that draws it some other way.
 */
export function filePathSplit(path: string): FilePathParts {
  const cut = (path || '').split('/');
  const name = cut.pop() ?? '';

  return {
    folder: cut.length ? `${cut.join('/')}/` : '',
    name,
  };
}
