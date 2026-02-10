import Component from '@wexample/symfony-loader/js/Class/Component';
import OverlayMixin from '@wexample/symfony-loader/js/Class/Mixins/OverlayMixin';
import { applyOverlayDialogLifecycle } from '@wexample/symfony-loader/js/Utils/OverlayDialogHelper';
import FadeAnimationMixin from '@wexample/symfony-loader/js/Class/Mixins/FadeAnimationMixin';
import { renderPromptActions, PromptAction } from '../js/Helper/PromptActionsHelper';

export default class extends Component {
  protected fadeOpen?: () => void;
  protected closeWithAnimation?: (event?: Event) => Promise<void>;
  async init() {
    FadeAnimationMixin.apply(this);
    if (this.options?.variant !== 'toast') {
      OverlayMixin.apply(this);
      applyOverlayDialogLifecycle(this, {
        setHiddenOnOpen: false,
        setHiddenOnClose: false,
        animateClose: true,
      });
    }

    await super.init();
  }

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    if (this.options?.variant === 'toast') {
      return;
    }

    this.app.services.keyboard.registerKeyDown(
      this,
      'Enter',
      (event: KeyboardEvent) => {
        if (!this.shouldHandleEnter(event)) {
          return false;
        }

        const action = this.findPrimaryAction();
        if (!action) {
          return false;
        }

        this.resolve({ ...action, keepOpen: false });
      },
      {
        priority: 150,
        preventDefault: true,
        enabled: () => this.isActiveOverlay(),
      }
    );
  }

  attachHtmlElements() {
    super.attachHtmlElements();
    this.attachHtmlElementsMap({
      title: '[data-prompt-title]',
      message: '[data-prompt-message]',
      actions: '[data-prompt-actions]',
    });
  }

  protected async mounted(): Promise<void> {
    if (this.options?.variant === 'toast') {
      this.el.classList.add('confirm--toast');
      // Ensure the toast variant can receive pointer events within the toast stack.
      this.el.classList.add('toast-stack--item');
    } else {
      this.el.classList.add('confirm--center');
    }

    const titleEl = this.elements.title as HTMLElement | undefined;
    const messageEl = this.elements.message as HTMLElement | undefined;
    const actionsEl = this.elements.actions as HTMLElement | undefined;

    if (titleEl) {
      if (this.options?.title) {
        titleEl.textContent = this.options.title;
        titleEl.removeAttribute('hidden');
      } else {
        titleEl.setAttribute('hidden', 'hidden');
      }
    }

    if (messageEl) {
      if (this.options?.message) {
        messageEl.textContent = this.options.message;
        messageEl.removeAttribute('hidden');
      } else {
        messageEl.setAttribute('hidden', 'hidden');
      }
    }

    this.renderActions(actionsEl);

    if (this.fadeOpen) {
      this.fadeOpen();
    }

    await super.mounted();
  }

  private renderActions(actionsEl?: HTMLElement) {
    if (!actionsEl) {
      return;
    }

    const actions: PromptAction[] = this.options?.actions || [];
    renderPromptActions(
      actionsEl,
      actions,
      (action) => this.resolve(action),
      {
        buttonClass: 'confirm--action'
      }
    );
  }

  private resolve(action: PromptAction) {
    if (this.options?.onResolve) {
      this.options.onResolve(action);
    }
  }

  overlayOnEscape(): void {
    const action = this.findCancelAction();
    if (action) {
      this.resolve({ ...action, keepOpen: false });
      return;
    }

    if ((this as any).overlayClose) {
      (this as any).overlayClose();
    }
  }

  private isActiveOverlay(): boolean {
    const activeOverlay = this.app.services.overlay.getActiveOverlay?.();
    return activeOverlay === this && this.el?.classList.contains('is-open');
  }

  private shouldHandleEnter(event: KeyboardEvent): boolean {
    if (!this.isActiveOverlay()) {
      return false;
    }

    const target = event.target as HTMLElement | null;
    if (!target) {
      return true;
    }

    const tag = target.tagName;
    if (tag === 'INPUT' || tag === 'TEXTAREA' || target.isContentEditable) {
      return false;
    }

    return true;
  }

  private findPrimaryAction(): PromptAction | null {
    const actions: PromptAction[] = this.options?.actions || [];
    if (!actions.length) {
      return null;
    }

    return (
      actions.find((action) => action.role === 'primary') ||
      actions[0]
    );
  }

  private findCancelAction(): PromptAction | null {
    const actions: PromptAction[] = this.options?.actions || [];
    if (!actions.length) {
      return null;
    }

    return (
      actions.find((action) => ['cancel', 'no'].includes(action.value)) ||
      actions.find((action) => action.key === 'n') ||
      actions.find((action) => action.role === 'secondary') ||
      actions[actions.length - 1]
    );
  }

}
