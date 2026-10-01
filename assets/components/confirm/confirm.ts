import Component from '@wexample/symfony-loader/js/Class/Component';
import OverlayMixin from '@wexample/symfony-loader/js/Class/Mixins/OverlayMixin';
import FadeAnimationMixin from '@wexample/symfony-loader/js/Class/Mixins/FadeAnimationMixin';
import KeyboardService from '@wexample/symfony-loader/js/Services/KeyboardService';
import { renderPromptActions, PromptAction, promptActionCancel, promptActionDefault } from '../../js/Helper/PromptActionsHelper';
import { focusTrapFocusables, focusTrapNext } from '../../js/Helper/FocusTrapHelper';

export default class extends Component {
  async init() {
    FadeAnimationMixin.apply(this);
    OverlayMixin.apply(this);

    await super.init();
  }

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    if (this.options?.variant === 'toast') {
      return;
    }

    // Tab goes round the answers, never into the page behind the question.
    this.app.services.keyboard.registerKeyDown(
      this,
      KeyboardService.KEY_TAB,
      (event: KeyboardEvent) => this.trapTab(event),
      {
        priority: 150,
        enabled: () => this.isActiveOverlay(),
      }
    );

    this.app.services.keyboard.registerKeyDown(
      this,
      KeyboardService.KEY_ENTER,
      () => {
        const action = this.findDefaultAction();
        if (!action) {
          return false;
        }

        this.resolve({ ...action, keepOpen: false });
      },
      {
        priority: 150,
        preventDefault: true,
        // Decided before the key is held back: a button of the question
        // holding the focus must keep its own Enter.
        enabled: (event: KeyboardEvent) => this.shouldHandleEnter(event),
      }
    );
  }

  attachHtmlElements() {
    super.attachHtmlElements();
    this.attachHtmlElementsMap({
      title: '[data-confirm-title]',
      message: '[data-confirm-message]',
      actions: '[data-confirm-actions]',
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
    this.describe(titleEl, messageEl);

    await super.mounted();
  }

  // A question that must be answered before anything else, to assistive
  // technologies: named by its title, described by its message.
  private describe(titleEl?: HTMLElement, messageEl?: HTMLElement) {
    const id = `confirm-${Math.random().toString(36).slice(2, 10)}`;

    this.el.setAttribute('role', 'alertdialog');
    if (this.options?.variant !== 'toast') {
      this.el.setAttribute('aria-modal', 'true');
    }

    if (titleEl && this.options?.title) {
      titleEl.id = `${id}-title`;
      this.el.setAttribute('aria-labelledby', titleEl.id);
    }

    if (messageEl && this.options?.message) {
      messageEl.id = `${id}-message`;
      this.el.setAttribute(this.options?.title ? 'aria-describedby' : 'aria-labelledby', messageEl.id);
    }
  }

  async overlayOnOpen(): Promise<void> {
    await (this as FadeAnimationMixin).fadeOpen();
    this.getActionButton(this.findDefaultAction())?.focus({ preventScroll: true });
  }

  private getActionButton(action: PromptAction | null): HTMLButtonElement | null {
    if (!action) {
      return null;
    }

    return Array.from(this.el.querySelectorAll<HTMLButtonElement>('[data-confirm-value]'))
      .find((button) => button.dataset.confirmValue === action.value) ?? null;
  }

  private trapTab(event: KeyboardEvent): boolean {
    const focusables = focusTrapFocusables(this.el);
    const active = document.activeElement as HTMLElement | null;
    const next = focusTrapNext(focusables, active && focusables.includes(active) ? active : null, event.shiftKey);

    if (!next) {
      return false;
    }

    event.preventDefault();
    next.focus();

    return true;
  }

  overlayOnClickOutside(): void {
    // Do not close confirms when clicking outside (box variant).
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
        buttonClass: 'confirm--action',
        roleClasses: this.options?.variant === 'toast' ? { primary: '' } : undefined,
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

    (this as any).overlayClose();
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

    // A button of the question holding the focus answers for itself.
    if (target.closest('[data-confirm-value]') && this.el.contains(target)) {
      return false;
    }

    const tag = target.tagName;
    return !(tag === 'INPUT' || tag === 'TEXTAREA' || target.isContentEditable);
  }

  private findDefaultAction(): PromptAction | null {
    return promptActionDefault(this.options?.actions || []);
  }

  private findCancelAction(): PromptAction | null {
    return promptActionCancel(this.options?.actions || []);
  }
}
