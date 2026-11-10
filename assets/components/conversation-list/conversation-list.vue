<script>
import Avatar from '../avatar/avatar.vue';
import AvatarGroup from '../avatar-group/avatar-group.vue';
import DateDisplay from '../date-display/date-display.vue';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.conversations.';

// The twin of conversation-list.html.twig: conversations to open, a row each
// — who it is with, its title and when it last spoke, the last line, the
// unread count —, the open one selected. A row with an address is a link; one
// without says it was picked (`select`), for a vue that opens it itself.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-conversation-list-conversation-list',

  components: {
    Avatar,
    AvatarGroup,
    DateDisplay
  },

  emits: ['select'],

  props: {
    // [{ id, title, href, preview, date, unread, avatar, people }].
    items: {
      type: Array,
      default: () => []
    },
    // The open one, by its id.
    activeId: {
      type: [String, Number],
      default: null
    },
    empty: {
      type: String,
      default: null
    }
  },

  computed: {
    emptyText() {
      return this.empty ?? this.trans(`${DOMAIN}empty`);
    }
  },

  methods: {
    isActive(item) {
      return this.activeId !== null && this.activeId !== undefined && String(item.id) === String(this.activeId);
    },

    onSelect(item, event) {
      if (!item.href) {
        event.preventDefault();
        this.$emit('select', item);
      }
    },

    unreadText(item) {
      return this.trans(`${DOMAIN}unread`, { '%count%': item.unread });
    }
  }
};
</script>
