<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Task::latest();

        // Logika Filter Tugas
        if ($status === 'completed') {
            $query->where('is_completed', true);
        } elseif ($status === 'pending') {
            $query->where('is_completed', false);
        }

        $tasks = $query->get();

        return view('tasks.index', compact('tasks', 'status'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'is_completed' => false,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil ditambahkan!');
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil diperbarui!');
    }

    // Method khusus untuk mengubah status selesai / belum dengan cepat (Toggle)
    public function toggle(Task $task)
    {
        $task->update([
            'is_completed' => !$task->is_completed
        ]);

        return redirect()->back()->with('success', 'Status tugas diperbarui!');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dihapus!');
    }
}
