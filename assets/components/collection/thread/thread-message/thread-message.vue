<script>
import DateDisplay from "../../../date-display/date-display.vue";

// One line of a thread as a conversation reads it: an avatar, an author and a
// date, the words. What it draws is the projection the thread made of its
// entity (abstract-entity-thread), never the entity itself; a thread whose
// lines are something else hands its own component instead
// (getMessageComponent).
export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-components-collection-thread-thread-message-thread-message",

  components: {
    DateDisplay
  },

  props: {
    // { author, content, html, date, iconHtml, variant, own }.
    message: {
      type: Object,
      required: true
    }
  }
};
</script>
