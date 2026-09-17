<div class="modal fade" id="remindTask{{ $task->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5">Send reminder</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Send a status reminder for <strong>{{ $task->title }}</strong>.</p>
                @if ($task->developerEmails() === [])
                    <p class="small text-danger mb-0">This task has no developer email. Add one on the task or in Developers first.</p>
                @else
                    <p class="small text-secondary mb-0">Email will go to {{ implode(', ', $task->developerEmails()) }}.</p>
                    @if (($task->attachments_count ?? $task->attachments->count()) > 0)
                        <p class="small text-secondary mb-0 mt-2">Task documents will be attached to the email.</p>
                    @endif
                @endif
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <form method="POST" action="{{ route('tasks.reminders.store', $task) }}">
                    @csrf
                    <input type="hidden" name="channel" value="email">
                    <button class="btn btn-dark" type="submit" @disabled($task->developerEmails() === [])>Email reminder</button>
                </form>
                <button class="btn btn-outline-secondary" type="button" disabled>WhatsApp reminder</button>
                <button class="btn btn-outline-secondary" type="button" disabled>Both</button>
            </div>
        </div>
    </div>
</div>
