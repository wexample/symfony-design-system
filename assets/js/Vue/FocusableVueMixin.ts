import WithKeyboardInteractionVueMixin from "./WithKeyboardInteractionVueMixin";

export default {
  mixins: [
    WithKeyboardInteractionVueMixin
  ],

  methods: {
    focusableRootRef(): string {
      return "focusableRoot";
    },

    getFocusableRootElement(): HTMLElement | null {
      const rootRef = this.focusableRootRef();
      const refs = this.$refs as Record<string, unknown>;
      const rootElement = refs[rootRef];

      if (!(rootElement instanceof HTMLElement)) {
        return null;
      }

      return rootElement;
    },

    focusableContainsElement(element: Element | null): boolean {
      const rootElement = this.getFocusableRootElement();
      if (!rootElement || !element) {
        return false;
      }

      return rootElement === element || rootElement.contains(element);
    },

    focusableIsActive(event?: KeyboardEvent): boolean {
      const activeElement = document.activeElement;
      if (this.focusableContainsElement(activeElement)) {
        return true;
      }

      if (!event?.target || !(event.target instanceof Element)) {
        return false;
      }

      return this.focusableContainsElement(event.target);
    },

    keyboardEnabled(event?: KeyboardEvent): boolean {
      return this.focusableIsActive(event);
    }
  }
};
