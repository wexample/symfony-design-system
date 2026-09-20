// Named with an extension on purpose: `button-input` is now both a script and
// a vue component in the same directory, and webpack resolves `.vue` first. The
// `.js` spelling is what the build maps back to the `.ts`, and it is the one
// form typescript accepts.
import ButtonInput from '../button-input/button-input.js';

export default class extends ButtonInput {}
