export async function initCodeBlocks(scope: HTMLElement | Document = document): Promise<void> {
    const elements = [...scope.querySelectorAll<HTMLElement>('.code-block[data-lang]')];
    if (!elements.length) return;

    const { createHighlighter } = await import('shiki');
    const langs = [...new Set(elements.map(el => el.dataset.lang || 'text'))];

    const highlighter = await createHighlighter({
        themes: ['github-dark'],
        langs: langs as Parameters<typeof createHighlighter>[0]['langs'],
    });

    for (const el of elements) {
        const lang = el.dataset.lang || 'text';
        const codeEl = el.querySelector('code');
        if (!codeEl) continue;

        el.innerHTML = highlighter.codeToHtml(codeEl.textContent || '', {
            lang,
            theme: 'github-dark',
        });
    }
}
