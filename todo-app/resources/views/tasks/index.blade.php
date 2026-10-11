<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List App</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-900 font-sans antialiased py-10 relative min-h-screen">

    <!-- Notifikasi Sukses Melayang di Kiri Atas -->
    @if (session('success'))
        <div id="success-alert"
            class="fixed top-5 left-5 z-50 max-w-sm p-4 bg-green-500 text-white rounded-xl shadow-lg flex items-center space-x-3 transition-opacity duration-500">
            <span>✅</span>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>

        <script>
            setTimeout(function() {
                const alertBox = document.getElementById('success-alert');
                if (alertBox) {
                    alertBox.style.opacity = '0';
                    setTimeout(function() {
                        alertBox.remove();
                    }, 500);
                }
            }, 3000);
        </script>
    @endif

    <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-indigo-600">📝 To-Do List App</h1>

        <!-- Form Tambah Tugas -->
        <form action="{{ route('tasks.store') }}" method="POST" class="mb-6 space-y-3">
            @csrf
            <div>
                <input type="text" name="title" placeholder="Judul tugas baru..." required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <textarea name="description" placeholder="Deskripsi (opsional)..." rows="2"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Tambah Tugas
            </button>
        </form>

        <hr class="my-6">

        <!-- Fitur Filter Tugas -->
        <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-medium text-gray-600">Filter Status:</span>
            <div class="space-x-2">
                <a href="{{ route('tasks.index') }}"
                    class="px-3 py-1 text-sm rounded-lg {{ request('status') === null ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                    Semua
                </a>
                <a href="{{ route('tasks.index', ['status' => 'pending']) }}"
                    class="px-3 py-1 text-sm rounded-lg {{ request('status') === 'pending' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                    Belum Selesai
                </a>
                <a href="{{ route('tasks.index', ['status' => 'completed']) }}"
                    class="px-3 py-1 text-sm rounded-lg {{ request('status') === 'completed' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                    Selesai
                </a>
            </div>
        </div>

        <!-- Daftar Tugas -->
        <div class="space-y-3">
            @forelse($tasks as $task)
                <div
                    class="flex items-center justify-between p-4 border rounded-lg {{ $task->is_completed ? 'bg-gray-50 border-green-200' : 'bg-white' }}">
                    <div class="flex items-center space-x-3">
                        <!-- Toggle Status -->
                        <form action="{{ route('tasks.toggle', $task) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xl">
                                {!! $task->is_completed ? '✅' : '⬜' !!}
                            </button>
                        </form>
                        <div>
                            <h3
                                class="font-semibold first-letter:uppercase {{ $task->is_completed ? 'line-through text-gray-400' : 'text-gray-800' }}">
                                {{ $task->title }}
                            </h3>
                            @if ($task->description)
                                <p
                                    class="text-sm text-gray-500 first-letter:uppercase {{ $task->is_completed ? 'line-through' : '' }}">
                                    {{ $task->description }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Tombol Aksi (Edit & Hapus) -->
                    <div class="flex items-center space-x-3">
                        <button
                            onclick="openEditModal('{{ $task->id }}', '{{ addslashes($task->title) }}', '{{ addslashes($task->description) }}')"
                            class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                            Edit
                        </button>

                        <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                            onsubmit="return confirm('Hapus tugas ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="text-red-500 hover:text-red-700 text-sm font-medium">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-400 py-4">Tidak ada tugas ditemukan.</p>
            @endforelse
        </div>
    </div>

    <!-- MODAL EDIT TUGAS -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-xl w-full max-w-md shadow-xl">
            <h2 class="text-xl font-bold mb-4 text-indigo-600">Edit Tugas</h2>

            <form id="editForm" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul Tugas</label>
                    <input type="text" id="edit-title" name="title" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea id="edit-description" name="description" rows="3"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, title, description) {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editForm');
            const inputTitle = document.getElementById('edit-title');
            const inputDesc = document.getElementById('edit-description');

            form.action = `/tasks/${id}`;
            inputTitle.value = title;
            inputDesc.value = description === 'null' ? '' : description;

            modal.classList.remove('hidden');
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.add('hidden');
        }
    </script>
</body>

</html>
