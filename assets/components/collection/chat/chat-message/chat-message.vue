<script>
import DateDisplay from "../../../date-display/date-display.vue";

// One line of a chat as a conversation reads it: an avatar, an author and a
// date, the words. What it draws is the projection the chat made of its
// entity (abstract-entity-chat), never the entity itself; a chat whose lines
// are something else hands its own component instead (getMessageComponent).
export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-components-collection-chat-chat-message-chat-message",

  components: {
    DateDisplay
  },

  props: {
    // { key, author, content, html, date, iconHtml, variant, own }.
    message: {
      type: Object,
      required: true
    }
  }
};
</script>
