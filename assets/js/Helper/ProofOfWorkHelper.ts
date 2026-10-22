/**
 * The challenge ProofOfWorkService (symfony-security) draws with a form: the
 * hash of `salt` and a number below `max_number`, signed by the server, which
 * reads it back with the number found.
 */
export type ProofOfWorkChallenge = {
  algorithm: string;
  salt: string;
  challenge: string;
  max_number: number;
  issued: number;
  signature: string;
};

// Numbers tried between two breaths given back to the page: a few
// milliseconds of work, never long enough for a click to wait on it.
const PROOF_OF_WORK_BATCH = 2000;

const SHA256_K = new Uint32Array([
  0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5, 0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
  0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3, 0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
  0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc, 0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
  0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7, 0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
  0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13, 0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
  0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3, 0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
  0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5, 0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
  0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208, 0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2,
]);

const SHA256_H = [
  0x6a09e667, 0xbb67ae85, 0x3c6ef372, 0xa54ff53a, 0x510e527f, 0x9b05688c, 0x1f83d9ab, 0x5be0cd19,
];

const rotateRight = (value: number, bits: number): number => (value >>> bits) | (value << (32 - bits));

const sha256W = new Uint32Array(64);

function sha256Block(hash: Uint32Array, words: Uint32Array, offset: number): void {
  const w = sha256W;

  for (let t = 0; t < 16; t++) {
    w[t] = words[offset + t];
  }

  for (let t = 16; t < 64; t++) {
    const s0 = rotateRight(w[t - 15], 7) ^ rotateRight(w[t - 15], 18) ^ (w[t - 15] >>> 3);
    const s1 = rotateRight(w[t - 2], 17) ^ rotateRight(w[t - 2], 19) ^ (w[t - 2] >>> 10);
    w[t] = w[t - 16] + s0 + w[t - 7] + s1;
  }

  let a = hash[0];
  let b = hash[1];
  let c = hash[2];
  let d = hash[3];
  let e = hash[4];
  let f = hash[5];
  let g = hash[6];
  let h = hash[7];

  for (let t = 0; t < 64; t++) {
    const t1 = (h
      + (rotateRight(e, 6) ^ rotateRight(e, 11) ^ rotateRight(e, 25))
      + ((e & f) ^ (~e & g))
      + SHA256_K[t]
      + w[t]) | 0;
    const t2 = ((rotateRight(a, 2) ^ rotateRight(a, 13) ^ rotateRight(a, 22))
      + ((a & b) ^ (a & c) ^ (b & c))) | 0;

    h = g;
    g = f;
    f = e;
    e = (d + t1) | 0;
    d = c;
    c = b;
    b = a;
    a = (t1 + t2) | 0;
  }

  hash[0] += a;
  hash[1] += b;
  hash[2] += c;
  hash[3] += d;
  hash[4] += e;
  hash[5] += f;
  hash[6] += g;
  hash[7] += h;
}

/**
 * Whether SHA-256 of `prefix` followed by `number` is `target`. Written out
 * rather than asked of `crypto.subtle`, which the browser offers on HTTPS
 * pages only and answers in a promise per hash: the hundred thousand short
 * hashes a challenge may take go faster here. Nothing is written out either:
 * the target is compared word by word, the message built in place in
 * `words` — a salt and digits are ASCII, one byte a character.
 */
function sha256Matches(prefix: string, number: number, target: Uint32Array, words: Uint32Array, hash: Uint32Array): boolean {
  const text = prefix + number;
  const length = text.length;
  const size = ((length + 9 + 63) >> 6) * 16;

  words.fill(0, 0, size);

  for (let index = 0; index < length; index++) {
    words[index >> 2] |= text.charCodeAt(index) << (24 - (index & 3) * 8);
  }
  words[length >> 2] |= 0x80 << (24 - (length & 3) * 8);
  words[size - 1] = length * 8;
  hash.set(SHA256_H);

  for (let offset = 0; offset < size; offset += 16) {
    sha256Block(hash, words, offset);
  }

  for (let index = 0; index < 8; index++) {
    if (hash[index] !== target[index]) {
      return false;
    }
  }

  return true;
}

/**
 * Tries every number until its hash is the challenge, and answers what the
 * server reads back: the challenge and the number, as base64 JSON. Null when
 * none matches — a challenge not drawn by the server — or when `stopped()`
 * says the form is gone.
 */
export async function proofOfWorkSolve(
  challenge: ProofOfWorkChallenge,
  stopped: () => boolean = () => false
): Promise<string | null> {
  const target = new Uint32Array(8);
  const hash = new Uint32Array(8);

  for (let index = 0; index < 8; index++) {
    target[index] = parseInt(challenge.challenge.slice(index * 8, index * 8 + 8), 16);
  }

  // Room for the salt and the longest number, in whole blocks.
  const words = new Uint32Array(((challenge.salt.length + String(challenge.max_number).length + 9 + 63) >> 6) * 16);

  for (let number = 0; number <= challenge.max_number; number++) {
    if (number % PROOF_OF_WORK_BATCH === 0) {
      await new Promise((resolve) => setTimeout(resolve));

      if (stopped()) {
        return null;
      }
    }

    if (sha256Matches(challenge.salt, number, target, words, hash)) {
      return btoa(JSON.stringify({ ...challenge, number }));
    }
  }

  return null;
}
