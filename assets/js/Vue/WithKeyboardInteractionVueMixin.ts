import KeyboardService from "@wexample/symfony-loader/js/Services/KeyboardService";

interface KeyboardBindingOptions {
  priority?: number;
  preventDefault?: boolean;
  stopPropagation?: boolean;
  enabled?: (event: KeyboardEvent) => boolean;
}

interface KeyboardBinding {
  key: string;
  callback: (event: KeyboardEvent) => boolean | void;
  options?: KeyboardBindingOptions;
}

export default {
  props: {
    parentAllowKeyboard: {
      type: Boolean,
      default: true
    }
  },

  mounted() {
    this.app.ready(() => {
      this.syncKeyboardBindings();
    });
  },

  beforeUnmount() {
    this.app.ready(() => {
      this.unregisterKeyboardBindings();
    });
  },

  watch: {
    parentAllowKeyboard() {
      this.syncKeyboardBindings();
    }
  },

  methods: {
    keyboardBindings(): KeyboardBinding[] {
      return [];
    },

    keyboardPriority(): number {
      return 0;
    },

    keyboardEnabled(_event?: KeyboardEvent): boolean {
      return true;
    },

    syncKeyboardBindings() {
      this.unregisterKeyboardBindings();

      if (!this.parentAllowKeyboard) {
        return;
      }

      const keyboard = this.getKeyboardService();
      const bindings = this.keyboardBindings();

      for (const binding of bindings) {
        const bindingOptions = binding.options;
        const bindingEnabled = bindingOptions?.enabled;

        keyboard.registerKeyDown(
          this,
          binding.key,
          binding.callback.bind(this),
          {
            priority: bindingOptions?.priority ?? this.keyboardPriority(),
            preventDefault: bindingOptions?.preventDefault ?? false,
            stopPropagation: bindingOptions?.stopPropagation ?? false,
            enabled: (event: KeyboardEvent) => {
              if (!this.parentAllowKeyboard) {
                return false;
              }

              if (!this.keyboardEnabled(event)) {
                return false;
              }

              if (bindingEnabled && !bindingEnabled(event)) {
                return false;
              }

              return true;
            }
          }
        );
      }
    },

    unregisterKeyboardBindings() {
      this.getKeyboardService().unregisterOwner(this);
    },

    getKeyboardService(): KeyboardService {
      return this.app.services.keyboard as KeyboardService;
    }
  }
};
