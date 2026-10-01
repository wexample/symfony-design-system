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
  destructive: 'button--danger',
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

// The answer that backs out: the one named cancel or no, else the `n` key,
// else a secondary one, else the last.
export function promptActionCancel(actions: PromptAction[]): PromptAction | null {
  return actions.find((action) => ['cancel', 'no'].includes(action.value))
    || actions.find((action) => action.key === 'n')
    || actions.find((action) => action.role === 'secondary')
    || actions[actions.length - 1]
    || null;
}

export function promptActionPrimary(actions: PromptAction[]): PromptAction | null {
  return actions.find((action) => action.role === 'primary') || actions[0] || null;
}

/**
 * What Enter takes and the focus starts on: the action named, else the one
 * that backs out when another is destructive — a question about deactivating
 * someone is not answered by a stray Enter — else the primary one.
 */
export function promptActionDefault(actions: PromptAction[], named?: string | null): PromptAction | null {
  const chosen = named ? actions.find((action) => action.value === named) : null;

  if (chosen) {
    return chosen;
  }

  return actions.some((action) => action.role === 'destructive')
    ? promptActionCancel(actions)
    : promptActionPrimary(actions);
}
