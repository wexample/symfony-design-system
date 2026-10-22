import Component from '@wexample/symfony-loader/js/Class/Component';
import { proofOfWorkSolve, type ProofOfWorkChallenge } from '../../../js/Helper/ProofOfWorkHelper';

/**
 * Works out the challenge as soon as the form is shown, while it is being
 * filled in. A form sent before the work is done waits for it, then leaves
 * by the same button — captured, so the wait comes before whatever else
 * sends the form.
 */
export default class extends Component {
  private solutionEl: HTMLInputElement | null = null;
  private formEl: HTMLFormElement | null = null;
  private solving: Promise<void> | null = null;
  private stopped = false;

  attachHtmlElements() {
    super.attachHtmlElements();

    this.solutionEl = this.el.querySelector('.proof-of-work-input--solution');
  }

  protected async activateListeners(): Promise<void> {
    await super.activateListeners();

    this.stopped = false;
    this.formEl = this.el.closest('form');
    this.formEl?.addEventListener('submit', this.onSubmit, true);
    this.solving = this.solve();
  }

  protected async deactivateListeners(): Promise<void> {
    await super.deactivateListeners();

    this.stopped = true;
    this.formEl?.removeEventListener('submit', this.onSubmit, true);
  }

  private async solve(): Promise<void> {
    const challenge = JSON.parse(this.el.dataset.challenge ?? '{}') as ProofOfWorkChallenge;
    const solution = await proofOfWorkSolve(challenge, () => this.stopped);

    if (solution && this.solutionEl) {
      this.solutionEl.value = solution;
    }
  }

  // Once the work is done, the form goes, solved or not: the server says
  // what an unsolved one gets.
  private onSubmit = async (event: SubmitEvent): Promise<void> => {
    if (this.solutionEl?.value || !this.solving) {
      return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();

    const submitter = event.submitter;

    await this.solving;
    this.solving = null;
    this.formEl?.requestSubmit(
      submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement ? submitter : undefined
    );
  };
}
