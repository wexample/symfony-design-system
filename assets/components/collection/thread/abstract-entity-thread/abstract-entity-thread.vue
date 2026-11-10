<script>
import AbstractEntityCollectionVueMixin from "../../../../js/Vue/AbstractEntityCollectionVueMixin";
import buildTranslatedBindings from "../../../../js/Helper/TranslationHelper";
import {markdownRender} from "../../../../js/Helper/MarkdownHelper";
import ThreadMessage from "../thread-message/thread-message.vue";
import LoadMore from "../../../load-more/load-more.vue";
import Disclosure from "../../../disclosure/disclosure.vue";
import {threadContinued, threadDayKey, threadDayLabel} from "../../../../js/Helper/ThreadHelper";
import {mentionMark} from "../../../../js/Helper/MentionHelper";

// How recent the last line waiting for an answer has to be, the thread opened
// again, for the thread to show the answer is still coming. While the thread
// stays open, it shows it until the other side is done (isReplyComplete), as
// long as it takes.
const PENDING_ANSWER_MS = 2 * 60 * 1000;

// What turns while the other side works, a frame at a time.
const ACTIVITY_FRAMES = ['·', '✧', '✦', '◆', '✦', '✧'];
const ACTIVITY_FRAME_MS = 150;

// Where the thread's own words are, for a line it writes itself.
const DOMAIN = 'WexampleSymfonyDesignSystemBundle.components.collection.thread.abstract-entity-thread.abstract-entity-thread::';

const translated = buildTranslatedBindings({
  resolvedLoadOlderLabel: ['loadOlderLabel', `${DOMAIN}load_older`]
});

// The messages of a conversation, as a collection read backwards: the latest
// at the foot, older ones loaded above on demand, each line drawn by the
// component the thread names for it, consecutive lines of a kind folded
// together, what arrives on the live topic added as it comes. A thread knows
// who speaks in it — the reader's own lines, the other side's — and shows,
// while the other side works, that its answer is coming. It does not write:
// what writes to a conversation is a composer, and a chat is the two
// together (abstract-entity-chat). A subclass says, in the words of its own
// entity, what a line is.
export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-components-collection-thread-abstract-entity-thread-abstract-entity-thread",

  mixins: [AbstractEntityCollectionVueMixin],

  components: {
    Disclosure,
    LoadMore
  },

  props: {
    ...translated.props,

    // How the lines are laid: `flat`, every line at the start with its author
    // first, as a channel reads; `aligned`, the reader's own lines at the end
    // side and the others at the start, as a messenger reads.
    layout: {
      type: String,
      default: 'flat'
    },
    // A divider where a day begins — today, yesterday, a date —, for a
    // conversation read across days.
    dayDividers: {
      type: Boolean,
      default: false
    },
    // When the reader last read the conversation: a divider above the first
    // line from someone else written after it, « New messages ».
    unreadSince: {
      type: String,
      default: null
    },
    // The people of the conversation, as the composer offers them
    // (`mentions`): each `@handle` naming one set apart in the lines.
    people: {
      type: Array,
      default: () => []
    }
  },

  data() {
    return {
      // How far the thread was from its bottom when older messages were asked
      // for. Null means the thread should simply go to the bottom.
      threadScrollAnchor: null,
      // A message was sent and its answer has not arrived yet: the thread
      // shows it is coming (expectsReply()).
      awaitingReply: false,
      // When the wait began, the frame the activity shows, and the time it
      // is measured against: kept while the wait lasts, and only then.
      awaitingSince: null,
      activityFrame: 0,
      activityNow: Date.now()
    };
  },

  computed: {
    ...translated.computed,

    // The lines of the thread, each with the entity it draws: what a line
    // looks like is the component's business (getMessageComponent).
    messages() {
      return this.entities.filter((entity) => this.isMessageShown(entity)).map((entity, index) => ({
        key: this.getMessageKey(entity, index),
        entity,
        // Sent by the reader: the side the `aligned` layout puts it on.
        own: this.isOwnMessage(entity)
      }));
    },

    // What the thread draws: the lines, those the thread groups gathered — the
    // consecutive ones of the same group key — into `{ key, group: true,
    // groupKey, lines }`. A group is keyed by its first line, so one growing
    // at its end — a line arriving live — stays as it is, open or not.
    // Between them, the dividers asked for (`{ key, divider, label }`): a
    // day beginning, the first line unread. A line going on from the one
    // before — its author, soon after, no divider between — says so
    // (`continued`): its author and its face are not drawn again.
    threadItems() {
      const items = [];
      let previous = null;
      let previousDay = null;
      let unreadPlaced = false;
      const unreadSince = this.unreadSince ? new Date(this.unreadSince).getTime() : null;

      for (const line of this.messages) {
        const groupKey = this.getMessageGroupKey(line.entity);
        const last = items[items.length - 1];
        const facts = {
          author: this.getMessageAuthor(line.entity),
          own: line.own,
          date: this.getMessageDate(line.entity)
        };
        let divided = false;

        if (this.dayDividers) {
          const day = threadDayKey(facts.date);

          if (day && day !== previousDay) {
            items.push({ key: `day-${day}`, divider: 'day', label: this.dayLabel(day) });
            divided = true;
          }

          previousDay = day ?? previousDay;
        }

        if (unreadSince !== null && !unreadPlaced && !line.own && facts.date && new Date(facts.date).getTime() > unreadSince) {
          items.push({ key: 'unread', divider: 'unread', label: this.trans(`${DOMAIN}divider.unread`) });
          unreadPlaced = true;
          divided = true;
        }

        if (groupKey === null || groupKey === undefined) {
          items.push({ ...line, continued: !divided && threadContinued(previous, facts) });
        } else if (!divided && last?.group && last.groupKey === groupKey) {
          last.lines.push(line);
        } else {
          items.push({ key: `group-${line.key}`, group: true, groupKey, lines: [line] });
        }

        previous = groupKey === null || groupKey === undefined ? facts : null;
      }

      return items;
    },

    // A reader asking for less motion sees the mark still.
    activityGlyph() {
      return window.matchMedia?.('(prefers-reduced-motion: reduce)').matches
        ? ACTIVITY_FRAMES[2]
        : ACTIVITY_FRAMES[this.activityFrame % ACTIVITY_FRAMES.length];
    },

    activityLabel() {
      return this.trans(`${DOMAIN}activity.thinking`);
    },

    // 45s, 13m5s, 2h4m: how long the other side has been at it.
    activityElapsed() {
      if (this.awaitingSince === null) {
        return '';
      }

      const seconds = Math.max(0, Math.floor((this.activityNow - this.awaitingSince) / 1000));
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);

      if (hours) {
        return `${hours}h${minutes}m`;
      }

      return minutes ? `${minutes}m${seconds % 60}s` : `${seconds % 60}s`;
    },

    // Shown once the first page is read (`pagination` set by it), nothing in
    // it, and the thread has something to say; gone with the first message,
    // live ones included.
    emptyText() {
      if (this.isLoading || this.pagination === null || this.messages.length) {
        return null;
      }

      return this.getEmptyText() || null;
    }
  },

  beforeUnmount() {
    this.stopActivity();
  },

  watch: {
    awaitingReply(awaiting) {
      if (awaiting) {
        this.startActivity();
      } else {
        this.stopActivity();
      }
    },

    entities(entities) {
      // The answer is in once the thread ends on a line someone else spoke.
      const last = entities[entities.length - 1];
      if (this.awaitingReply && last && !this.isOwnMessage(last) && this.isSpoken(last)) {
        this.awaitingReply = false;
      } else if (!this.awaitingReply && last && this.isAnswerPending(last)) {
        // Measured from the line still waiting, not from the thread opening.
        this.awaitingSince = new Date(this.getMessageDate(last)).getTime();
        this.awaitingReply = true;
      }

      this.$nextTick(() => this.settleThreadScroll());
    }
  },

  methods: {
    startActivity() {
      this.awaitingSince = this.awaitingSince ?? Date.now();
      this.activityNow = Date.now();
      window.clearInterval(this.activityTimer);
      this.activityTimer = window.setInterval(() => {
        this.activityFrame++;
        this.activityNow = Date.now();
      }, ACTIVITY_FRAME_MS);
    },

    stopActivity() {
      window.clearInterval(this.activityTimer);
      this.activityTimer = null;
      this.awaitingSince = null;
    },

    // Projections. A subclass overrides the ones its entity does not already
    // answer to under these names.
    getMessageKey(entity, index) {
      return entity?.id ?? entity?.secureId ?? index;
    },

    getMessageAuthor(entity) {
      return entity?.author ?? '';
    },

    getMessageContent(entity) {
      return entity?.content ?? '';
    },

    getMessageDate(entity) {
      return entity?.createdAt ?? null;
    },

    getMessageIcon() {
      return 'ph:bold/user';
    },

    // The author's face, for a conversation between people; none draws the
    // icon (getMessageIcon).
    getMessageAvatar() {
      return null;
    },

    // What tells the author apart beside its name; none by default.
    getMessageAuthorMarker() {
      return null;
    },

    // Names the kind of line this is, so the thread can draw a tool call or a
    // system notice differently from a spoken turn. Null keeps the plain row.
    getMessageVariant() {
      return null;
    },

    // Whether a line is written in markdown and drawn from it: what the other
    // side speaks — an agent writes markdown —, never what the reader typed.
    isMessageMarkdown(entity) {
      return !this.isOwnMessage(entity) && this.isSpoken(entity);
    },

    // Whether a message of the thread is drawn at all. A layer keeping lines
    // in the thread that nobody reads — a page's answer to a tool — says
    // which; every line is drawn by default.
    isMessageShown() {
      return true;
    },

    // Whether a line is someone speaking — what ends a wait, is read as
    // markdown —, rather than a line the thread carries for its machinery.
    // Every line is spoken by default.
    isSpoken() {
      return true;
    },

    // Whether a message of the thread was written by whoever reads it: what
    // ends the wait for an answer is a message that is not.
    isOwnMessage() {
      return true;
    },

    // What the thread says while it holds no message — what the conversation
    // is about, how to start it. None by default.
    getEmptyText() {
      return null;
    },

    // Lines gathered under one folded line: consecutive lines of the same
    // key form a group. Null, the default, leaves a line alone.
    getMessageGroupKey() {
      return null;
    },

    // What the folded line of a group says: how many lines it holds.
    getMessageGroupLabel(group) {
      return this.trans(`${DOMAIN}group`, { '%count%': group.lines.length });
    },

    isMessageGroupOpen() {
      return false;
    },

    // What draws a line of the thread: a line of conversation by default. A
    // thread whose lines are something else — an inspector's block, a card —
    // returns its own component, imported and named in its template's
    // vue_require like any other it uses.
    getMessageComponent() {
      return ThreadMessage;
    },

    // What that component is handed: the projection below for the line of
    // conversation; a thread with its own component passes what it reads,
    // typically `{ entity }`.
    getMessageProps(entity) {
      return {message: this.getMessage(entity)};
    },

    // A line as a conversation reads it, drawn from the projections above:
    // never from the shape of the entity.
    getMessage(entity) {
      const html = this.isMessageMarkdown(entity) ? markdownRender(this.getMessageContent(entity)) : null;

      return {
        author: this.getMessageAuthor(entity),
        content: this.getMessageText(entity),
        // Drawn from markdown, or null for a line shown as the text it is;
        // drawn as html too when it names someone of the conversation.
        html: this.markMentions(html, this.getMessageText(entity)),
        date: this.getMessageDate(entity),
        iconHtml: this.renderIcon(this.getMessageIcon(entity)),
        // A person's face (`avatar`: { label, src, emoji, presence }), in
        // place of the icon.
        avatar: this.getMessageAvatar(entity),
        // What tells the author apart beside its name — an agent among
        // people: { label, tone }.
        authorMarker: this.getMessageAuthorMarker(entity),
        variant: this.getMessageVariant(entity),
        own: this.isOwnMessage(entity)
      };
    },

    // The people a line names set apart, in its html or in its text made
    // html; the html as it was when the conversation has nobody to name.
    markMentions(html, text) {
      if (!this.people.length) {
        return html;
      }

      const source = html ?? String(text ?? '').replace(/[&<>"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[char]));
      const marked = mentionMark(source, this.people);

      return html === null && marked === source ? null : marked;
    },

    // A day as the divider says it.
    dayLabel(day) {
      return threadDayLabel(day, {
        today: this.trans(`${DOMAIN}divider.today`),
        yesterday: this.trans(`${DOMAIN}divider.yesterday`)
      }, document.documentElement.lang || undefined);
    },

    // What the thread shows of a line: its content, or what a layer makes
    // of a line that is not spoken.
    getMessageText(entity) {
      return this.getMessageContent(entity);
    },

    // A line arriving on the live topic: every payload of the topic is asked
    // whether the other side is done, then the line is handed to onLiveEntity.
    onLiveSourceMessage(payload, source) {
      AbstractEntityCollectionVueMixin.methods.onLiveSourceMessage.call(this, payload, source);

      if (this.awaitingReply && this.isReplyComplete(payload, source)) {
        this.awaitingReply = false;
      }

      const entity = payload?.data?.entity ?? payload?.data;

      if (!entity || !this.isLiveSourceEvent(source, payload?.event)) {
        return;
      }

      this.onLiveEntity(entity, payload);
    },

    // A line that just arrived on the live topic. A layer doing something
    // with it — hearing someone else speak, running what it asks — says so
    // here; nothing by default.
    onLiveEntity() {
    },

    // Whether the other side being done is what this payload of the live
    // topic says: by default a message of the thread written by the other
    // side, not a line a tool adds. A thread whose engine says so otherwise
    // — an event closing the request — reads it here, and the thread stops
    // showing an answer is coming only then, however long it takes.
    isReplyComplete(payload, source) {
      const entity = payload?.data?.entity ?? payload?.data;

      return Boolean(entity)
        && this.isLiveSourceEvent(source, payload?.event)
        && !this.isOwnMessage(entity)
        && this.isSpoken(entity);
    },

    // What the thread hangs from, since a browser opens the page before a
    // single message exists: the only name it can subscribe to is the one
    // already there. `{entityName, id, event}`, the event being what the
    // server publishes on it when a message is written — one, or several.
    // Null leaves the thread without live updates.
    getLiveSource() {
      return this.getLiveThread();
    },

    // Kept as the thread's own word for it, since a thread is what a
    // conversation calls the thing it is the collection of.
    getLiveThread() {
      return null;
    },

    // Whether a message sent here is answered by someone else — an agent —,
    // the thread showing the answer is coming until it arrives. A thread
    // whose messages wait for nobody leaves it off.
    expectsReply() {
      return false;
    },

    // A thread opened — a page reached, a reload — whose last line still waits
    // for the other side: the reader's own, or one not spoken, written lately.
    // Older than that, the answer is not coming, and the thread does not
    // pretend it is.
    isAnswerPending(last) {
      if (!this.expectsReply() || (!this.isOwnMessage(last) && this.isSpoken(last))) {
        return false;
      }

      const date = this.getMessageDate(last);

      return Boolean(date) && Date.now() - new Date(date).getTime() < PENDING_ANSWER_MS;
    },

    // Told by whoever just wrote to the conversation: the answer is coming,
    // if the thread expects one.
    expectReply() {
      this.awaitingReply = this.expectsReply();
    },

    renderIcon(name) {
      return name ? this.app.getServiceOrFail('icon').icon(name) : '';
    },

    // Older messages are added above what is being read, so the thread must
    // stay on the same line: what is held constant is the distance to the
    // bottom, since everything that appeared is higher up.
    async loadOlderMessages() {
      const lines = this.$refs.lines;

      this.threadScrollAnchor = lines ? lines.scrollHeight - lines.scrollTop : null;

      await this.loadOlderEntities();
    },

    settleThreadScroll() {
      const lines = this.$refs.lines;

      if (!lines) {
        return;
      }

      if (this.threadScrollAnchor === null) {
        lines.scrollTop = lines.scrollHeight;

        return;
      }

      lines.scrollTop = lines.scrollHeight - this.threadScrollAnchor;
      this.threadScrollAnchor = null;
    }
  }
};
</script>
