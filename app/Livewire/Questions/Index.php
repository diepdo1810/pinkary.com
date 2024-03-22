<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

final class Index extends Component
{
    use WithoutUrlPagination, WithPagination;

    /**
     * The component's user ID.
     */
    #[Locked]
    public int $userId;

    /**
     * The component's per page count.
     */
    public int $perPage = 5;

    /**
     * Whether the pinned label should be displayed or not.
     */
    public bool $pinnable = false;

    /**
     * Load more questions.
     */
    public function loadMore(): void
    {
        $this->perPage = ($this->perPage > 100) ? 100 : ($this->perPage + 5);
    }

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        $user = User::findOrFail($this->userId);

        return view('livewire.questions.index', [
            'user' => $user,
            'questions' => $user
                ->questionsReceived()
                ->where('is_reported', false)
                ->whereNotNull('answer')
                ->orderByDesc('pinned')
                ->orderByDesc('answered_at')
                ->simplePaginate($this->perPage),
        ]);
    }

    /**
     * Refresh the component.
     */
    #[On('question.created')]
    #[On('question.updated')]
    #[On('question.reported')]
    public function refresh(): void
    {
    }

    /**
     * Destroy the given question.
     */
    #[On('question.destroy')]
    public function destroy(string $questionId): void
    {
        $question = Question::findOrFail($questionId);

        $this->authorize('delete', $question);

        $question->delete();

        $this->dispatch('question.destroyed');
    }
}
