<script>
import AbstractEntityThread from "../../thread/abstract-entity-thread/abstract-entity-thread.vue";
import AbstractEntityCollectionVueMixin from "../../../../js/Vue/AbstractEntityCollectionVueMixin";
import {uiStateGet, uiStateSet} from "../../../../js/Helper/UiStateHelper";
import Composer from "../../../composer/composer.vue";

// The chats open in the page, whatever draws them: one conversation may be
// shown twice — in the dock, and again over the veil of a modal —, and one
// of them at a time listens to it and makes itself heard: the one in front,
// or the first opened when none is behind a veil. The others wait, and the
// one handed the conversation back reads what it missed.
const chatsOpen = new Set();

const syncChats = () => chatsOpen.forEach((chat) => chat.syncListening());
document.addEventListener('overlay:change', syncChats);

// What a message from the other side sounds like, bundled with the chat
// (sounds/CREDITS.md).
const SOUND_MESSAGE = new URL('../../../../sounds/chat-message.mp3', import.meta.url).href;

// Whether messages make that sound, kept for the reader across pages.
const UI_STATE_SOUND = 'ui.chat.sound';

// Where the chat's own words are, for a line it writes itself.
const DOMAIN = 'WexampleSymfonyDesignSystemBundle.components.collection.chat.abstract-entity-chat.abstract-entity-chat::';

export { chatsOpen };

// A conversation one reads and writes to: the thread of its messages
// (abstract-entity-thread) and, under it, the composer that sends the next
// one (composer). Over the two it holds what belongs to the conversation
// itself — the sound a message makes, which of the chats open on it listens
// —, and sends what the composer hands it: as the entity it is the
// collection of, or as a command the server or the page answers.
export default {
  extends: AbstractEntityThread,

  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-chat-abstract-entity-chat",

  components: {
    Composer
  },

  props: {
    // The composer's words, when the chat's are not its own.
    placeholder: {
      type: String,
      default: ''
    },

    submitLabel: {
      type: String,
      default: ''
    },

    // Both are handed over by slash_commands_js() from the chat's vue_config:
    // what the composer accepts besides text is declared on the server, and the
    // browser only receives the list.
    slashCommandGroup: {
      type: String,
      default: null
    },

    slashCommands: {
      type: Array,
      default: () => []
    }
  },

  data() {
    return {
      soundEnabled: true,
      // The messages a sound was made for: one live message may be published
      // more than once.
      soundedMessages: new Set()
    };
  },

  computed: {
    // What the chat lets be done to the conversation, as the composer takes
    // it: each action's `onClick` handed the chat.
    composerActions() {
      return this.getComposerActions().map((action) => ({
        ...action,
        onClick: action.onClick ? () => action.onClick(this) : undefined
      }));
    },

    // The bar above the thread holds what concerns the conversation itself,
    // and is drawn when it holds anything.
    hasToolbar() {
      return this.hasSound();
    },

    soundIconHtml() {
      return this.renderIcon(this.soundEnabled ? 'ph:bold/speaker-high' : 'ph:bold/speaker-slash');
    },

    soundLabel() {
      return this.trans(`${DOMAIN}toolbar.${this.soundEnabled ? 'sound_off' : 'sound_on'}`);
    }
  },

  mounted() {
    chatsOpen.add(this);
    this.soundEnabled = uiStateGet(this.app, UI_STATE_SOUND, true);
    this.runWhenAppReady(() => syncChats());
  },

  beforeUnmount() {
    chatsOpen.delete(this);
    syncChats();
  },

  methods: {
    // What can be done to the conversation, under the composer: [{ key, icon,
    // label, onClick | href, confirm: { title, message }, flat, iconOnly, end,
    // pressed }] as the composer reads them (composer `actions`), `onClick`
    // handed the chat — to reload its thread (reloadEntitiesCollection) once
    // it is emptied — and awaited. None by default.
    getComposerActions() {
      return [];
    },

    // Whether files can go with a message — a paperclip by the send button,
    // files dropped on the composer —, handed to postMessage(content, files).
    acceptsAttachments() {
      return false;
    },

    // The `accept` of the picker: types, extensions. Null takes any file.
    getAttachmentAccept() {
      return null;
    },

    // How many files a message takes at most. Null for no limit.
    getAttachmentLimit() {
      return null;
    },

    // A line that just arrived on the live topic: someone else speaking is
    // heard. A layer doing more with a line — running what it asks — says so
    // here.
    onLiveEntity(entity) {
      if (!this.isOwnMessage(entity) && this.isSpoken(entity)) {
        this.playMessageSound(entity);
      }
    },

    // Whether a message from the other side — a person, an agent — arriving
    // while the chat is open makes a sound, the reader free to turn it off:
    // a chat talked to by an agent says so; a chat between people, by
    // default, makes none.
    hasSound() {
      return false;
    },

    toggleSound() {
      this.soundEnabled = !this.soundEnabled;
      uiStateSet(this.app, UI_STATE_SOUND, this.soundEnabled);
    },

    // Whether this chat stands behind an open overlay's veil.
    isCovered() {
      return Boolean(this.app.services.overlay?.isCovered(this.$el));
    },

    // Whether this chat is the one of its conversation that listens and is
    // heard: not behind a veil, or the only one; the first opened among
    // equals — the dock's, before the one over the veil while no veil is up.
    // A chat that cannot name its conversation is a peer of none.
    isHeard() {
      const conversation = this.getConversationKey();

      if (conversation === null) {
        return true;
      }

      const peers = [...chatsOpen].filter((chat) => chat.getConversationKey() === conversation);
      const front = peers.filter((chat) => !chat.isCovered());

      return (front.length ? front : peers)[0] === this;
    },

    // The subscription follows the hearing: handed the conversation back —
    // the veil gone —, the chat reads what was said meanwhile.
    syncListening() {
      if (this.isHeard()) {
        if (!this.liveConnection && this.getLiveSource()) {
          AbstractEntityCollectionVueMixin.methods.connectToLiveSource.call(this);
          this.refreshEntitiesCollection({silent: true});
        }
      } else if (this.liveConnection) {
        this.liveConnection.close();
        this.liveConnection = null;
      }
    },

    // Asked by the collection as it mounts: only the chat that is heard
    // subscribes, the others wait to be.
    connectToLiveSource() {
      if (this.isHeard()) {
        return AbstractEntityCollectionVueMixin.methods.connectToLiveSource.call(this);
      }
    },

    // Once per message, however many times it is published. The browser may
    // refuse a sound the reader has not yet allowed by acting in the page:
    // then there is none, and nothing else changes.
    playMessageSound(entity) {
      const key = entity?.id ?? entity?.secureId;

      if (!this.hasSound() || !this.soundEnabled || !key || this.soundedMessages.has(key) || !this.isHeard()) {
        return;
      }

      this.soundedMessages.add(key);
      new Audio(SOUND_MESSAGE).play().catch(() => {});
    },

    // What names the conversation across pages and across the chats open on
    // it — the one in the dock, the one over a veil. Null for a chat that
    // cannot tell.
    getConversationKey() {
      return null;
    },

    // What the composer turns into before it is sent. Only a subclass knows
    // which entity that is and which of its fields carries the text.
    buildMessageEntity() {
      throw new Error('buildMessageEntity(content) must be implemented.');
    },

    // Sends what the reader wrote: written as the entity the chat is the
    // collection of. A layer sending more with it says so here.
    // `files`, the browser's File objects the reader attached: a chat that
    // accepts them (acceptsAttachments) posts them as it must; by default the
    // text alone is written.
    async postMessage(content, files = []) {
      await this.getEntityRepository().createEntity(this.buildMessageEntity(content));
    },

    // What the composer hands the chat: a message, or a command the page
    // keeps for itself. A message carrying files is a message, whatever its
    // text says.
    async submitMessage(content, files = []) {
      const frontCommand = files.length ? null : this.findFrontSlashCommand(content);

      if (frontCommand) {
        await this.runFrontSlashCommand(frontCommand.command, frontCommand.text);
      } else {
        // A command running on the server travels as the message it was typed
        // in: the endpoint reads the slash and answers it, so the composer has
        // one way out and only one.
        await this.postMessage(content, files);
        this.expectReply();
      }

      await this.refreshEntitiesCollection();
    },

    // A command stands either first or last in the message, and is separated
    // from the text by a space. A slash in the middle of a sentence is text.
    // The server parses the same way, on the message it receives.
    parseSlashCommand(content) {
      const leading = content.match(/^\/([\w-]+)(?:\s+([\s\S]+))?$/);

      if (leading) {
        return {name: leading[1], text: (leading[2] ?? '').trim()};
      }

      const trailing = content.match(/^([\s\S]+?)\s+\/([\w-]+)$/);

      return trailing ? {name: trailing[2], text: trailing[1].trim()} : null;
    },

    // The only thing the browser does with the list: recognise what it must
    // keep for itself. Everything else is sent and answered server-side.
    findFrontSlashCommand(content) {
      const parsed = this.parseSlashCommand(content);

      if (!parsed) {
        return null;
      }

      const command = this.slashCommands.find(
        (candidate) => candidate.name === parsed.name && candidate.frontOnly
      );

      return command ? {command, text: parsed.text} : null;
    },

    // A front-only command names the class that runs it, resolved the same way
    // a render node resolves its own: by the asset name it was bundled under.
    async runFrontSlashCommand(command, text) {
      const definition = this.app.getBundleClassDefinition(command.handler);

      if (!definition) {
        throw new Error(`Slash command "${command.name}" points at an unknown class: ${command.handler}.`);
      }

      await new definition().run(this, text);
    }
  }
};
</script>
