<script>
import AbstractEntityChat from "../abstract-entity-chat/abstract-entity-chat.vue";
import AgentToolsService from "@wexample/symfony-loader/js/Services/AgentToolsService";

// How long what an action shows — a toast once a request is answered — is
// waited for before the agent is told what came of it.
const NOTIFICATIONS_SETTLE_MS = 600;

// How long a press said to leave the page is given to do it.
const NAVIGATION_GRACE_MS = 2000;

// Where this layer's own words are.
const DOMAIN = 'WexampleSymfonyDesignSystemBundle.components.collection.chat.abstract-agent-chat.abstract-agent-chat::';

// A chat talked to by an agent that acts in the page. Over the chat of
// messages (abstract-entity-chat) it adds what an agent's turn is made of:
// the calls it makes to the page's tools, run once and answered
// (AgentToolsService); the answers held across a page the call left; the
// lines nobody reads — a call's answer, what the user did in the page —,
// kept in the thread and out of sight; what the user did, sent along with
// what they write. A chat says, in the words of its own entity, what a call
// is, what an answer is, and where to send one.
export default {
  extends: AbstractEntityChat,

  template: "#vue-template-wexample-symfony-design-system-bundle-vue-collection-chat-abstract-agent-chat",

  mounted() {
    this.sendHeldToolResults();
  },

  methods: {
    // An agent answers what is written to it.
    expectsReply() {
      return true;
    },

    // Spoken: a turn of the conversation, not a call, its answer, or what
    // the page said the user did.
    isSpoken(entity) {
      return !this.isToolLine(entity) && !this.isUserEvent(entity);
    },

    // Drawn: a call is — as a short line naming the tool —, its answer and
    // the user's doings are not.
    isMessageShown(entity) {
      return !this.isToolResult(entity) && !this.isUserEvent(entity);
    },

    getMessageText(entity) {
      const call = this.readToolCall(entity);

      return call ? this.getToolCallContent(call) : this.getMessageContent(entity);
    },

    // Whether a line is what the page said the user did (AgentUserEvents),
    // sent with the message that followed.
    isUserEvent() {
      return false;
    },

    // What the user did in the page since the agent last heard from it,
    // taken to be sent with what goes to it now: nothing for a chat whose
    // messages wait for nobody.
    takeUserEvents() {
      if (!this.expectsReply()) {
        return [];
      }

      return this.app.services.agentTools?.events.take() ?? [];
    },

    // Tool calls. The agent asks the page to run one of its tools with a line
    // of the thread (AgentToolsService); the page runs it once, however many
    // times the line reaches it, and answers with another line. A subclass
    // says how its entity carries both, and where the answer is sent.

    // The call a line carries — `{name, arguments}` —, null for any other line.
    // Read from the entity, or from the raw message a live topic carries.
    readToolCall() {
      return null;
    },

    // Whether a line is what the page answered to a call.
    isToolResult() {
      return false;
    },

    isToolLine(entity) {
      return Boolean(this.readToolCall(entity)) || this.isToolResult(entity);
    },

    // What names a call, the same each time it reaches the page.
    getToolCallId(entity) {
      return entity?.secureId ?? entity?.id ?? null;
    },

    // Tells the agent what came of a call: `results` is `[value]`, or
    // `[{error}]`; `userEvents` what the user did in the page meanwhile, for
    // a chat that passes it on.
    async postToolResult() {
      throw new Error('postToolResult(call, callId, results) must be implemented by a chat whose agent calls tools.');
    },

    // By tool name, what draws its call in the thread: `(call) => string`.
    // None by default: a call is a short line naming the tool.
    getToolRenderers() {
      return {};
    },

    getToolCallContent(call) {
      const render = this.getToolRenderers()[call.name];

      return render ? render(call) : `${this.trans(`${DOMAIN}thread.tool`)} · ${call.name}`;
    },

    // A call arriving on the live topic is run; anyone else speaking is heard.
    onLiveEntity(entity, payload) {
      if (this.readToolCall(entity)) {
        this.runToolCall(entity);
      } else {
        AbstractEntityChat.methods.onLiveEntity.call(this, entity, payload);
      }
    },

    async runToolCall(entity) {
      const call = this.readToolCall(entity);
      const callId = this.getToolCallId(entity);

      // A call the application's server answered itself — one of its own
      // tools — is shown in the thread and not run here.
      if (!call || !callId || call.runBy === 'back') {
        return;
      }

      // Null when the call already ran here, and the agent has its answer.
      // Made for this conversation: what it leaves in the page is its own.
      const agentTools = this.app.getServiceOrFail(AgentToolsService);
      const conversation = this.getConversationKey();
      const startedAt = Date.now();
      const outcome = await agentTools.run(callId, call.name, call.arguments ?? {}, {caller: conversation ?? undefined});
      if (outcome === null) {
        return;
      }

      const results = [outcome.error !== undefined ? {error: outcome.error} : outcome.value];

      // A call leaving the page is answered from the page reached, once the
      // conversation is open there again to hear what comes next. A press
      // that ends up not leaving — a form sent in the background — is
      // answered from here after all.
      if (outcome.navigation && conversation) {
        agentTools.held.hold(conversation, {callId, call, results});

        let leaving = false;
        const onLeave = () => {
          leaving = true;
        };
        window.addEventListener('beforeunload', onLeave, {once: true});
        outcome.navigation.go();

        window.setTimeout(() => {
          window.removeEventListener('beforeunload', onLeave);

          if (!leaving) {
            this.sendHeldToolResults();
          }
        }, NAVIGATION_GRACE_MS);

        return;
      }

      // A call that only read has nothing to wait for.
      await this.postToolResult(
        call,
        callId,
        this.withNotifications(results, outcome.readOnly ? [] : await this.collectNotifications(startedAt)),
        this.takeUserEvents()
      );
      outcome.navigation?.go();
    },

    // What the page told the reader in toasts from that moment on — what an
    // action did, as the reader saw it —, once it had time to say it.
    async collectNotifications(since) {
      await new Promise((resolve) => window.setTimeout(resolve, NOTIFICATIONS_SETTLE_MS));

      return this.app.services.toast?.shownSince(since) ?? [];
    },

    withNotifications(results, notifications) {
      if (!notifications.length) {
        return results;
      }

      return results.map((result) => (
        result && typeof result === 'object' && !result.error ? {...result, notifications} : result
      ));
    },

    // The answers held for this conversation by the page it was open in
    // before, sent from here, with where the page now is.
    async sendHeldToolResults() {
      const conversation = this.getConversationKey();
      if (!conversation) {
        return;
      }

      const agentTools = this.app.getServiceOrFail(AgentToolsService);

      if (!agentTools.held.has(conversation)) {
        return;
      }

      // What the page reached says on arriving — the flashes of the action,
      // as toasts — is part of the answer.
      const notifications = await this.collectNotifications(performance.timeOrigin);
      const arrived = {url: window.location.href, title: document.title};

      // The agent answers from what is sent here: the thread says so.
      this.awaitingReply = this.expectsReply();

      for (const held of agentTools.held.take(conversation)) {
        await this.postToolResult(
          held.call,
          held.callId,
          this.withNotifications(
            held.results.map((result) => (result && typeof result === 'object' && !result.error ? {...result, arrived} : result)),
            notifications
          ),
          this.takeUserEvents()
        );
      }
    },

    // Sends what the reader wrote, with what they did in the page since the
    // agent last heard from it: a chat whose engine reads it passes it on
    // (postAgentMessage); by default the message is written alone.
    async postMessage(content) {
      await this.postAgentMessage(content, this.takeUserEvents());
    },

    async postAgentMessage(content, userEvents) {
      await this.getEntityRepository().createEntity(this.buildMessageEntity(content));
    }
  }
};
</script>
