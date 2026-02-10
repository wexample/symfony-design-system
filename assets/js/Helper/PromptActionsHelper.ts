export type PromptAction = {
  key: string;
  value: string;
  label: string;
  role?: 'primary' | 'secondary' | 'destructive';
  keepOpen?: boolean;
};

type PromptActionsOptions = {
  buttonClass?: string;
  roleClasses?: Record<string, string>;
};

const defaultRoleClasses: Record<string, string> = {
  primary: 'button--invert',
};

export const renderPromptActions = (
  actionsEl: HTMLElement,
  actions: PromptAction[],
  onResolve: (action: PromptAction) => void,
  options: PromptActionsOptions = {}
): void => {
  actionsEl.innerHTML = '';

  const roleClasses = {
    ...defaultRoleClasses,
    ...(options.roleClasses || {})
  };

  actions.forEach((action) => {
    const button = document.createElement('button');
    button.type = 'button';

    const role = action.role || 'secondary';
    const buttonClasses = ['button'];
    if (options.buttonClass) {
      buttonClasses.push(options.buttonClass);
    }
    if (roleClasses[role]) {
      buttonClasses.push(roleClasses[role]);
    }

    button.className = buttonClasses.join(' ');
    button.textContent = action.label;
    button.dataset.confirmValue = action.value;
    button.dataset.confirmKey = action.key;
    button.addEventListener('click', () => onResolve(action));
    actionsEl.appendChild(button);
  });
};
