<script>
import { avatarInitials } from '../../js/Helper/AvatarGroupHelper';

// The twin of avatar.html.twig: one person, or one agent, as a face — a
// picture, else an emoji, else its initials —, a dot on its corner saying
// whether it is there.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-avatar-avatar',

  props: {
    label: {
      type: String,
      default: ''
    },
    src: {
      type: String,
      default: null
    },
    emoji: {
      type: String,
      default: null
    },
    size: {
      type: String,
      default: null
    },
    // `online`, `away`, `busy`, `offline`; none, no dot.
    presence: {
      type: String,
      default: null
    }
  },

  computed: {
    initials() {
      return avatarInitials(this.label);
    },

    presenceWords() {
      return this.presence ? this.trans(`WexampleSymfonyDesignSystemBundle.common.system::frontend.presence.${this.presence}`) : '';
    }
  }
};
</script>
