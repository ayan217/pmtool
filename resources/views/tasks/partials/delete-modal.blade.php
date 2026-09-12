<div class="modal fade" id="deleteTask{{ $task->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5">Permanently delete task?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">This will permanently delete <strong>{{ $task->title }}</strong>, its comments, and attached documents. This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('tasks.destroy', $task) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Permanently Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
