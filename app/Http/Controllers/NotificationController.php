<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends WebBaseController
{
    public function index(Request $request)
    {
        if (!$this->user()) return redirect()->route('login');

        $onlyUnread = (int) $request->query('unread', 0) === 1;

        $query = Notification::query()
            ->where('user_id', (int) $this->user()->id)
            ->orderByDesc('id');

        if ($onlyUnread) $query->whereNull('read_at');

        $notifications = $query->paginate(20)->withQueryString();

        return view('notifications.index', compact('notifications','onlyUnread'));
    }

    public function markRead(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $row = Notification::where('user_id', (int) $this->user()->id)->findOrFail($id);
        if ($row->read_at === null) $row->update(['read_at' => now()]);

        return back()->with('success', 'Marked read');
    }

    public function markAllRead(Request $request)
    {
        if (!$this->user()) return redirect()->route('login');

        Notification::where('user_id', (int) $this->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'All marked read');
    }

    public function destroy(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $row = Notification::where('user_id', (int) $this->user()->id)->findOrFail($id);
        $row->delete();

        return back()->with('success', 'Deleted');
    }

    public function check(Request $request)
    {
        if (!$this->user()) return response()->json(['count' => 0, 'latest' => 0]);

        $data = $request->validate(['after_id' => ['sometimes', 'integer', 'min:0']]);
        $query = Notification::where('user_id', (int) $this->user()->id);
        $latest = (int) ((clone $query)->max('id') ?? 0);
        $unread = (clone $query)->whereNull('read_at');
        $count = (clone $unread)->count();
        $newCount = isset($data['after_id'])
            ? (clone $unread)->where('id', '>', $data['after_id'])->where('id', '<=', $latest)->count()
            : 0;

        return response()->json([
            'count' => $count,
            'latest' => $latest,
            'new_count' => $newCount,
        ]);
    }
}
