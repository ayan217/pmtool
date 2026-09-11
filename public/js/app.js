(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const toastEl = document.getElementById('appToast');
    const toastBody = document.getElementById('appToastBody');
    const toast = toastEl && window.bootstrap ? window.bootstrap.Toast.getOrCreateInstance(toastEl) : null;

    function showToast(message) {
        if (!toast || !toastBody || !message) {
            return;
        }

        toastBody.textContent = message;
        toast.show();
    }

    window.pmShowToast = showToast;

    document.querySelectorAll('[data-confirm-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm-form');
            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const commentForm = document.getElementById('commentForm');
    const commentList = document.getElementById('commentList');
    const commentEmpty = document.getElementById('commentEmpty');

    function commentMarkup(comment) {
        return `
            <article class="comment-item mb-4" data-comment-id="${comment.id}">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="fw-semibold">${escapeHtml(comment.author)} · ${escapeHtml(comment.created_at)}</div>
                        <p class="mb-0 mt-1 comment-text">${escapeHtml(comment.comment)}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-edit-comment>Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-delete-comment>Delete</button>
                    </div>
                </div>
            </article>
        `;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    async function sendJson(url, method, body) {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body ? JSON.stringify(body) : null,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'Request failed.');
        }

        return data;
    }

    if (commentForm && commentList) {
        commentForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const textarea = commentForm.querySelector('[name="comment"]');
            const value = textarea.value.trim();

            if (!value) {
                return;
            }

            try {
                const data = await sendJson(commentForm.action, 'POST', { comment: value });
                commentEmpty?.remove();
                commentList.insertAdjacentHTML('afterbegin', commentMarkup(data.comment));
                textarea.value = '';
                showToast(data.message || 'Comment added.');
            } catch (error) {
                showToast(error.message);
            }
        });

        commentList.addEventListener('click', async (event) => {
            const item = event.target.closest('[data-comment-id]');
            if (!item) {
                return;
            }

            const id = item.getAttribute('data-comment-id');
            const updateUrl = item.getAttribute('data-update-url') || `/tasks/${commentForm.dataset.taskId}/comments/${id}`;
            const deleteUrl = item.getAttribute('data-delete-url') || `/tasks/${commentForm.dataset.taskId}/comments/${id}`;

            if (event.target.matches('[data-edit-comment]')) {
                const current = item.querySelector('.comment-text').textContent;
                const next = window.prompt('Edit comment', current);
                if (next === null || next.trim() === '') {
                    return;
                }

                try {
                    const data = await sendJson(updateUrl, 'PUT', { comment: next.trim() });
                    item.querySelector('.comment-text').textContent = data.comment.comment;
                    showToast(data.message || 'Comment updated.');
                } catch (error) {
                    showToast(error.message);
                }
            }

            if (event.target.matches('[data-delete-comment]')) {
                if (!window.confirm('Delete this comment?')) {
                    return;
                }

                try {
                    const data = await sendJson(deleteUrl, 'DELETE');
                    item.remove();
                    showToast(data.message || 'Comment deleted.');
                } catch (error) {
                    showToast(error.message);
                }
            }
        });
    }
})();
