<script>
import DateDisplay from "../../../date-display/date-display.vue";
import Avatar from "../../../avatar/avatar.vue";
import Marker from "../../../marker/marker.vue";

// One line of a thread as a conversation reads it: an avatar, an author and a
// date, the words. What it draws is the projection the thread made of its
// entity (abstract-entity-thread), never the entity itself; a thread whose
// lines are something else hands its own component instead
// (getMessageComponent).
export default {
  template: "#vue-template-wexample-symfony-design-system-bundle-components-collection-thread-thread-message-thread-message",

  components: {
    Avatar,
    DateDisplay,
    DsMarker: Marker
  },

  props: {
    // { author, authorMarker, content, html, date, iconHtml, avatar, variant, own }.
    message: {
      type: Object,
      required: true
    }
  }
};
</script>
